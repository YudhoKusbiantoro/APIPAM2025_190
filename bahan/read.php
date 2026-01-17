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
    SELECT 
        b.id, 
        b.nama_bahan AS nama,
        b.volume, 
        b.expired, 
        b.kondisi, 
        b.lab_id,
        l.institusi,
        l.nama_lab
    FROM bahan b
    JOIN lab l ON b.lab_id = l.id
    WHERE b.lab_id = ?
    ORDER BY b.created_at DESC
");

$stmt->execute([$lab_id]);
$bahan = $stmt->fetchAll(PDO::FETCH_ASSOC);

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'data' => $bahan
]);
?>