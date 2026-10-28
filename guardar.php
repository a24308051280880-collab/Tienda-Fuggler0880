<?php
$host = "localhost";
$dbname = "fuggler";
$usuario_db = "root";
$contrasena_db = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario_db,
        $contrasena_db,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        exit("Método no permitido.");
    }

    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $contrasena = $_POST["contrasena"] ?? "";
    $direccion = trim($_POST["direccion_envio"] ?? "");

    if (
        $nombre === "" ||
        $telefono === "" ||
        !filter_var($correo, FILTER_VALIDATE_EMAIL) ||
        $contrasena === "" ||
        $direccion === ""
    ) {
        exit("Verifica que todos los campos sean correctos.");
    }

    $hash = password_hash($contrasena, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios
            (nombre, telefono, correo, contrasena_hash,
             direccion_envio)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $nombre,
        $telefono,
        $correo,
        $hash,
        $direccion
    ]);

    echo "¡Registro guardado correctamente en MySQL!";

} catch (PDOException $e) {
    if (isset($pdo) && $e->getCode() === "23000") {
        http_response_code(409);
        exit("Ese correo ya está registrado.");
    }

    error_log($e->getMessage());
    http_response_code(500);
    echo "Ocurrió un error al guardar el registro.";
}
?>