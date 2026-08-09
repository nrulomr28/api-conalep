<?php



declare(strict_types=1);



require_once __DIR__ . '/../../../config.php';

require_once __DIR__ . '/../../../helpers/response.php';



header('Content-Type: application/json; charset=utf-8');



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {



    jsonResponse([

        "success" => false,

        "message" => "Método no permitido"

    ], 405);

}



$inicio = microtime(true);



$input = json_decode(

    file_get_contents('php://input'),

    true

);



if (!is_array($input)) {



    jsonResponse([

        "success" => false,

        "message" => "JSON inválido"

    ], 400);

}



$usuario = trim($input['usuario'] ?? '');

$password = $input['password'] ?? '';



if ($usuario === '' || $password === '') {



    jsonResponse([

        "success" => false,

        "message" => "Usuario y contraseña son requeridos"

    ], 400);

}



$stmt = $conexion->prepare("

    SELECT

        id,

        nombre,

        usuario,

        password_hash,

        activo

    FROM api_clientes

    WHERE usuario = :usuario

    LIMIT 1

");



$stmt->execute([

    ':usuario' => $usuario

]);



$cliente = $stmt->fetch();



$ip = $_SERVER['REMOTE_ADDR'] ?? null;

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;



if (!$cliente) {



    $log = $conexion->prepare("

        INSERT INTO api_login_log (

            cliente_id,

            usuario,

            resultado,

            ip,

            user_agent,

            detalle

        )

        VALUES (

            NULL,

            :usuario,

            'USUARIO_INVALIDO',

            :ip,

            :user_agent,

            'Usuario no encontrado'

        )

    ");



    $log->execute([

        ':usuario' => $usuario,

        ':ip' => $ip,

        ':user_agent' => $userAgent

    ]);



    jsonResponse([

        "success" => false,

        "message" => "Credenciales inválidas"

    ], 401);

}



if ((int)$cliente['activo'] !== 1) {



    $log = $conexion->prepare("

        INSERT INTO api_login_log (

            cliente_id,

            usuario,

            resultado,

            ip,

            user_agent,

            detalle

        )

        VALUES (

            :cliente_id,

            :usuario,

            'CLIENTE_INACTIVO',

            :ip,

            :user_agent,

            'Cliente API inactivo'

        )

    ");



    $log->execute([

        ':cliente_id' => $cliente['id'],

        ':usuario' => $usuario,

        ':ip' => $ip,

        ':user_agent' => $userAgent

    ]);



    jsonResponse([

        "success" => false,

        "message" => "Cliente API inactivo"

    ], 401);

}



if (!password_verify($password, $cliente['password_hash'])) {



    $log = $conexion->prepare("

        INSERT INTO api_login_log (

            cliente_id,

            usuario,

            resultado,

            ip,

            user_agent,

            detalle

        )

        VALUES (

            :cliente_id,

            :usuario,

            'USUARIO_INVALIDO',

            :ip,

            :user_agent,

            'Contraseña incorrecta'

        )

    ");



    $log->execute([

        ':cliente_id' => $cliente['id'],

        ':usuario' => $usuario,

        ':ip' => $ip,

        ':user_agent' => $userAgent

    ]);



    jsonResponse([

        "success" => false,

        "message" => "Credenciales inválidas"

    ], 401);

}





/*

 * A partir de aquí tenemos autenticación válida.

 *

 * Una sola sesión activa por cliente.

 */

try {



    $conexion->beginTransaction();



    /*

     * Revocamos tokens anteriores.

     */

    $revocar = $conexion->prepare("

        UPDATE api_tokens



        SET

            activo = 0,

            fecha_revocacion = NOW(),

            motivo_revocacion = 'Nuevo inicio de sesión'



        WHERE cliente_id = :cliente_id

          AND activo = 1

    ");



    $revocar->execute([

        ':cliente_id' => $cliente['id']

    ]);





    /*

     * Token criptográficamente seguro.

     */

    $token = bin2hex(random_bytes(32));



    /*

     * Nunca almacenamos el token original.

     */

    $tokenHash = hash('sha256', $token);





    /*

     * Vigencia inicial: 1 hora.

     *

     * Después la hacemos configurable.

     */

    $fechaExpiracion = date(

        'Y-m-d H:i:s',

        time() + 3600

    );





    $insert = $conexion->prepare("

        INSERT INTO api_tokens (

            cliente_id,

            token_hash,

            fecha_expiracion,

            activo

        )

        VALUES (

            :cliente_id,

            :token_hash,

            :fecha_expiracion,

            1

        )

    ");



    $insert->execute([

        ':cliente_id' => $cliente['id'],

        ':token_hash' => $tokenHash,

        ':fecha_expiracion' => $fechaExpiracion

    ]);



    $tokenId = (int)$conexion->lastInsertId();





    /*

     * Actualizamos último login.

     */

    $updateCliente = $conexion->prepare("

        UPDATE api_clientes

        SET fecha_ultimo_login = NOW()

        WHERE id = :id

    ");



    $updateCliente->execute([

        ':id' => $cliente['id']

    ]);





    /*

     * Login exitoso.

     */

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

            'EXITOSO',

            :ip,

            :user_agent,

            'Inicio de sesión exitoso'

        )

    ");



    $log->execute([

        ':cliente_id' => $cliente['id'],

        ':token_id' => $tokenId,

        ':usuario' => $usuario,

        ':ip' => $ip,

        ':user_agent' => $userAgent

    ]);



    $conexion->commit();



} catch (Throwable $e) {



    if ($conexion->inTransaction()) {

        $conexion->rollBack();

    }



    jsonResponse([

        "success" => false,

        "message" => "No fue posible iniciar sesión"

    ], 500);

}





jsonResponse([

    "success" => true,

    "message" => "Autenticación exitosa",

    "data" => [

        "token" => $token,

        "tipo" => "Bearer",

        "expira_en" => $fechaExpiracion,

        "cliente" => [

            "id" => (int)$cliente['id'],

            "nombre" => $cliente['nombre'],

            "usuario" => $cliente['usuario']

        ]

    ]

]);
