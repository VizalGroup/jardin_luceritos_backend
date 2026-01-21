<?php

include '../bd/bd.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $query = "SELECT * FROM users where id=" . $_GET['id'];
        $resultado = metodoGet($query);
        echo json_encode($resultado->fetch(PDO::FETCH_ASSOC));
        exit();
    } elseif (isset($_GET['email'])) {
        $email = $_GET['email'];
        $query = "SELECT * FROM users WHERE email = '$email'";
        $resultado = metodoGet($query);
        $user = $resultado->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo json_encode($user);
        } else {
            echo json_encode([]);
        }
        exit();
    } else {
        $query = "SELECT * FROM users";
        $resultado = metodoGet($query);
        echo json_encode($resultado->fetchAll());
        exit();
    }
}

if ($_POST['METHOD'] == 'POST') {
    unset($_POST['METHOD']);
    $first_name = $_POST['first_name'];
    $lastname = $_POST['lastname'];
    $user_role = $_POST['user_role'];
    $email = $_POST['email'];
    $clue = $_POST['clue'];
    $phone = $_POST['phone'];
    $is_activate = $_POST['is_activate'] ?? 1;
    $document_type = $_POST['document_type'];
    $document_number = $_POST['document_number'];
    $street_address = $_POST['street_address'];
    $created_at = $_POST['created_at'];
    $updated_at = $_POST['updated_at'];
    
    // Verificar si el email ya existe
    $checkEmailQuery = "SELECT COUNT(*) as count FROM users WHERE email = '$email'";
    $emailExists = metodoGet($checkEmailQuery);
    $emailCount = $emailExists->fetch(PDO::FETCH_ASSOC);
    
    if ($emailCount['count'] > 0) {
        header("HTTP/1.1 409 Conflict");
        echo json_encode([
            'error' => true,
            'message' => 'Este email ya está registrado. Por favor, utiliza otro email o inicia sesión.'
        ]);
        exit();
    }
    
    // Email no existe, proceder con el registro
    $query = "INSERT INTO users(first_name, lastname, user_role, email, clue, phone, is_activate, document_type, document_number, street_address, created_at, updated_at) VALUES ('$first_name', '$lastname', '$user_role', '$email', '$clue', '$phone', '$is_activate', '$document_type', '$document_number', '$street_address', '$created_at', '$updated_at')";
    $queryAutoIncrement = "SELECT MAX(id) as id FROM users";
    $resultado = metodoPost($query, $queryAutoIncrement);
    
    // Enviar email de bienvenida
    $to = $email;
    $subject = "¡Bienvenido a Luceritos Jardín Maternal! 🌟";
    
    $message = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>¡Bienvenido a Luceritos Jardín Maternal!</title>
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
            
            .highlight-name {
                color: #FF5ED;
                font-size: 2rem;
            }
            
            .message-text {
                color: #213472;
                font-size: 1.05rem;
                line-height: 1.8;
                margin-bottom: 25px;
                text-align: center;
            }
            
            .benefits-container {
                background: linear-gradient(135deg, rgba(255, 245, 237, 0.3) 0%, rgba(255, 229, 217, 0.3) 100%);
                border-radius: 15px;
                padding: 25px;
                margin: 30px 0;
                border-left: 5px solid #FFF5ED;
            }
            
            .benefits-title {
                color: #213472;
                font-family: 'Montserrat', sans-serif;
                font-size: 1.4rem;
                font-weight: 700;
                margin-bottom: 15px;
                text-align: center;
            }
            
            .benefits-text {
                color: #213472;
                font-size: 1.05rem;
                line-height: 1.8;
                text-align: left;
                margin-bottom: 15px;
            }
            
            .coming-soon {
                background: rgba(255, 245, 237, 0.5);
                border-radius: 8px;
                padding: 12px 20px;
                margin-top: 15px;
                text-align: center;
            }
            
            .coming-soon-text {
                color: #213472;
                font-size: 0.95rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            
            .cta-container {
                text-align: center;
                margin: 35px 0;
            }
            
            .cta-button {
                display: inline-block;
                background: linear-gradient(135deg, #FFF5ED 0%, #FFE5D9 100%);
                color: #213472 !important;
                font-family: 'Montserrat', sans-serif;
                font-size: 1.1rem;
                font-weight: 600;
                text-decoration: none;
                padding: 18px 45px;
                border-radius: 30px;
                box-shadow: 0 8px 25px rgba(255, 245, 237, 0.4);
                transition: all 0.3s ease;
                border: 2px solid #213472;
            }
            
            .cta-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 12px 35px rgba(255, 245, 237, 0.6);
            }
            
            .divider {
                height: 3px;
                background: linear-gradient(to right, #FFF5ED, #FFE5D9, #FFF5ED);
                margin: 30px 0;
                border-radius: 2px;
            }
            
            .contact-info {
                background: rgba(255, 245, 237, 0.3);
                border-radius: 12px;
                padding: 30px 25px;
                margin: 25px 0 40px 0;
                text-align: center;
                border: 1px solid rgba(255, 245, 237, 0.5);
            }
            
            .contact-info p {
                color: #213472;
                font-size: 1rem;
                margin: 12px 0;
            }
            
            .contact-info a {
                color: #213472;
                text-decoration: none;
                font-weight: 600;
            }
            
            .contact-info a:hover {
                text-decoration: underline;
            }
            
            .location-info {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid rgba(255, 245, 237, 0.5);
            }
            
            .whatsapp-container {
                margin: 20px 0;
            }
            
            .whatsapp-button {
                display: inline-block;
                background: #25D366;
                color: white !important;
                padding: 12px 25px;
                border-radius: 30px;
                text-decoration: none;
                font-size: 1rem;
                font-weight: 600;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(37, 211, 102, 0.3);
            }
            
            .whatsapp-button:hover {
                background: #20BA5A;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37, 211, 102, 0.5);
                text-decoration: none;
            }
            
            .location-icon {
                width: 18px;
                height: 18px;
                vertical-align: middle;
                margin-right: 5px;
            }
            
            .social-container {
                margin-top: 25px;
                padding-top: 20px;
                border-top: 1px solid rgba(255, 245, 237, 0.5);
            }
            
            .social-title {
                color: #213472;
                font-size: 1rem;
                font-weight: 600;
                margin-bottom: 15px;
            }
            
            .social-icons {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
            }
            
            .social-link {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                text-decoration: none;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            
            .social-link img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .social-link:hover {
                transform: translateY(-3px);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            }
            
            .automated-message {
                background: rgba(255, 245, 237, 0.3);
                border-left: 4px solid #FFF5ED;
                padding: 15px 20px;
                margin: 30px 0 0 0;
                border-radius: 8px;
            }
            
            .automated-message p {
                color: #213472;
                font-size: 0.9rem;
                margin: 0;
                font-style: italic;
            }
            
            @media (max-width: 600px) {
                .email-container {
                    margin: 10px;
                    border-radius: 15px;
                }
                
                .email-header, .email-body {
                    padding: 25px 20px;
                }
                
                .header-title {
                    font-size: 1.6rem;
                }
                
                .greeting {
                    font-size: 1.5rem;
                }
                
                .highlight-name {
                    font-size: 1.7rem;
                }
                
                .cta-button {
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
                <h2 class='greeting'>
                    ¡Hola <span class='highlight-name'>" . htmlspecialchars($first_name) . "</span>!
                </h2>
                
                <p class='message-text'>
                    ¡Bienvenido a la familia de Luceritos Jardín Maternal! Nos llena de alegría que hayas decidido confiar en nosotros para el cuidado y desarrollo de tu pequeño. Estamos comprometidos a brindar un espacio seguro, cálido y estimulante.
                </p>
                
                <div class='benefits-container'>
                    <h3 class='benefits-title'>🌟 Portal Luceritos</h3>
                    <p class='benefits-text'>
                        En nuestro portal <strong>luceritosjardinmaternal.com</strong> próximamente tendrás acceso completo a toda la información de tu hijo/a. Podrás ver actividades diarias, seguimiento del desarrollo, galería de fotos y comunicación directa con las educadoras. Todo centralizado en un solo lugar para que estés siempre conectado con nosotros.
                    </p>
                </div>
                
                <div class='cta-container'>
                    <a href='https://luceritosjardinmaternal.com' class='cta-button'>
                        Ir al Sitio Web
                    </a>
                </div>
                
                <div class='divider'></div>
                
                <div class='contact-info'>
                    <p><strong><img src='https://w7.pngwing.com/pngs/457/630/png-transparent-location-logo-location-computer-icons-symbol-location-miscellaneous-angle-heart-thumbnail.png' alt='Ubicación' class='location-icon'> Dirección</strong></p>
                    <p>Pedro Simón Laplace N°5640<br>B° Villa Belgrano</p>
                    
                    <div class='whatsapp-container'>
                        <p><strong>Contáctanos por WhatsApp</strong></p>
                        <a href='https://wa.me/5493512489805' class='whatsapp-button' target='_blank'>
                            WhatsApp
                        </a>
                    </div>
                    
                    <div class='social-container'>
                        <p class='social-title'>Síguenos en Instagram:</p>
                        <div class='social-icons'>
                            <a href='https://www.instagram.com/luceritos_espacio/' class='social-link' target='_blank' title='Instagram'>
                                <img src='https://img.freepik.com/psd-premium/logo-instagram_971166-164438.jpg?semt=ais_hybrid&w=740&q=80' alt='Instagram'>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class='automated-message'>
                    <p>Este es un mensaje automático. Por favor, no responder a este correo.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: sistema@luceritosjardinmaternal.com" . "\r\n";
    
    // Enviar el email (no bloquear si falla)
    $emailSent = mail($to, $subject, $message, $headers);
    
    // Responder con éxito independientemente del email
    echo json_encode([
        'success' => true,
        'message' => 'Usuario registrado exitosamente.',
        'user_id' => $resultado['id'],
        'email_sent' => $emailSent
    ]);
    
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'PUT') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $first_name = $_POST['first_name'];
    $lastname = $_POST['lastname'];
    $user_role = $_POST['user_role'];
    $email = $_POST['email'];
    $clue = $_POST['clue'];
    $phone = $_POST['phone'];
    $is_activate = $_POST['is_activate'];
    $document_type = $_POST['document_type'];
    $document_number = $_POST['document_number'];
    $street_address = $_POST['street_address'];
    $created_at = $_POST['created_at'];
    $updated_at = $_POST['updated_at'];
    
    $query = "UPDATE users SET first_name='$first_name', lastname='$lastname', user_role='$user_role', email='$email', clue='$clue', phone='$phone', is_activate='$is_activate', document_type='$document_type', document_number='$document_number', street_address='$street_address', created_at='$created_at', updated_at='$updated_at' WHERE id='$id'";
    $resultado = metodoPut($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'DELETE') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $query = "DELETE FROM users WHERE id='$id'";
    $resultado = metodoDelete($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

header("HTTP/1.1 400 Bad Request");

?>
