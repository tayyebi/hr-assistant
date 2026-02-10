<?php
/**
 * Custom HTTP Client
 * Pure PHP HTTP client to replace GuzzleHTTP dependency
 * 
 * This class provides a simple, dependency-free HTTP client with the same interface
 * as GuzzleHTTP for basic HTTP operations (GET, POST, PUT, DELETE, HEAD).
 */
class HttpClient
{
    /**
     * @var int Default timeout in seconds
     */
    private $timeout;

    /**
     * @var bool Whether to verify SSL certificates
     */
    private $verifySSL;

    /**
     * @var array Default HTTP context options
     */
    private $defaultContext;

    /**
     * @var array Last response info
     */
    private $lastResponse;

    /**
     * Initialize HTTP client
     *
     * @param array $config Configuration options
     */
    public function __construct(array $config = [])
    {
        $this->timeout = $config['timeout'] ?? 30;
        $this->verifySSL = $config['verify'] ?? true;
        $this->lastResponse = [];

        // Build default context options
        $this->defaultContext = [
            'http' => [
                'timeout' => $this->timeout,
                'follow_location' => true,
                'max_redirects' => 5,
                'user_agent' => 'HR-Assistant-HttpClient/1.0',
            ],
            'ssl' => [
                'verify_peer' => $this->verifySSL,
                'verify_peer_name' => $this->verifySSL,
                'allow_self_signed' => !$this->verifySSL,
            ]
        ];
    }

    /**
     * Make a GET request
     *
     * @param string $url The URL to request
     * @param array $options Request options (headers, etc.)
     * @return HttpResponse
     */
    public function get(string $url, array $options = []): HttpResponse
    {
        return $this->makeRequest('GET', $url, $options);
    }

    /**
     * Make a POST request
     *
     * @param string $url The URL to request
     * @param array $options Request options (headers, json, form_params, etc.)
     * @return HttpResponse
     */
    public function post(string $url, array $options = []): HttpResponse
    {
        return $this->makeRequest('POST', $url, $options);
    }

    /**
     * Make a PUT request
     *
     * @param string $url The URL to request
     * @param array $options Request options (headers, json, etc.)
     * @return HttpResponse
     */
    public function put(string $url, array $options = []): HttpResponse
    {
        return $this->makeRequest('PUT', $url, $options);
    }

    /**
     * Make a DELETE request
     *
     * @param string $url The URL to request
     * @param array $options Request options (headers, etc.)
     * @return HttpResponse
     */
    public function delete(string $url, array $options = []): HttpResponse
    {
        return $this->makeRequest('DELETE', $url, $options);
    }

    /**
     * Make a HEAD request
     *
     * @param string $url The URL to request
     * @param array $options Request options (headers, etc.)
     * @return HttpResponse
     */
    public function head(string $url, array $options = []): HttpResponse
    {
        return $this->makeRequest('HEAD', $url, $options);
    }

    /**
     * Make an HTTP request
     *
     * @param string $method HTTP method
     * @param string $url URL to request
     * @param array $options Request options
     * @return HttpResponse
     */
    private function makeRequest(string $method, string $url, array $options): HttpResponse
    {
        $context = $this->buildContext($method, $options);
        
        // Store request details for debugging
        $this->lastResponse = [
            'method' => $method,
            'url' => $url,
            'options' => $options,
            'context' => $context
        ];

        // Suppress warnings and handle errors manually
        $response = @file_get_contents($url, false, $context);
        
        // Get response headers and status
        $responseHeaders = $this->parseResponseHeaders($http_response_header ?? []);
        $statusCode = $this->extractStatusCode($http_response_header ?? []);

        return new HttpResponse($response, $statusCode, $responseHeaders);
    }

    /**
     * Build HTTP context from options
     *
     * @param string $method HTTP method
     * @param array $options Request options
     * @return resource
     */
    private function buildContext(string $method, array $options)
    {
        $contextOptions = $this->defaultContext;
        
        // Set HTTP method
        $contextOptions['http']['method'] = strtoupper($method);

        // Handle headers
        if (isset($options['headers'])) {
            $headers = [];
            foreach ($options['headers'] as $key => $value) {
                $headers[] = "$key: $value";
            }
            $contextOptions['http']['header'] = implode("\r\n", $headers);
        }

        // Handle JSON data
        if (isset($options['json'])) {
            $data = json_encode($options['json']);
            $contextOptions['http']['content'] = $data;
            
            // Add JSON content type if not already set
            if (!isset($options['headers']['Content-Type'])) {
                $existingHeaders = $contextOptions['http']['header'] ?? '';
                $contextOptions['http']['header'] = $existingHeaders . 
                    ($existingHeaders ? "\r\n" : '') . 'Content-Type: application/json';
            }
        }

        // Handle form parameters
        if (isset($options['form_params'])) {
            $contextOptions['http']['content'] = http_build_query($options['form_params']);
            
            // Add form content type if not already set
            if (!isset($options['headers']['Content-Type'])) {
                $existingHeaders = $contextOptions['http']['header'] ?? '';
                $contextOptions['http']['header'] = $existingHeaders . 
                    ($existingHeaders ? "\r\n" : '') . 'Content-Type: application/x-www-form-urlencoded';
            }
        }

        // Handle raw body content
        if (isset($options['body'])) {
            $contextOptions['http']['content'] = $options['body'];
        }

        // Handle timeout override
        if (isset($options['timeout'])) {
            $contextOptions['http']['timeout'] = $options['timeout'];
        }

        // Handle SSL verification override
        if (isset($options['verify'])) {
            $contextOptions['ssl']['verify_peer'] = $options['verify'];
            $contextOptions['ssl']['verify_peer_name'] = $options['verify'];
            $contextOptions['ssl']['allow_self_signed'] = !$options['verify'];
        }

        // Handle redirects
        if (isset($options['allow_redirects'])) {
            $contextOptions['http']['follow_location'] = $options['allow_redirects'];
        }

        return stream_context_create($contextOptions);
    }

    /**
     * Parse response headers from $http_response_header
     *
     * @param array $responseHeaders Raw response headers
     * @return array Parsed headers
     */
    private function parseResponseHeaders(array $responseHeaders): array
    {
        $headers = [];
        
        foreach ($responseHeaders as $header) {
            if (strpos($header, ':') !== false) {
                list($key, $value) = explode(':', $header, 2);
                $headers[trim($key)] = trim($value);
            }
        }

        return $headers;
    }

    /**
     * Extract status code from response headers
     *
     * @param array $responseHeaders Raw response headers
     * @return int HTTP status code
     */
    private function extractStatusCode(array $responseHeaders): int
    {
        if (empty($responseHeaders)) {
            return 0;
        }

        $statusLine = $responseHeaders[0] ?? '';
        if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    /**
     * Get debug information about the last request
     *
     * @return array Debug information
     */
    public function getLastRequestInfo(): array
    {
        return $this->lastResponse;
    }
}

/**
 * HTTP Response class
 * Represents an HTTP response compatible with GuzzleHTTP interface
 */
class HttpResponse
{
    /**
     * @var string Response body
     */
    private $body;

    /**
     * @var int HTTP status code
     */
    private $statusCode;

    /**
     * @var array Response headers
     */
    private $headers;

    /**
     * Constructor
     *
     * @param string $body Response body
     * @param int $statusCode HTTP status code
     * @param array $headers Response headers
     */
    public function __construct($body, int $statusCode, array $headers = [])
    {
        $this->body = $body ?: '';
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Get response body
     *
     * @return HttpResponseBody
     */
    public function getBody(): HttpResponseBody
    {
        return new HttpResponseBody($this->body);
    }

    /**
     * Get status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Get a specific header
     *
     * @param string $name Header name
     * @return string|null Header value or null if not found
     */
    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    /**
     * Check if response was successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}

/**
 * HTTP Response Body class
 * Compatible with GuzzleHTTP response body interface
 */
class HttpResponseBody
{
    /**
     * @var string Body content
     */
    private $content;

    /**
     * Constructor
     *
     * @param string $content Body content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * Get contents as string
     *
     * @return string
     */
    public function getContents(): string
    {
        return $this->content;
    }

    /**
     * Get size of content
     *
     * @return int
     */
    public function getSize(): int
    {
        return strlen($this->content);
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->content;
    }
}