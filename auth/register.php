<?php
// auth/register.php
header('Content-Type: application/json');

require_once '../koneks.php';

$data = json_decode(file_get_contents('php://input'), true);

// === 1. CEK KELENGKAPAN DATA ===
if (!$data || !isset($data['nama']) || !isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Semua data harus diisi.']);
    exit;
}

$nama = trim($data['nama']);
$username = trim($data['username']);
$password = $data['password'];

// === 2. VALIDASI USERNAME ===
if (!preg_match('/^[a-zA-Z0-9_-]{6,}$/', $username)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Username minimal 6 karakter dan hanya boleh huruf, angka, - atau _.'
    ]);
    exit;
}

// === 3. VALIDASI PASSWORD ===
// === 3. VALIDASI PASSWORD ===
if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $password)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Password minimal 6 karakter dan hanya boleh huruf dan angka.'
    ]);
    exit;
}

// === 4. CEK DUPLIKAT USERNAME ===
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan.']);
    exit;
}

// === 5. PROSES REGISTRASI ===
try {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    if (isset($data['institusi']) && isset($data['nama_lab'])) {
        // Admin: buat lab baru
        $stmt = $pdo->prepare("INSERT INTO lab (institusi, nama_lab) VALUES (?, ?)");
        $stmt->execute([$data['institusi'], $data['nama_lab']]);
        $lab_id = $pdo->lastInsertId();
        $role = 'admin';
    } else {
        // User: pakai lab_id yang dikirim
        $lab_id = $data['lab_id'] ?? null;
        $role = 'user';
        
        if (!$lab_id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Lab ID wajib diisi untuk user.']);
            exit;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role, lab_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $username, $hashedPassword, $role, $lab_id]);

    http_response_code(201);
    echo json_encode(['status' => 'success', 'message' => 'Registrasi berhasil!']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data.']);
}
?>