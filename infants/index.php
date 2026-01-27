<?php

include '../bd/bd.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        // Consulta para un infante específico por ID con información de usuario y tarifa
        $query = "SELECT i.*, 
                  u.first_name as user_first_name, u.lastname as user_lastname,
                  t.number_of_hours, t.price
                  FROM infants i
                  LEFT JOIN users u ON i.user_id = u.id
                  LEFT JOIN tariffs t ON i.id_tariff = t.id
                  WHERE i.id = " . $_GET['id'];
        
        $resultado = metodoGet($query);
        $result = $resultado->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Decodificar el campo schedule si existe
            if (isset($result['schedule'])) {
                $result['schedule'] = json_decode($result['schedule']);
            }
            
            // Crear los objetos anidados y eliminar los campos originales
            $result['user'] = array(
                'first_name' => $result['user_first_name'],
                'lastname' => $result['user_lastname']
            );
            
            $result['tariff'] = array(
                'number_of_hours' => $result['number_of_hours'],
                'price' => $result['price']
            );
            
            // Eliminar los campos que ya se han incluido en los objetos anidados
            unset($result['user_first_name'], $result['user_lastname'], 
                  $result['number_of_hours'], $result['price']);
            
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'Infant not found']);
        }
    } else {
        // Consulta para todos los infantes con información de usuario y tarifa
        $query = "SELECT i.*, 
                  u.first_name as user_first_name, u.lastname as user_lastname,
                  t.number_of_hours, t.price
                  FROM infants i
                  LEFT JOIN users u ON i.user_id = u.id
                  LEFT JOIN tariffs t ON i.id_tariff = t.id";
        
        $resultado = metodoGet($query);
        $results = $resultado->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as &$result) {
            // Decodificar el campo schedule si existe
            if (isset($result['schedule'])) {
                $result['schedule'] = json_decode($result['schedule']);
            }
            
            // Crear los objetos anidados y eliminar los campos originales
            $result['user'] = array(
                'first_name' => $result['user_first_name'],
                'lastname' => $result['user_lastname']
            );
            
            $result['tariff'] = array(
                'number_of_hours' => $result['number_of_hours'],
                'price' => $result['price']
            );
            
            // Eliminar los campos que ya se han incluido en los objetos anidados
            unset($result['user_first_name'], $result['user_lastname'], 
                  $result['number_of_hours'], $result['price']);
        }
        
        echo json_encode($results);
    }
    exit(); // Salir después de enviar la respuesta
}

if ($_POST['METHOD'] == 'POST') {
    unset($_POST['METHOD']);
    $document_number = $_POST['document_number'];
    $first_name = $_POST['first_name'];
    $lastname = $_POST['lastname'];
    $birthdate = $_POST['birthdate'];
    $current_state = $_POST['current_state'];
    $schedule = $_POST['schedule'];
    $user_id = $_POST['user_id'];
    $last_update = $_POST['last_update'];
    $id_tariff = $_POST['id_tariff'];
    $room = $_POST['room'];
    $location = $_POST['location'];
    
    $query = "INSERT INTO infants(document_number, first_name, lastname, birthdate, current_state, schedule, user_id, last_update, id_tariff, room, location) 
              VALUES ('$document_number', '$first_name', '$lastname', '$birthdate', '$current_state', '$schedule', '$user_id', '$last_update', '$id_tariff', '$room', '$location')";
    
    $queryAutoIncrement = "SELECT MAX(id) as id FROM infants";
    $resultado = metodoPost($query, $queryAutoIncrement);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'PUT') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $document_number = $_POST['document_number'];
    $first_name = $_POST['first_name'];
    $lastname = $_POST['lastname'];
    $birthdate = $_POST['birthdate'];
    $current_state = $_POST['current_state'];
    $schedule = $_POST['schedule'];
    $user_id = $_POST['user_id'];
    $last_update = $_POST['last_update'];
    $id_tariff = $_POST['id_tariff'];
    $room = $_POST['room'];
    $location = $_POST['location'];
    
    // Nota la eliminación de comillas alrededor de los valores NULL
    $query = "UPDATE infants SET document_number='$document_number', first_name='$first_name', lastname='$lastname', 
              birthdate='$birthdate', current_state='$current_state', schedule='$schedule', user_id='$user_id', 
              last_update='$last_update', id_tariff='$id_tariff', room='$room', location='$location' WHERE id='$id'";
              
    $resultado = metodoPut($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'DELETE') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $query = "DELETE FROM infants WHERE id='$id'";
    $resultado = metodoDelete($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

header("HTTP/1.1 400 Bad Request");

?>