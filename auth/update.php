<?php
require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

$required = ['id', 'nama', 'username', 'role'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => "$field wajib diisi"]);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT lab_id FROM users WHERE id = ?");
$stmt->execute([$data['id']]);
$targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$targetUser) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
    exit;
}

$lab_id = $targetUser['lab_id'];

// Cek duplikat username di lab yang sama
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ? AND lab_id = ?");
$stmt->execute([$data['username'], $data['id'], $lab_id]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan di lab ini']);
    exit;
}

// Siapkan field update (tanpa lab_id)
$fields = ['nama', 'username', 'role'];
$params = [$data['nama'], $data['username'], $data['role']];

if (!empty($data['password'])) {
    $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
    $fields[] = 'password';
    $params[] = $hashedPassword;
}

$params[] = $data['id'];

$fieldPlaceholders = implode(' = ?, ', $fields) . ' = ?';
$query = "UPDATE users SET $fieldPlaceholders WHERE id = ?";

$stmt = $pdo->prepare($query);
if ($stmt->execute($params)) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.nama, u.username, u.role, u.lab_id, l.institusi, l.nama_lab
        FROM users u
        JOIN lab l ON u.lab_id = l.id
        WHERE u.id = ?
    ");
    $stmt->execute([$data['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        http_response_code(200);
        echo json_encode([
        'status' => 'success',
        'message' => 'User berhasil diperbarui', 
        'data' => $user]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data setelah update']);
    }
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui user']);
}
?>