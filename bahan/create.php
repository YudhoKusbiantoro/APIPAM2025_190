<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

$required = ['nama', 'volume', 'expired', 'kondisi', 'lab_id'];
foreach ($required as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "$field wajib diisi"]);
        exit;
    }
}

// ✅ Validasi lab_id
if ((int)$data['lab_id'] <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'lab_id tidak valid']);
    exit;
}
// ✅ CEK DUPLIKAT NAMA BAHAN DI LAB YANG SAMA
$stmt = $pdo->prepare("SELECT id FROM bahan WHERE nama_bahan = ? AND lab_id = ?");
$stmt->execute([$data['nama'], (int)$data['lab_id']]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Bahan sudah ada di laboratorium ini.'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO bahan (nama_bahan, volume, expired, kondisi, lab_id)
    VALUES (?, ?, ?, ?, ?)
");
$result = $stmt->execute([
    $data['nama'],
    $data['volume'],
    $data['expired'],
    $data['kondisi'],
    (int)$data['lab_id']
]);

if ($result) {
    $id = $pdo->lastInsertId();
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

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $bahan
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan bahan']);
}
?>