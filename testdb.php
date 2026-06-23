<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "ls-b171550554b78158f6181f1a091b7551caf7ee49.crqkmwkg2myj.us-east-2.rds.amazonaws.com";
$dbname = "conalep";
$user = "dbmasteruser";
$password = "pwd2026*S3guR";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $user,
        $password
    );

    echo "CONEXION OK";

} catch (Exception $e) {

    echo $e->getMessage();
}
?>
