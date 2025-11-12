<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_res(['error' => 'POST required'], 405);
}
$eaId = (int)($_POST['ea_id'] ?? 0);
if (!$eaId) json_res(['error' => 'Missing ea_id'], 400);

$stmt = $mysqli->prepare("DELETE FROM players WHERE ea_id=?");
$stmt->bind_param('i', $eaId);
$stmt->execute();

json_res(['ok' => true, 'deleted' => $eaId]);
