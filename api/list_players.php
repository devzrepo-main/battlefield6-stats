<?php
require __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

$result = $mysqli->query("SELECT ea_id, handle, platform, kills, deaths, wins, losses, score, last_updated FROM players ORDER BY score DESC");

if (!$result) {
    json_res(['error' => 'DB query failed'], 500);
}

$players = [];
while ($row = $result->fetch_assoc()) {
    $players[] = $row;
}

json_res(['players' => $players]);
?>

