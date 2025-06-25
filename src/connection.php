<?php
function getMySQLConnection() {
    $servername = "mysql";
    $username = "username";
    $password = "password";
    $dbname = "bostarter_db";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connessione fallita a MySQL: " . $e->getMessage());
    }
}

function getMongoDBConnection() {
    try {
        $manager = new MongoDB\Driver\Manager("mongodb://admin_username:admin_password@mongodb:27017");
        return $manager;
    } catch (MongoDB\Driver\Exception\Exception $e) {
        die("Connessione fallita a MongoDB: " . $e->getMessage());
    }
}
?>