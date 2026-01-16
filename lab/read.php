<?php
require_once '../koneks.php';

try {
    // ✅ HAPUS created_at karena tidak ada di tabel
    $stmt = $pdo->prepare("SELECT id, institusi, nama_lab FROM lab");
    $stmt->execute();
    $labs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $labs
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil daftar lab: ' . $e->getMessage()]);
}
?>