<?php



require_once __DIR__ . '/config/google.php';



if (!isset($_GET['code'])) {



    die('No se recibió el código OAuth.');



}



$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);



if (isset($token['error'])) {



    die($token['error']);



}



$client->setAccessToken($token);



$oauth = new Google_Service_Oauth2($client);



$user = $oauth->userinfo->get();



$_SESSION['usuario_google'] = [



    'id' => $user->id,



    'nombre' => $user->name,



    'correo' => $user->email,



    'foto' => $user->picture



];



header('Location: index.php');



exit;
