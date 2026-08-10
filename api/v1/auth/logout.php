<?php



declare(strict_types=1);



require_once __DIR__ . '/../../../config.php';

require_once __DIR__ . '/../../../helpers/response.php';

require_once __DIR__ . '/../../../helpers/auth.php';



$inicio = microtime(true);



$auth = autenticarToken($conexion);



$tokenId = $auth['token_id'];

$clienteId = $auth['cliente_id'];



$update = $conexion->prepare("

    UPDATE api_tokens

    SET

        activo = 0,

        fecha_revocacion = NOW(),

        motivo_revocacion = 'Logout'

    WHERE id = :id

      AND activo = 1

");



$update->execute([

    ':id' => $tokenId

]);





$ip = $_SERVER['REMOTE_ADDR'] ?? null;

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;



$log = $conexion->prepare("

    INSERT INTO api_login_log (

        cliente_id,

        token_id,

        usuario,

        resultado,

        ip,

        user_agent,

        detalle

    )

    VALUES (

        :cliente_id,

        :token_id,

        :usuario,

        'LOGOUT',

        :ip,

        :user_agent,

        'Cierre de sesión'

    )

");



$log->execute([

    ':cliente_id' => $clienteId,

    ':token_id' => $tokenId,

    ':usuario' => $auth['usuario'],

    ':ip' => $ip,

    ':user_agent' => $userAgent

]);





jsonResponse([

    "success" => true,

    "message" => "Sesión cerrada correctamente"

]);
