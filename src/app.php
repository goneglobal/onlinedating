<?php
// Fake DB connection and data
require_once __DIR__ . '/../db/fake_db.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($users);
    exit;
}
