<?php
require 'db.php';

// 取得最後一筆資料
$stmtLast = $pdo->query("SELECT * FROM dht ORDER BY datetime DESC LIMIT 1");
$latest = $stmtLast->fetch();

// 取得最近10筆資料（照時間排序）
$stmtChart = $pdo->query("SELECT * FROM dht ORDER BY datetime DESC LIMIT 10");
$data = $stmtChart->fetchAll();
$data = array_reverse($data); // 時間由舊到新

echo json_encode([
    'latest' => $latest,
    'chart' => $data
]);
?>
