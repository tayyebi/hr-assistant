<?php

/**
 * HTTP Provider Base Class
 * Provides common HTTP functionality for API-based providers
 * 
 * Requires Guzzle HTTP client: composer require guzzlehttp/guzzle:^7.5
 */
abstract class HttpProvider extends AbstractProvider
{
    /**
     * @var object Guzzle HTTP Client
     */
    protected $httpClient;

    /**
     * @var string Base URL for API calls
     */
    protected $baseUrl;

    /**
     * Initialize HTTP client
     */
    public function __construct(string $tenantId, array $config)
    {
        parent::__construct($tenantId, $config);
        
        // Dynamically load Guzzle if available
        if (class_exists('GuzzleHttp\Client')) {
            $this->httpClient = new \GuzzleHttp\Client([
                'timeout' => 30,
                'verify' => true,
            ]);
        } else {
            throw new Exception('GuzzleHttp\Client not found. Install via: composer require guzzlehttp/guzzle:^7.5');
        }
    }

    /**
     * Make a GET request
     *
     * @return array|null Response body as array or null on error
     */
    protected function get(string $path, array $headers = [])
    {
        try {
            $response = $this->httpClient->get($this->baseUrl . $path, [
                'headers' => $this->getDefaultHeaders($headers),
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            $this->logError('GET', $path, $e->getMessage());
            return null;
        }
    }

    /**
     * Make a POST request
     *
     * @return array|bool|null Response body as array, true on success, null on error
     */
    protected function post(string $path, array $data = [], array $headers = [])
    {
        try {
            $response = $this->httpClient->post($this->baseUrl . $path, [
                'headers' => $this->getDefaultHeaders($headers),
                'json' => $data,
            ]);

            $content = $response->getBody()->getContents();
            return !empty($content) ? json_decode($content, true) : true;
        } catch (\Exception $e) {
            $this->logError('POST', $path, $e->getMessage());
            return null;
        }
    }

    /**
     * Make a PUT request
     *
     * @return array|bool|null Response body as array, true on success, null on error
     */
    protected function put(string $path, array $data = [], array $headers = [])
    {
        try {
            $response = $this->httpClient->put($this->baseUrl . $path, [
                'headers' => $this->getDefaultHeaders($headers),
                'json' => $data,
            ]);

            $content = $response->getBody()->getContents();
            return !empty($content) ? json_decode($content, true) : true;
        } catch (\Exception $e) {
            $this->logError('PUT', $path, $e->getMessage());
            return null;
        }
    }

    /**
     * Make a DELETE request
     *
     * @return bool True on success, false on error
     */
    protected function delete(string $path, array $headers = []): bool
    {
        try {
            $this->httpClient->delete($this->baseUrl . $path, [
                'headers' => $this->getDefaultHeaders($headers),
            ]);
            return true;
        } catch (\Exception $e) {
            $this->logError('DELETE', $path, $e->getMessage());
            return false;
        }
    }

    /**
     * Get default headers for requests
     */
    protected function getDefaultHeaders(array $additional = []): array
    {
        return array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $additional);
    }

    /**
     * Log HTTP error for debugging
     */
    protected function logError(string $method, string $path, string $message): void
    {
        error_log("[HttpProvider] [$method] $path - $message");
    }

    /**
     * Test HTTP connection to base URL
     */
    public function testConnection(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = $this->httpClient->head($this->baseUrl, [
                'timeout' => 10,
                'allow_redirects' => true,
            ]);
            return $response->getStatusCode() >= 200 && $response->getStatusCode() < 400;
        } catch (\Exception $e) {
            $this->logError('HEAD', '/', $e->getMessage());
            return false;
        }
    }

    /**
     * Format standard asset response
     */
    protected function formatAsset(string $id, string $identifier, string $status = 'active', array $metadata = []): array
    {
        return [
            'id' => $id,
            'provider' => $this->getType(),
            'asset_type' => $this->getAssetType(),
            'identifier' => $identifier,
            'status' => $status,
            'metadata' => !empty($metadata) ? json_encode($metadata) : null,
        ];
    }
}
