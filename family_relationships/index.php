<?php

include '../bd/bd.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['id'])) {
        $query = "SELECT 
                    fl.*, 
                    u.first_name AS user_first_name, 
                    u.lastname AS user_last_name,
                    u.phone AS user_phone,
                    i.first_name AS infant_first_name, 
                    i.lastname AS infant_lastname
                  FROM family_relationships fl
                  JOIN users u ON fl.user_id = u.id
                  JOIN infants i ON fl.infant_id = i.id
                  WHERE fl.id = " . $_GET['id'];

        $resultado = metodoGet($query);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);

        // Estructurar la respuesta con objetos 'user' e 'infant'
        $response = [
            "id" => $data["id"],
            "user_id" => $data["user_id"],
            "infant_id" => $data["infant_id"],
            "user" => [
                "first_name" => $data["user_first_name"],
                "user_last_name" => $data["user_last_name"],
                "phone" => $data["user_phone"]
            ],
            "infant" => [
                "first_name" => $data["infant_first_name"],
                "lastname" => $data["infant_lastname"]
            ]
        ];

        echo json_encode($response);
        exit();
    } else {
        $query = "SELECT 
                    fl.*, 
                    u.first_name AS user_first_name, 
                    u.lastname AS user_last_name,
                    u.phone AS user_phone,
                    i.first_name AS infant_first_name, 
                    i.lastname AS infant_lastname
                  FROM family_relationships fl
                  JOIN users u ON fl.user_id = u.id
                  JOIN infants i ON fl.infant_id = i.id";

        $resultado = metodoGet($query);
        $data = $resultado->fetchAll(PDO::FETCH_ASSOC);

        // Estructurar cada registro en la respuesta
        $response = array_map(function ($row) {
            return [
                "id" => $row["id"],
                "user_id" => $row["user_id"],
                "infant_id" => $row["infant_id"],
                "user" => [
                    "first_name" => $row["user_first_name"],
                    "user_last_name" => $row["user_last_name"],
                    "phone" => $row["user_phone"]
                ],
                "infant" => [
                    "first_name" => $row["infant_first_name"],
                    "lastname" => $row["infant_lastname"]
                ]
            ];
        }, $data);

        echo json_encode($response);
        exit();
    }
}

if ($_POST['METHOD'] == 'POST') {
    unset($_POST['METHOD']);        //Aqui debajo se agregan los datos segun cada columna $variable=$_POST['variable']
    $infant_id = $_POST['infant_id'];
    $user_id = $_POST['user_id'];
    $query = "INSERT INTO family_relationships(infant_id, user_id) VALUES ('$infant_id', '$user_id')";
    $queryAutoIncrement = "SELECT MAX(id) as id FROM family_relationships";
    $resultado = metodoPost($query, $queryAutoIncrement);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'PUT') {
    unset($_POST['METHOD']);    //Aqui debajo se agregan los datos segun cada columna $variable=$_POST['variable']
    $id = $_GET['id'];
    $infant_id = $_POST['infant_id'];
    $user_id = $_POST['user_id'];
    $query = "UPDATE family_relationships SET infant_id='$infant_id', user_id='$user_id' WHERE id='$id'";
    $resultado = metodoPut($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

if ($_POST['METHOD'] == 'DELETE') {
    unset($_POST['METHOD']);
    $id = $_GET['id'];
    $query = "DELETE FROM family_relationships WHERE id='$id'";
    $resultado = metodoDelete($query);
    echo json_encode($resultado);
    header("HTTP/1.1 200 OK");
    exit();
}

header("HTTP/1.1 400 Bad Request");

?>