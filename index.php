<?php
session_start();

// === File setup ===
$counterFile = "counter_data.json";
$likesFile = "likes.txt";

// Create file if not exists
if (!file_exists($counterFile)) {
    file_put_contents($counterFile, json_encode(["total" => 0, "visitors" => []]));
}
if (!file_exists($likesFile)) {
    file_put_contents($likesFile, "0");
}

// === Load data ===
$data = json_decode(file_get_contents($counterFile), true);
$totalVisits = $data["total"];
$visitors = $data["visitors"];
$ip = $_SERVER["REMOTE_ADDR"];
$now = time();

// === Remove inactive visitors (after 30 seconds) ===
foreach ($visitors as $visitorIP => $lastActive) {
    if ($now - $lastActive > 30) unset($visitors[$visitorIP]);
}

// === Add new visitor ===
if (!isset($_SESSION["visited"])) {
    $_SESSION["visited"] = true;
    $totalVisits++;
}
$visitors[$ip] = $now;

// === Save back ===
$data["total"] = $totalVisits;
$data["visitors"] = $visitors;
file_put_contents($counterFile, json_encode($data));

// === Like System ===
if (isset($_GET["like"])) {
    $likes = (int)file_get_contents($likesFile);
    if (!isset($_SESSION["liked"])) {
        $_SESSION["liked"] = true;
        $likes++;
        file_put_contents($likesFile, $likes);
        echo json_encode(["message" => "❤️ You liked this site!", "likes" => $likes]);
        exit;
    } else {
        echo json_encode(["message" => "💖 You already liked this site!", "likes" => $likes]);
        exit;
    }
}

// === API mode (for AJAX) ===
if (isset($_GET["api"])) {
    $likes = (int)file_get_contents($likesFile);
    echo json_encode([
        "live" => count($visitors),
        "total" => $totalVisits,
        "likes" => $likes
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🌎 Live Visitors Counter</title>
<style>
body {
  font-family: "Poppins", sans-serif;
  background: linear-gradient(135deg, #000428, #004e92);
  color: white;
  text-align: center;
  padding-top: 80px;
}
h1 { font-size: 2.5rem; margin-bottom: 5px; }
#liveCount { font-size: 3.5rem; color: #00FFAA; text-shadow: 0 0 25px #00ffaa; }
#totalCount { font-size: 1.5rem; margin-top: 10px; color: #FFD700; }
#likeSection { margin-top: 25px; }
#likeBtn {
  background: #ff0066;
  border: none;
  padding: 10px 25px;
  border-radius: 25px;
  color: white;
  font-size: 1rem;
  box-shadow: 0 0 15px #ff0066;
  cursor: pointer;
  transition: 0.3s;
}
#likeBtn:hover { transform: scale(1.1); }
#likeMsg { margin-top: 8px; font-size: 1rem; color: #ffb3ff; }
footer {
  position: fixed;
  bottom: 10px;
  width: 100%;
  font-size: 14px;
  opacity: 0.7;
}
</style>
</head>
<body>
  <h1>🌍 Live Visitors Right Now</h1>
  <div id="liveCount">Loading...</div>
  <div id="totalCount">Total Visits: Loading...</div>

  <div id="likeSection">
    <button id="likeBtn">❤️ Like</button>
    <div id="likeMsg"></div>
  </div>

  <footer>Created by SAYAN | All Rights Reserved</footer>

<script>
async function loadData() {
  const res = await fetch("?api=1");
  const data = await res.json();
  document.getElementById("liveCount").innerText = data.live;
  document.getElementById("totalCount").innerText = "Total Visits: " + data.total;
  document.getElementById("likeMsg").innerText = "Total Likes: " + data.likes;
}

// Initial + update every 3s
loadData();
setInterval(loadData, 3000);

document.getElementById("likeBtn").addEventListener("click", async () => {
  const res = await fetch("?like=1");
  const data = await res.json();
  document.getElementById("likeMsg").innerText = data.message + " ❤️ Total Likes: " + data.likes;
});
</script>
</body>
</html>
