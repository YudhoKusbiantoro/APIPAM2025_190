<?php
require_once '../koneks.php';

// Ambil data dari POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username dan password wajib diisi.']);
    exit;
}

$username = trim($data['username']);
$password = $data['password'];

// Cari user + data lab dengan JOIN
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.nama, 
        u.username, 
        u.password, 
        u.role, 
        u.lab_id,
        l.institusi,
        l.nama_lab
    FROM users u
    INNER JOIN lab l ON u.lab_id = l.id
    WHERE u.username = ?
");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah.']);
    exit;
}

// Verifikasi password
if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Username atau password salah.']);
    exit;
}

// Hapus password dari respons
unset($user['password']);

http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Login berhasil!',
    'data' => $user
]);
?>