<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_res(['error' => 'POST required'], 405);
}

$ea_id = intval($_POST['ea_id'] ?? 0);
if (!$ea_id) {
    json_res(['error' => 'Missing ea_id'], 400);
}

$stmt = $mysqli->prepare("DELETE FROM players WHERE ea_id = ?");
$stmt->bind_param('i', $ea_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    json_res(['ok' => true, 'deleted' => $ea_id]);
} else {
    json_res(['error' => 'Player not found'], 404);
}
?>
