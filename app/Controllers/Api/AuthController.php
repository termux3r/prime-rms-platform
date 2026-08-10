<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AuthService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Login, refresh, logout, me.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 6.1 / Module 1 (Authentication)
 * Implement using that document's matching "Prompt" block (Section 8).
 * Keep this controller thin: translate HTTP <-> Service calls only.
 */
class AuthController extends BaseController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(): ResponseInterface
    {
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');

        if (! $username || ! $password) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Username and password are required',
            ], 422);
        }

        try {
            $tokens = $this->authService->login($username, $password);
            $this->setAuthCookie($tokens['access_token'] ?? '', $tokens['expires_in'] ?? 1800);
            return $this->respond([
                'status' => 'success',
                'data'   => $tokens,
                'message' => 'Login successful',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 401);
        }
    }

    /**
     * POST /api/v1/auth/refresh
     */
    public function refresh(): ResponseInterface
    {
        $refreshToken = $this->request->getVar('refresh_token');
        if (! $refreshToken) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Refresh token is required',
            ], 422);
        }

        try {
            $tokens = $this->authService->refresh($refreshToken);
            $this->setAuthCookie($tokens['access_token'] ?? '', $tokens['expires_in'] ?? 1800);
            return $this->respond([
                'status' => 'success',
                'data'   => $tokens,
                'message' => 'Token refreshed',
            ]);
        } catch (\RuntimeException $e) {
            return $this->respond([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 401);
        }
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(): ResponseInterface
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (! $authHeader || ! preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Missing or invalid Authorization header',
            ], 401);
        }

        $accessToken = $matches[1];
        $this->authService->logout($accessToken);
        $this->clearAuthCookie();

        return $this->respond([
            'status'  => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(): ResponseInterface
    {
        $userId = $this->request->userId ?? $this->request->get('userId');
        if (! $userId) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'Authentication required',
            ], 401);
        }

        $user = $this->authService->me($userId);
        if (! $user) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return $this->respond([
            'status' => 'success',
            'data'   => $user,
        ]);
    }

    /**
     * Persist the access token in an HttpOnly cookie so server-rendered staff
     * pages (WebAuthFilter) can gate access. A second, JWT-less mechanism is not
     * needed: the frontend keeps using localStorage Bearer tokens for the API.
     */
    protected function setAuthCookie(string $accessToken, int $expiresIn = 1800): void
    {
        if ($accessToken === '') {
            return;
        }

        $this->response->setCookie('rms_access', $accessToken, $expiresIn, '', '/', '', false, true, 'Lax');
    }

    protected function clearAuthCookie(): void
    {
        $this->response->setCookie('rms_access', '', -2592000, '', '/');
    }

    /**
     * Helper to return JSON with proper status code
     */
    protected function respond(array $data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }
}
