<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load helpers available to all controllers here.
        // $this->helpers = ['url'];

        parent::initController($request, $response, $logger);
    }

    /**
     * Read the request body as an array, tolerating both `application/json`
     * payloads and multipart/form-data submissions (image uploads).
     *
     * `IncomingRequest::getJSON(true)` throws when the body is not valid JSON,
     * so we only parse JSON when the Content-Type actually declares it.
     *
     * @return array<string, mixed>
     */
    protected function body(): array
    {
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));

        if (str_contains($contentType, 'application/json')) {
            $json = $this->request->getJSON(true);

            return is_array($json) ? $json : [];
        }

        return $this->request->getPost() ?? [];
    }
}
