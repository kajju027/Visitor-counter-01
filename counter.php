<?php
session_start();

// Files for storage
$liveFile = "live.json";
$totalFile = "total.txt";

// Initialize if missing
if (!file_exists($liveFile)) file_put_contents($liveFile, json_encode([]));
if (!file_exists($totalFile)) file_put_contents($totalFile, "0");

// Get data
$live = json_decode(file_get_contents($liveFile), true);
$total = (int)file_get_contents($totalFile);
$ip = $_SERVER['REMOTE_ADDR'];
$time = time();

// Remove old sessions (after 30 seconds inactive)
foreach ($live as $key => $lastActive) {
  if ($time - $lastActive > 30) unset($live[$key]);
}

// Add/refresh current user
$live[$ip] = $time;

// Increase total count if new visitor
if (!isset($_SESSION['visited'])) {
  $_SESSION['visited'] = true;
  $total++;
  file_put_contents($totalFile, $total);
}

// Save live list
file_put_contents($liveFile, json_encode($live));

// Return JSON data
echo json_encode([
  "live" => count($live),
  "total" => $total
]);
?>
