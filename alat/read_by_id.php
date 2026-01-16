<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'id wajib diisi']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        a.id, 
        a.nama_alat AS nama,
        a.jumlah,
        a.terakhir_kalibrasi,
        a.interval_kalibrasi,
        a.satuan_interval,
        a.kondisi,
        a.lab_id,
        l.institusi,
        l.nama_lab
    FROM alat a
    JOIN lab l ON a.lab_id = l.id
    WHERE a.id = ?
");
$stmt->execute([(int)$data['id']]);
$alat = $stmt->fetch(PDO::FETCH_ASSOC);

if ($alat) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $alat]);
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Alat tidak ditemukan']);
}
?>