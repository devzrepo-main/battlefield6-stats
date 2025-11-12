<?php
require __DIR__ . '/../config.php';

$res = $mysqli->query("SELECT ea_id, handle, platform, kills, deaths, wins, losses, score, last_updated
                       FROM players ORDER BY kills DESC, score DESC LIMIT 500");
$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = $row;
}
json_res(['players' => $out]);
