<?php
session_start();
$file = "likes.txt";

if (!file_exists($file)) {
  file_put_contents($file, "0");
}

$likes = (int)file_get_contents($file);

if (!isset($_SESSION['liked'])) {
  $_SESSION['liked'] = true;
  $likes++;
  file_put_contents($file, $likes);
  echo "You liked this site! ❤️ Total Likes: $likes";
} else {
  echo "You already liked this site! ❤️ Total Likes: $likes";
}
?>
