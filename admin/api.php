<?php
/**
 * Genius Landings Admin — helper para consumir las APIs internas.
 * Usar: require_once 'api.php';
 */

define('BUDGET_MANAGER_URL', 'http://localhost:8080');
define('LANDING_CRM_URL',    'http://localhost:3000');

function api_get(string $url): array {
    $context  = stream_context_create(['http' => ['timeout' => 3]]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) return [];
    return json_decode($response, true) ?? [];
}

function get_campaigns(?string $client = null): array {
    $url = BUDGET_MANAGER_URL . '/api/campaigns';
    if ($client) $url .= '?client=' . urlencode($client);
    return api_get($url);
}

function get_landings(?string $client = null): array {
    $url = LANDING_CRM_URL . '/api/landings';
    if ($client) $url .= '?client=' . urlencode($client);
    return api_get($url);
}

function get_leads(int $landing_id): array {
    return api_get(LANDING_CRM_URL . '/api/landings/' . $landing_id . '/leads');
}

function post_landing(array $payload): array {
    $url = LANDING_CRM_URL . '/api/landings';

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_TIMEOUT        => 10,
    ]);

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($code === 201) {
        return ['ok' => true, 'data' => json_decode($body, true)];
    }
    if ($code === 404) {
        return ['ok' => false, 'error' => 'Template no encontrado en el CRM.'];
    }

    $data = json_decode($body, true);
    $detalle = $data['message'] ?? $data['error'] ?? $body;
    return ['ok' => false, 'error' => "HTTP $code — $detalle"];
}