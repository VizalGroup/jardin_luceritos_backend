<?php

include '../bd/bd.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['METHOD']) && $_POST['METHOD'] == 'LOGIN_USER') {
        unset($_POST['METHOD']);
        $email = $_POST['email'];
        $clue = $_POST['clue'];

        // Verificar si existe el email
        $queryEmail = "SELECT * FROM users WHERE email='$email'";
        $resultadoEmail = metodoGet($queryEmail);

        if ($resultadoEmail) {
            $userEmail = $resultadoEmail->fetch(PDO::FETCH_ASSOC);
            if ($userEmail) {
                // Verificar si la cuenta está activa
                if ($userEmail['is_activate'] == 0) {
                    error_log("❌ AUTH FAILED: Cuenta desactivada - " . $email);
                    echo json_encode(array("error" => "Esta cuenta ha sido desactivada. Contacte al administrador."));
                    header("HTTP/1.1 403 Forbidden");
                    exit();
                }

                // Verificar la contraseña usando password_verify
                $passwordMatch = password_verify($clue, $userEmail['clue']);

                if ($passwordMatch) {
                    // Agregar timestamp de expiración (48 horas = 172800 segundos)
                    $userEmail['login_time'] = time();
                    $userEmail['expires_at'] = time() + 172800; // 48 horas
                    
                    error_log("✅ AUTH SUCCESS: Usuario " . $userEmail['first_name'] . " " . $userEmail['lastname'] . " logueado (expira en 48h)");
                    echo json_encode($userEmail);
                    header("HTTP/1.1 200 OK");
                    exit();
                } else {
                    error_log("❌ AUTH FAILED: Password incorrecta para " . $email);
                    echo json_encode(array("error" => "Credenciales incorrectas"));
                    header("HTTP/1.1 401 Unauthorized");
                    exit();
                }
            } else {
                error_log("❌ AUTH FAILED: Email no registrado - " . $email);
                echo json_encode(array("error" => "Email no registrado"));
                header("HTTP/1.1 401 Unauthorized");
                exit();
            }
        } else {
            error_log("💥 AUTH ERROR: Error en query de email");
            echo json_encode(array("error" => "Error en la base de datos"));
            header("HTTP/1.1 500 Internal Server Error");
            exit();
        }
    }
}

echo json_encode(array("error" => "Solicitud inválida"));
header("HTTP/1.1 400 Bad Request");

?>