<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * Base Site URL
     */
    public string $baseURL = 'http://localhost:8080/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     * Required by CI4 v4.4+.
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * Index File
     * Empty = no index.php in URLs (clean URLs via spark serve or .htaccess)
     */
    public string $indexPage = '';

    /**
     * URI Protocol
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * Allowed URI Characters
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * Default Locale
     */
    public string $defaultLocale = 'en';

    /**
     * Negotiate Locale
     */
    public bool $negotiateLocale = false;

    /**
     * Supported Locales
     *
     * @var list<string>
     */
    public array $supportedLocales = ['en'];

    /**
     * Application Timezone
     */
    public string $appTimezone = 'Africa/Addis_Ababa';

    /**
     * Default Character Set
     */
    public string $charset = 'UTF-8';

    /**
     * Force Global Secure Requests
     */
    public bool $forceGlobalSecureRequests = false;

    /**
     * Reverse Proxy IPs
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    /**
     * Content Security Policy
     */
    public bool $CSPEnabled = false;

    // -------------------------------------------------------------------------
    // Custom API / CORS settings (used by app filters)
    // -------------------------------------------------------------------------
    public bool $removeServerHeader   = false;
    public string $responseType       = 'json';
    public bool $CSRFProtection       = false;
    public string $CSRFTokenName      = 'csrf_token';
    public string $CSRFHeaderName     = 'X-CSRF-TOKEN';
    public string $CSRFCookieName     = 'csrf_cookie';
    public int $CSRFExpire            = 7200;
    public bool $CSRFRegenerate       = true;

    /** @var list<string> */
    public array $CSRFExcludeURIs     = [];

    /** @var list<string> */
    public array $allowHeader         = ['*'];

    /** @var list<string> */
    public array $allowMethods        = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    public bool $allowCredentials     = false;

    /** @var list<string> */
    public array $exposeHeader        = [];

    public int $maxAge                = 86400;
    public bool $routerCaseInsensitive = true;
    public bool $strictURI            = false;

    public function __construct()
    {
        parent::__construct();

        $vercelUrl = getenv('VERCEL_URL') ?: ($_ENV['VERCEL_URL'] ?? null);
        if ($vercelUrl && empty(getenv('app.baseURL'))) {
            $this->baseURL = 'https://' . rtrim($vercelUrl, '/') . '/';
        }
    }
}