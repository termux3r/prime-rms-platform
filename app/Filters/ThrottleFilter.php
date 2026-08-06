<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rate-limits POST /auth/login (~5/min per IP+username); optionally a looser global variant.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 5
 */
class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = service('throttler');

        $ip       = $request->getIPAddress();
        $username = '';

        try {
            $json = $request->getJSON(true);
            $username = is_array($json) ? ($json['username'] ?? '') : '';
        } catch (\Throwable $e) {
            $username = $request->getPost('username') ?? '';
        }

        // Use md5 hash for alphanumeric cache key without reserved characters
        $key = 'login_' . md5($ip . '_' . $username);

        if (! $throttler->check($key, 5, MINUTE)) {
            return $this->tooManyRequests('Too many login attempts. Please try again later.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    protected function tooManyRequests(string $message): ResponseInterface
    {
        return service('response')
            ->setStatusCode(429)
            ->setJSON([
                'status'  => 'error',
                'message' => $message,
            ]);
    }
}
