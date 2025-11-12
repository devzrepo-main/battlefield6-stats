<?php
require __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_res(['error' => 'POST required'], 405);
}
$eaId = (int)($_POST['ea_id'] ?? 0);
if (!$eaId) json_res(['error' => 'Missing ea_id'], 400);

$row = null;
$stmt = $mysqli->prepare("SELECT handle, platform FROM players WHERE ea_id=?");
$stmt->bind_param('i', $eaId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) json_res(['error' => 'Player not found'], 404);
if (!$row['platform'] || !$row['handle']) {
  json_res(['error' => 'Cached platform/handle missing; re-add this player via add_player'], 409);
}

[$profile, $err] = tracker_get_profile($row['platform'], $row['handle'], $TRACKER_API_KEY);
if ($err || !$profile || !isset($profile['data'])) {
  json_res(['error' => 'Refresh failed', 'detail' => $err], 502);
}

$stats = map_stats_from_tracker($profile);
if ((int)$stats['ea_id'] !== $eaId) {
  // Very defensive: if the EA ID somehow differs, keep the original EA ID
  $stats['ea_id'] = $eaId;
}

$upd = $mysqli->prepare("
  UPDATE players
  SET handle=?, platform=?, kills=?, deaths=?, wins=?, losses=?, score=?, last_updated=NOW()
  WHERE ea_id=?
");
$upd->bind_param(
  'ssiiiiii',
  $stats['handle'],
  $stats['platform'],
  $stats['kills'],
  $stats['deaths'],
  $stats['wins'],
  $stats['losses'],
  $stats['score'],
  $eaId
);
$upd->execute();

json_res(['ok' => true, 'player' => $stats]);
