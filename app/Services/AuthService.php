<?php

namespace App\Services;

use CodeIgniter\Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

/**
 * Login, JWT issuance/rotation, logout/blocklist.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Module 1 (Authentication)
 * Implement the methods listed in that document's matching "Prompt" block (Section 8).
 * Wrap every multi-table write in $this->db->transStart() / transComplete().
 */
class AuthService
{
    protected $db;
    protected $userModel;
    protected $refreshTokenModel;
    protected $revokedTokenModel;
    protected $jwtSecret;
    protected $accessTokenTtl;    // seconds
    protected $refreshTokenTtl;   // seconds

    public function __construct()
    {
        $this->db                = \Config\Database::connect();
        $this->userModel         = new \App\Models\UserModel();
        $this->refreshTokenModel = new \App\Models\RefreshTokenModel();
        $this->revokedTokenModel = new \App\Models\RevokedTokenModel();
        $this->jwtSecret         = env('JWT_SECRET') ?: getenv('JWT_SECRET') ?: 'dev-secret-change-me';
        $this->accessTokenTtl    = (int) (env('JWT_ACCESS_TTL') ?: getenv('JWT_ACCESS_TTL') ?: 1800);

        $refreshDays = env('JWT_REFRESH_TTL_DAYS') ?: getenv('JWT_REFRESH_TTL_DAYS');
        if ($refreshDays) {
            $this->refreshTokenTtl = (int) $refreshDays * 86400;
        } else {
            $this->refreshTokenTtl = (int) (env('JWT_REFRESH_TTL') ?: getenv('JWT_REFRESH_TTL') ?: 604800);
        }
    }

    /**
     * Verify credentials, issue access + refresh token pair.
     */
    public function login(string $username, string $password): array
    {
        $user = $this->userModel->where('username', $username)->first();
        if (! $user || ! password_verify($password, $user->password_hash)) {
            throw new RuntimeException('Invalid credentials', 401);
        }
        if ($user->status !== 'active') {
            throw new RuntimeException('Account is inactive', 403);
        }

        return $this->issueTokenPair($user);
    }

    /**
     * Rotate refresh token: validate presented token, revoke it, issue new pair.
     * Reuse detection: if a revoked token is presented again, revoke ALL tokens for that user.
     */
    public function refresh(string $refreshToken): array
    {
        $tokenHash = hash('sha256', $refreshToken);

        $stored = $this->refreshTokenModel
            ->where('token_hash', $tokenHash)
            ->where('revoked', false)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if (! $stored) {
            // Reuse detection: check if this hash exists but is already revoked
            $revoked = $this->refreshTokenModel
                ->where('token_hash', $tokenHash)
                ->where('revoked', true)
                ->first();
            if ($revoked) {
                // Possible token theft — revoke ALL refresh tokens for this user
                $this->revokeAllUserTokens($revoked['user_id']);
            }
            throw new RuntimeException('Invalid or expired refresh token', 401);
        }

        $user = $this->userModel->find($stored['user_id']);
        if (! $user || $user->status !== 'active') {
            throw new RuntimeException('User not found or inactive', 401);
        }

        // Revoke the used refresh token
        $this->refreshTokenModel->update($stored['id'], ['revoked' => true]);

        return $this->issueTokenPair($user);
    }

    /**
     * Logout: blocklist the current access token's jti; revoke its refresh token.
     */
    public function logout(string $accessToken): void
    {
        try {
            $payload = JWT::decode($accessToken, new Key($this->jwtSecret, 'HS256'));
            $jti  = $payload->jti ?? null;
            $exp  = $payload->exp ?? null;
            $userId = $payload->user_id ?? null;

            if ($jti && $exp) {
                $this->revokedTokenModel->insert([
                    'jti'        => $jti,
                    'expires_at' => date('Y-m-d H:i:s', $exp),
                ]);
            }
            if ($userId) {
                // Revoke all refresh tokens for this user on logout
                $this->refreshTokenModel
                    ->where('user_id', $userId)
                    ->where('revoked', false)
                    ->set(['revoked' => true])
                    ->update();
            }
        } catch (\Throwable $e) {
            // Token may be malformed/expired; still try to revoke if we can decode without verification
            // But for security, we just silently succeed — logout is idempotent
        }
    }

    /**
     * Return the authenticated user entity (without password_hash).
     */
    public function me(int $userId): ?\App\Entities\User
    {
        return $this->userModel->find($userId);
    }

    /**
     * Issue a new access + refresh token pair for the given user.
     */
    protected function issueTokenPair(\App\Entities\User $user): array
    {
        $now   = time();
        $jti   = bin2hex(random_bytes(16));
        $accessExp  = $now + $this->accessTokenTtl;
        $refreshExp = $now + $this->refreshTokenTtl;

        // Access token payload
        $accessPayload = [
            'user_id' => $user->id,
            'role'    => $user->role,
            'jti'     => $jti,
            'iat'     => $now,
            'exp'     => $accessExp,
        ];
        $accessToken = JWT::encode($accessPayload, $this->jwtSecret, 'HS256');

        // Refresh token: opaque random string, store only its hash
        $refreshToken = bin2hex(random_bytes(32));
        $tokenHash    = hash('sha256', $refreshToken);

        $this->db->transStart();
        $this->refreshTokenModel->insert([
            'user_id'    => $user->id,
            'token_hash' => $tokenHash,
            'expires_at' => date('Y-m-d H:i:s', $refreshExp),
            'revoked'    => false,
        ]);
        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Failed to store refresh token', 500);
        }

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => $this->accessTokenTtl,
            'token_type'    => 'Bearer',
        ];
    }

    /**
     * Revoke ALL refresh tokens for a user (reuse detection).
     * used when a revoked token is presented again).
     */
    protected function revokeAllUserTokens(int $userId): void
    {
        $this->refreshTokenModel
            ->where('user_id', $userId)
            ->where('revoked', false)
            ->set(['revoked' => true])
            ->update();
    }
}
