<?php
// Redirect to channel page
$userId = (int)($_GET['id'] ?? 0);
header("Location: /channel.php?id=$userId");
exit();
?>
