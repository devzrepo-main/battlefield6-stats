<?php
require_once __DIR__ . '/../config.php';

/**
 * Fetch Battlefield 6 player profile from Tracker.gg API.
 * Adds User-Agent to avoid Cloudflare blocking and returns parsed JSON.
 */
function tracker_get_profile($platform, $username, $apiKey) {
  $url = "https://api.tracker.gg/api/v2/bf6/standard/profile/$platform/$username";

  $opts = [
    "http" => [
      "method" => "GET",
      "header" =>
        "TRN-Api-Key: $apiKey\r\n" .
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36\r\n",
      "timeout" => 10
    ]
  ];
  $context = stream_context_create($opts);
  $json = @file_get_contents($url, false, $context);

  // Handle blocked, empty, or non-JSON responses
  if (!$json || strpos($json, '<html') !== false) {
    return [null, 'Cloudflare blocked or no JSON returned'];
  }

  $data = json_decode($json, true);
  if ($data === null) {
    return [null, 'Invalid JSON from Tracker.gg'];
  }

  return [$data, null];
}

/**
 * Convert Tracker.gg JSON into local database structure.
 * This maps the key stats used in the players table.
 */
function map_stats_from_tracker($profile) {
  if (!isset($profile['data']['platformInfo'])) {
    return [
      'ea_id' => null,
      'handle' => null,
      'platform' => null,
      'kills' => 0,
      'deaths' => 0,
      'wins' => 0,
      'losses' => 0,
      'score' => 0
    ];
  }

  $info = $profile['data']['platformInfo'];
  $segments = $profile['data']['segments'][0]['stats'] ?? [];

  return [
    'ea_id'    => intval($info['platformUserId'] ?? 0),
    'handle'   => $info['platformUserHandle'] ?? '',
    'platform' => strtolower($info['platformSlug'] ?? ''),
    'kills'    => intval($segments['kills']['value'] ?? 0),
    'deaths'   => intval($segments['deaths']['value'] ?? 0),
    'wins'     => intval($segments['wins']['value'] ?? 0),
    'losses'   => intval($segments['losses']['value'] ?? 0),
    'score'    => intval($segments['score']['value'] ?? 0)
  ];
}

/**
 * Simple JSON response wrapper for uniform API output.
 */
function json_res($data, $status = 200) {
  http_response_code($status);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
?>
