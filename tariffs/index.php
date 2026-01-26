<?php

include '../bd/bd.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $query = "SELECT * FROM tariffs where id=" . $_GET['id'];
        $resultado = metodoGet($query);
        echo json_encode($resultado->fetch(PDO::FETCH_ASSOC));
        exit(); // Agrega esta línea para salir después de enviar la respuesta JSON
    } else {
        $query = "SELECT * FROM tariffs";
        $resultado = metodoGet($query);
        echo json_encode($resultado->fetchAll());
        exit(); // Agrega esta línea para salir después de enviar la respuesta JSON
    }
}

if ($_POST['METHOD'] == 'POST') {
    unset($_POST['METHOD']);
    $number_of_hours = $_POST['number_of_hours'];
    $price = $_POST['price'];
    $last_update = $_POST['last_update'];
    $query = "INSERT INTO tariffs(number_of_hours, price, last_update) 
              VALUES ('$number_of_hours', '$price', '$last_update')";
    
    $queryAutoIncrement = "SELECT MAX(id) as id FROM tariffs";
    $resultado = metodoPost($query, $queryAutoIncrement);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'PUT') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $number_of_hours = $_POST['number_of_hours'];
    $price = $_POST['price'];
    $last_update = $_POST['last_update'];
    $query = "UPDATE tariffs SET number_of_hours='$number_of_hours', price='$price', last_update='$last_update' WHERE id='$id'";
    $resultado = metodoPut($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'DELETE') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $query = "DELETE FROM tariffs WHERE id='$id'";
    $resultado = metodoDelete($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

header("HTTP/1.1 400 Bad Request");

?>