<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Validates the JWT (signature + expiry), then checks revoked_tokens for the jti.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 5
 */
class JwtAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (! $authHeader || ! preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $this->unauthorized('Missing or invalid Authorization header');
        }

        $token = $matches[1];
        $secret = env('JWT_SECRET') ?: getenv('JWT_SECRET');
        if (! $secret) {
            log_message('error', 'JWT_SECRET not configured in .env');
            return $this->unauthorized('Authentication misconfigured');
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $payload = (array) $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->unauthorized('Token expired');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return $this->unauthorized('Invalid token signature');
        } catch (\Exception $e) {
            log_message('error', 'JWT decode error: ' . $e->getMessage());
            return $this->unauthorized('Invalid token');
        }

        // Check revoked_tokens blocklist
        $jti = $payload['jti'] ?? null;
        if ($jti) {
            $revokedModel = model(\App\Models\RevokedTokenModel::class);
            if ($revokedModel->where('jti', $jti)->first()) {
                return $this->unauthorized('Token has been revoked');
            }
        }

        // Attach user to request for downstream use
        $request->userId = (int) ($payload['user_id'] ?? 0);
        $request->userRole = $payload['role'] ?? 'cashier';
        $request->userJti = $jti;

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    protected function unauthorized(string $message): ResponseInterface
    {
        return service('response')
            ->setStatusCode(401)
            ->setJSON([
                'status'  => 'error',
                'message' => $message,
            ]);
    }
}
