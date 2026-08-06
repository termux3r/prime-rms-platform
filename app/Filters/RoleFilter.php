<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Checks the authenticated user's role against the allowed roles in $arguments.
 *
 * Reference: RMS_Backend_Architecture_and_Prompts.md -- Section 5
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // JwtAuthFilter attaches userRole property to the request
        $userRole = $request->userRole ?? (method_exists($request, 'getUserRole') ? $request->getUserRole() : null);

        if (! $userRole) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Authentication required',
                ]);
        }

        $allowedRoles = is_array($arguments) ? $arguments : (empty($arguments) ? [] : [$arguments]);

        if (! in_array($userRole, $allowedRoles, true)) {
            return service('response')
                ->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Insufficient permissions',
                ]);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
