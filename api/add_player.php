<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_res(['error' => 'POST required'], 405);
}

$platform = strtolower(trim($_POST['platform'] ?? ''));
$username = trim($_POST['username'] ?? '');

if (!$platform || !$username) {
  json_res(['error' => 'Missing platform or username'], 400);
}
if (!$TRACKER_API_KEY || $TRACKER_API_KEY === 'YOUR_TRACKERGG_API_KEY') {
  json_res(['error' => 'Missing Tracker.gg API key in config.php'], 500);
}

[$profile, $err] = tracker_get_profile($platform, $username, $TRACKER_API_KEY);
if ($err || !$profile || !isset($profile['data'])) {
  json_res(['error' => 'Lookup failed', 'detail' => $err], 502);
}

$stats = map_stats_from_tracker($profile);
if (!$stats['ea_id']) {
  json_res(['error' => 'EA ID not found for that player'], 404);
}

// Upsert row
$stmt = $mysqli->prepare("
  INSERT INTO players (ea_id, handle, platform, kills, deaths, wins, losses, score)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?)
  ON DUPLICATE KEY UPDATE
    handle=VALUES(handle),
    platform=VALUES(platform),
    kills=VALUES(kills),
    deaths=VALUES(deaths),
    wins=VALUES(wins),
    losses=VALUES(losses),
    score=VALUES(score),
    last_updated=NOW()
");
$stmt->bind_param(
  'issiiiii',
  $stats['ea_id'],
  $stats['handle'],
  $stats['platform'],
  $stats['kills'],
  $stats['deaths'],
  $stats['wins'],
  $stats['losses'],
  $stats['score']
);
$stmt->execute();

json_res(['ok' => true, 'player' => $stats]);
