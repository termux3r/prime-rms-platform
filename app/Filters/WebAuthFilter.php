<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Guards server-rendered staff pages (web routes).
 *
 * Relies on the HttpOnly `rms_access` cookie that AuthController::login() sets,
 * so unauthenticated browsers are bounced to /login while the JSON API keeps
 * using the Authorization header via JwtAuthFilter.
 */
class WebAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $token = $request->getCookie('rms_access');
        if (! $token) {
            return $this->toLogin();
        }

        $secret = env('JWT_SECRET') ?: getenv('JWT_SECRET');
        if (! $secret) {
            log_message('error', 'JWT_SECRET not configured in .env');
            return $this->toLogin();
        }

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
            $payload = (array) $decoded;
        } catch (\Exception $e) {
            return $this->toLogin();
        }

        // Check revoked_tokens blocklist
        $jti = $payload['jti'] ?? null;
        if ($jti) {
            $revokedModel = model(\App\Models\RevokedTokenModel::class);
            if ($revokedModel->where('jti', $jti)->first()) {
                return $this->toLogin();
            }
        }

        $request->userId = (int) ($payload['user_id'] ?? 0);
        $request->userRole = $payload['role'] ?? 'cashier';

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    protected function toLogin(): ResponseInterface
    {
        return redirect()->to('/login');
    }
}