<?php
// Mostrar errores para depuración (desactiva en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $cerFile = $_FILES['cer'] ?? null;
    $keyFile = $_FILES['key'] ?? null;
    $password = $_POST['password'] ?? '';
    $rfc = strtoupper(trim($_POST['rfc'] ?? ''));

    if (!$cerFile || !$keyFile || empty($password) || empty($rfc)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

    // Validar formato del RFC
    if (strlen($rfc) > 13 || !preg_match('/^[A-Z0-9]{12,13}$/i', $rfc)) {
        echo "El RFC proporcionado no es válido.";
        exit;
    }

    $cerPath = $uploadDir . basename($cerFile['name']);
    $keyPath = $uploadDir . basename($keyFile['name']);

    if (!move_uploaded_file($cerFile['tmp_name'], $cerPath)) {
        echo "Error al subir el archivo .cer";
        exit;
    }
    if (!move_uploaded_file($keyFile['tmp_name'], $keyPath)) {
        echo "Error al subir el archivo .key";
        exit;
    }

    // Validar archivo .cer usando opens1sl_x509_read()
    $cerContent = file_get_contents($cerPath);
    if (!$cerContent || !openssl_x509_read($cerContent)) {
        echo "El archivo .cer no es válido.";
        exit;
    }

    // Validar archivo .key
    $keyContent = file_get_contents($keyPath);
    if (!$keyContent || !openssl_pkey_get_private($keyContent, $password)) {
        echo "El archivo .key o la contraseña no son válidos.";
        exit;
    }

    // Conectar a la base de datos
    $host = 'localhost';
    $user = 'root';
    $passwordDB = 'papa';
    $dbname = 'validation_db';

    $conn = new mysqli($host, $user, $passwordDB, $dbname);
    if ($conn->connect_error) {
        echo "Error de conexión a la base de datos: " . $conn->connect_error;
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO certificates (rfc, cer_file, key_file) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $rfc, $cerPath, $keyPath);

    if ($stmt->execute()) {
        echo "Certificados validados exitosamente.";
    } else {
        echo "Error al guardar en la base de datos: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Acceso no válido.";
    exit;
}
?>
