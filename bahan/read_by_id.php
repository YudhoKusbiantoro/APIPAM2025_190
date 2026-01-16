<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID wajib diisi']);
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
    WHERE b.id = ?
");
$stmt->execute([$id]);
$bahan = $stmt->fetch(PDO::FETCH_ASSOC);

if ($bahan) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $bahan
    ]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Bahan tidak ditemukan']);
}
?>