<?php



require_once 'config.php';

require_once 'helpers/JwtHelper.php';



$data = json_decode(

    file_get_contents('php://input'),

    true

);



$usuario = $data['usuario'] ?? '';

$password = $data['password'] ?? '';



$sql = "

SELECT *

FROM api_clientes

WHERE usuario = :usuario

AND activo = 1

";



$stmt = $conexion->prepare($sql);



$stmt->execute([

    ':usuario' => $usuario

]);



$cliente = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$cliente ||

    !password_verify(

        $password,

        $cliente['password_hash']

    ))

{

    http_response_code(401);



    echo json_encode([

        'success' => false,

        'message' => 'Credenciales inválidas'

    ]);



    exit;

}



$token = JwtHelper::generarToken(

    $usuario,

    $JWT_SECRET,

    $JWT_EXPIRATION_SECONDS

);



echo json_encode([

    'success' => true,

    'access_token' => $token,

    'expires_in' => $JWT_EXPIRATION_SECONDS

]);
