<?php

namespace Irabbi360\LaravelApiInspector\Support;

class AuthenticationTester
{
    /**
     * Test Bearer Token authentication
     */
    public static function testBearerAuth(string $endpoint, string $token): array
    {
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return self::makeRequest('GET', $endpoint, $headers);
    }

    /**
     * Test API Key authentication
     */
    public static function testApiKeyAuth(string $endpoint, string $apiKey, string $header = 'X-API-Key'): array
    {
        $headers = [
            $header => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return self::makeRequest('GET', $endpoint, $headers);
    }

    /**
     * Test Basic authentication
     */
    public static function testBasicAuth(string $endpoint, string $username, string $password): array
    {
        $credentials = base64_encode("{$username}:{$password}");
        $headers = [
            'Authorization' => "Basic {$credentials}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return self::makeRequest('GET', $endpoint, $headers);
    }

    /**
     * Test OAuth 2.0 authentication
     */
    public static function testOAuth2(string $tokenEndpoint, string $clientId, string $clientSecret, string $endpoint): array
    {
        // Get token
        $tokenResponse = self::getOAuth2Token($tokenEndpoint, $clientId, $clientSecret);

        if (! isset($tokenResponse['access_token'])) {
            return [
                'success' => false,
                'error' => 'Failed to obtain OAuth2 token',
                'details' => $tokenResponse,
            ];
        }

        // Use token to access endpoint
        $headers = [
            'Authorization' => "Bearer {$tokenResponse['access_token']}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return self::makeRequest('GET', $endpoint, $headers);
    }

    /**
     * Get OAuth2 token
     */
    protected static function getOAuth2Token(string $tokenEndpoint, string $clientId, string $clientSecret): array
    {
        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ];

        try {
            $response = self::makeRequest('POST', $tokenEndpoint, [], $params);

            if ($response['statusCode'] === 200 && isset($response['body']['access_token'])) {
                return $response['body'];
            }

            return ['error' => 'Invalid token response'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Test without authentication
     */
    public static function testNoAuth(string $endpoint): array
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        return self::makeRequest('GET', $endpoint, $headers);
    }

    /**
     * Test with invalid token
     */
    public static function testInvalidAuth(string $endpoint, string $invalidToken): array
    {
        $headers = [
            'Authorization' => "Bearer {$invalidToken}",
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $result = self::makeRequest('GET', $endpoint, $headers);

        return [
            'success' => false,
            'message' => 'Testing with invalid token',
            'expected_status' => [401, 403],
            'actual_status' => $result['statusCode'],
            'matches_expected' => in_array($result['statusCode'], [401, 403]),
        ];
    }

    /**
     * Make HTTP request
     */
    protected static function makeRequest(string $method, string $url, array $headers = [], ?array $body = null): array
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => self::formatHeaders($headers),
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            if ($body) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
            }

            $response = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            curl_close($curl);

            if ($error) {
                return [
                    'success' => false,
                    'statusCode' => 0,
                    'error' => $error,
                ];
            }

            $body = json_decode($response, true) ?? $response;

            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'statusCode' => $statusCode,
                'body' => $body,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'statusCode' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format headers for curl
     */
    protected static function formatHeaders(array $headers): array
    {
        $formatted = [];

        foreach ($headers as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }

        return $formatted;
    }
}
