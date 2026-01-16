<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

$required = ['id', 'nama', 'volume', 'expired', 'kondisi', 'lab_id'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "$field wajib diisi"]);
        exit;
    }
}
// ✅ CEK DUPLIKAT NAMA (KECUALI DIRI SENDIRI)
$stmt = $pdo->prepare("
    SELECT id FROM bahan 
    WHERE nama_bahan = ? AND lab_id = ? AND id != ?
");
$stmt->execute([$data['nama'], $data['lab_id'], $data['id']]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Bahan sudah ada di laboratorium ini.'
    ]);
    exit;
}
$stmt = $pdo->prepare("
    UPDATE bahan
    SET nama_bahan = ?, volume = ?, expired = ?, kondisi = ?, lab_id = ?
    WHERE id = ?
");
$result = $stmt->execute([
    $data['nama'],
    $data['volume'],
    $data['expired'],
    $data['kondisi'],
    $data['lab_id'],
    $data['id']
]);

if ($result) {
    $stmt = $pdo->prepare("
        SELECT id, nama, volume, expired, kondisi, lab_id
        FROM bahan
        WHERE id = ?
    ");
    $stmt->execute([$data['id']]);
    $bahan = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $bahan
    ]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui bahan']);
}
?>