<?php

include '../bd/bd.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Endpoint para verificar token - DEBE IR ANTES del GET general
if (isset($_GET['verify_token'])) {
    $token = $_GET['verify_token'];
    
    $query = "SELECT prt.*, u.email, u.first_name, u.lastname 
              FROM password_reset_tokens prt 
              JOIN users u ON prt.user_id = u.id 
              WHERE prt.token='$token' AND prt.used=0 AND prt.expires_at > NOW()";
    
    $resultado = metodoGet($query);
    $tokenData = $resultado->fetch(PDO::FETCH_ASSOC);
    
    if ($tokenData) {
        echo json_encode([
            'valid' => true,
            'user_id' => $tokenData['user_id'],
            'email' => $tokenData['email'],
            'first_name' => $tokenData['first_name'],
            'lastname' => $tokenData['lastname']
        ]);
    } else {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode([
            'valid' => false,
            'message' => 'Token inválido o expirado.'
        ]); 
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $query = "SELECT prt.*, u.email, u.first_name, u.lastname 
                  FROM password_reset_tokens prt 
                  JOIN users u ON prt.user_id = u.id 
                  WHERE prt.id=" . $_GET['id'];
        $resultado = metodoGet($query);
        echo json_encode($resultado->fetch(PDO::FETCH_ASSOC));
        exit();
    } else {
        $query = "SELECT prt.*, u.email, u.first_name, u.lastname 
                  FROM password_reset_tokens prt 
                  JOIN users u ON prt.user_id = u.id";
        $resultado = metodoGet($query);
        echo json_encode($resultado->fetchAll());
        exit();
    }
}

if ($_POST['METHOD'] == 'POST') {
    unset($_POST['METHOD']);
    $user_id = $_POST['user_id'];
    
    // Verificar si el usuario existe y obtener sus datos
    $checkUserQuery = "SELECT id, first_name, lastname, email FROM users WHERE id = '$user_id'";
    $userExists = metodoGet($checkUserQuery);
    $userData = $userExists->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        header("HTTP/1.1 404 Not Found");
        echo json_encode([
            'error' => true,
            'message' => 'Usuario no encontrado en nuestros registros.'
        ]);
        exit();
    }
    
    // Generar token único
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token válido por 1 hora
    
    // Insertar token en la base de datos
    $query = "INSERT INTO password_reset_tokens(user_id, token, expires_at, used) VALUES ('$user_id', '$token', '$expires_at', 0)";
    $queryAutoIncrement = "SELECT MAX(id) as id FROM password_reset_tokens";
    $resultado = metodoPost($query, $queryAutoIncrement);
    
    // Enviar email
    $to = $userData['email'];
    $subject = "Recuperación de contraseña - Luceritos Jardín Maternal";
    
    // URL de desarrollo local 
    // $reset_link = "http://localhost:5173/restablecer_contrasena?token=" . $token;
    
    // URL de producción 
    $reset_link = "https://luceritosjardinmaternal.com/restablecer_contrasena?token=" . $token;
    
    $message = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Recuperación de contraseña - Luceritos Jardín Maternal</title>
        <link href='https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Poppins', sans-serif;
                line-height: 1.6;
                color: #213472;
                background: linear-gradient(135deg, #FFF5ED 0%, #FFE5D9 100%);
                min-height: 100vh;
                padding: 20px;
            }
            
            .email-container {
                max-width: 600px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 20px 60px rgba(255, 245, 237, 0.3);
                border: 3px solid #FFF5ED;
            }
            
            .email-header {
                background: linear-gradient(135deg, #FFF5ED 0%, #FFE5D9 100%);
                padding: 40px 30px;
                text-align: center;
                position: relative;
            }
            
            .logo-container {
                background: white;
                width: 140px;
                height: 140px;
                border-radius: 50%;
                margin: 0 auto 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
                border: 4px solid rgba(255, 255, 255, 0.5);
                padding: 10px;
            }
            
            .logo-container img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            
            .header-title {
                color: #213472;
                font-family: 'Montserrat', sans-serif;
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 10px;
                text-shadow: 2px 2px 4px rgba(255, 255, 255, 0.3);
            }
            
            .email-subtitle {
                color: #213472;
                font-size: 1.1rem;
                font-weight: 400;
            }
            
            .email-body {
                padding: 40px 30px;
                background: #ffffff;
            }
            
            .greeting {
                color: #213472;
                font-family: 'Montserrat', sans-serif;
                font-size: 1.8rem;
                font-weight: 700;
                margin-bottom: 20px;
                text-align: center;
            }
            
            .message-text {
                color: #213472;
                font-size: 1rem;
                line-height: 1.8;
                margin-bottom: 30px;
                text-align: center;
            }
            
            .cta-container {
                text-align: center;
                margin: 40px 0;
            }
            
            .reset-button {
                display: inline-block;
                background: linear-gradient(135deg, #FFF5ED 0%, #FFE5D9 100%);
                color: #213472 !important;
                font-family: 'Montserrat', sans-serif;
                font-size: 1.1rem;
                font-weight: 600;
                text-decoration: none;
                padding: 18px 40px;
                border-radius: 30px;
                box-shadow: 0 8px 25px rgba(255, 245, 237, 0.4);
                transition: all 0.3s ease;
                border: 2px solid #213472;
            }
            
            .reset-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 12px 35px rgba(255, 245, 237, 0.6);
            }
            
            .info-box {
                background: rgba(255, 245, 237, 0.3);
                border-radius: 15px;
                padding: 20px;
                margin: 30px 0;
                border-left: 5px solid #FFF5ED;
            }
            
            .info-text {
                color: #213472;
                font-size: 0.9rem;
                margin: 0;
            }
            
            .expiry-warning {
                color: #ff6b6b;
                font-weight: 600;
            }
            
            .email-footer {
                background: rgba(255, 245, 237, 0.3);
                padding: 30px;
                text-align: center;
                border-top: 1px solid rgba(255, 245, 237, 0.5);
            }
            
            .footer-text {
                color: #213472;
                font-size: 0.9rem;
                margin-bottom: 15px;
            }
            
            .signature {
                color: #213472;
                font-family: 'Montserrat', sans-serif;
                font-size: 1.1rem;
                font-weight: 700;
                margin-top: 20px;
            }
            
            .divider {
                height: 3px;
                background: linear-gradient(to right, #FFF5ED, #FFE5D9, #FFF5ED);
                margin: 30px 0;
                border-radius: 2px;
            }
            
            @media (max-width: 600px) {
                .email-container {
                    margin: 10px;
                    border-radius: 15px;
                }
                
                .email-header, .email-body, .email-footer {
                    padding: 25px 20px;
                }
                
                .header-title {
                    font-size: 1.6rem;
                }
                
                .greeting {
                    font-size: 1.5rem;
                }
                
                .reset-button {
                    padding: 15px 30px;
                    font-size: 1rem;
                }
                
                .logo-container {
                    width: 120px;
                    height: 120px;
                }
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <div class='logo-container'>
                    <img src='https://luceritosjardinmaternal.com/logo.jpeg' alt='Luceritos Jardín Maternal Logo'>
                </div>
                <h1 class='header-title'>Luceritos Jardín Maternal</h1>
                <div class='email-subtitle'>Donde los pequeños brillan con luz propia</div>
            </div>
            
            <div class='email-body'>
                <h2 class='greeting'>¡Hola " . htmlspecialchars($userData['first_name']) . " " . htmlspecialchars($userData['lastname']) . "!</h2>
                
                <p class='message-text'>
                    Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en Luceritos Jardín Maternal.
                </p>
                
                <div class='cta-container'>
                    <a href='" . $reset_link . "' class='reset-button'>
                        Restablecer mi contraseña
                    </a>
                </div>
                
                <div class='info-box'>
                    <p class='info-text'>
                        <strong>Importante:</strong> <span class='expiry-warning'>Este enlace expirará en 1 hora</span> por motivos de seguridad.
                    </p>
                </div>
                
                <div class='divider'></div>
                
                <p class='message-text'>
                    Si no solicitaste este cambio, puedes ignorar este email de forma segura. Tu contraseña no será modificada.
                </p>
            </div>
            
            <div class='email-footer'>
                <p class='footer-text'>
                    ¿Necesitas ayuda? Contáctanos en 
                    <a href='mailto:sistema@luceritosjardinmaternal.com' style='color: #213472; text-decoration: none;'>sistema@luceritosjardinmaternal.com</a>
                </p>
                
                <div class='signature'>
                    Saludos cordiales,<br>
                    <strong>Equipo de Luceritos Jardín Maternal</strong>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: sistema@luceritosjardinmaternal.com" . "\r\n";
    
    $emailSent = mail($to, $subject, $message, $headers);
    
    if ($emailSent) {
        echo json_encode([
            'success' => true,
            'message' => 'Email de recuperación enviado correctamente.',
            'token_id' => $resultado['id']
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Token creado pero error al enviar el email.'
        ]);
    }
    
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'PUT') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $user_id = $_POST['user_id'];
    $token = $_POST['token'];
    $expires_at = $_POST['expires_at'];
    $used = isset($_POST['used']) ? $_POST['used'] : 0;
    
    $query = "UPDATE password_reset_tokens SET user_id='$user_id', token='$token', expires_at='$expires_at', used='$used' WHERE id='$id'";
    $resultado = metodoPut($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'DELETE') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $query = "DELETE FROM password_reset_tokens WHERE id='$id'";
    $resultado = metodoDelete($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

header("HTTP/1.1 400 Bad Request");

?>
