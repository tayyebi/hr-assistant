<?php

namespace HRAssistant\Core;

/**
 * HTTP Provider Base Class
 * Provides common HTTP functionality for API-based providers
 * 
 * Uses custom HttpClient - no external dependencies required
 */
abstract class HttpProvider extends AbstractProvider
{
    /**
     * @var HttpClient Custom HTTP Client
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
        
        // Use our custom HTTP client
        $this->httpClient = new HttpClient([
            'timeout' => 30,
            'verify' => true,
        ]);
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

            if (!$response->isSuccessful()) {
                $this->logError('GET', $path, 'HTTP ' . $response->getStatusCode());
                return null;
            }

            $content = $response->getBody()->getContents();
            return !empty($content) ? json_decode($content, true) : [];
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

            if (!$response->isSuccessful()) {
                $this->logError('POST', $path, 'HTTP ' . $response->getStatusCode());
                return null;
            }

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

            if (!$response->isSuccessful()) {
                $this->logError('PUT', $path, 'HTTP ' . $response->getStatusCode());
                return null;
            }

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
            $response = $this->httpClient->delete($this->baseUrl . $path, [
                'headers' => $this->getDefaultHeaders($headers),
            ]);
            
            return $response->isSuccessful();
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
