<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

$required = ['id', 'nama', 'jumlah', 'terakhir_kalibrasi', 'interval_kalibrasi', 'satuan_interval', 'kondisi', 'lab_id'];
foreach ($required as $field) {
    if (!isset($data[$field]) || ($field !== 'terakhir_kalibrasi' && empty($data[$field]))) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "$field wajib diisi"]);
        exit;
    }
}
// ✅ CEK DUPLIKAT NAMA ALAT (KECUALI DIRI SENDIRI)
$stmt = $pdo->prepare("
    SELECT id FROM alat 
    WHERE nama_alat = ? AND lab_id = ? AND id != ?
");
$stmt->execute([$data['nama'], (int)$data['lab_id'], (int)$data['id']]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nama alat sudah ada di laboratorium ini.'
    ]);
    exit;
}

$stmt = $pdo->prepare("
    UPDATE alat 
    SET nama_alat = ?, jumlah = ?, terakhir_kalibrasi = ?, interval_kalibrasi = ?, satuan_interval = ?, kondisi = ?, lab_id = ?
    WHERE id = ?
");
$result = $stmt->execute([
    $data['nama'],
    (int)$data['jumlah'],
    $data['terakhir_kalibrasi'] ?: null,
    (int)$data['interval_kalibrasi'],
    $data['satuan_interval'],
    $data['kondisi'],
    (int)$data['lab_id'],
    (int)$data['id']
]);

if ($result) {
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

    http_response_code(200);
    echo json_encode(['status' => 'success', 'data' => $alat]);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate alat']);
}
?>