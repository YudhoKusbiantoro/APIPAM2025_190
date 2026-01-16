<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);
$lab_id = $data['lab_id'] ?? null;

if (!$lab_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'lab_id wajib diisi']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.id, u.nama, u.username, u.role, u.lab_id, l.institusi, l.nama_lab
    FROM users u
    JOIN lab l ON u.lab_id = l.id
    WHERE u.lab_id = ? AND u.role = 'user' 
    ORDER BY u.nama
");
$stmt->execute([$lab_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode(['status' => 'success', 'data' => $users]);
?>