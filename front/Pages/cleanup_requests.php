<?php
require 'connect.php';

// Delete binomes with status 'en_attente' older than 3 days
$sql = "DELETE FROM binome WHERE status = 'en_attente' AND DATE_ADD(date_created, INTERVAL 3 DAY) <= NOW()";
$conn->query($sql);

$conn->close();
