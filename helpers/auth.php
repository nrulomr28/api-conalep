<?php



declare(strict_types=1);



require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/response.php';



function obtenerTokenBearer(): ?string

{

    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';



    if ($header === '' &&

        function_exists('getallheaders')) {



        $headers = getallheaders();



        $header = $headers['Authorization']

            ?? $headers['authorization']

            ?? '';

    }



    if (!preg_match(

        '/^Bearer\s+(.+)$/i',

        trim($header),

        $matches

    )) {

        return null;

    }



    return trim($matches[1]);

}





function autenticarToken(PDO $conexion): array

{

    $token = obtenerTokenBearer();



    if (!$token) {



        jsonResponse([

            "success" => false,

            "message" => "Token requerido"

        ], 401);

    }



    $tokenHash = hash('sha256', $token);



    $sql = "

        SELECT

            t.id AS token_id,

            t.cliente_id,

            t.fecha_expiracion,

            t.activo,



            c.nombre,

            c.usuario,

            c.activo AS cliente_activo



        FROM api_tokens t



        INNER JOIN api_clientes c

            ON c.id = t.cliente_id



        WHERE t.token_hash = :token_hash



        LIMIT 1

    ";



    $stmt = $conexion->prepare($sql);



    $stmt->execute([

        ':token_hash' => $tokenHash

    ]);



    $tokenData = $stmt->fetch();



    if (!$tokenData) {



        jsonResponse([

            "success" => false,

            "message" => "Token inválido"

        ], 401);

    }



    if ((int)$tokenData['activo'] !== 1) {



        jsonResponse([

            "success" => false,

            "message" => "Token revocado"

        ], 401);

    }



    if ((int)$tokenData['cliente_activo'] !== 1) {



        jsonResponse([

            "success" => false,

            "message" => "Cliente API inactivo"

        ], 401);

    }



    if (

        strtotime($tokenData['fecha_expiracion'])

        <= time()

    ) {



        $update = $conexion->prepare("

            UPDATE api_tokens

            SET

                activo = 0,

                fecha_revocacion = NOW(),

                motivo_revocacion = 'Token expirado'

            WHERE id = :id

        ");



        $update->execute([

            ':id' => $tokenData['token_id']

        ]);



        jsonResponse([

            "success" => false,

            "message" => "Token expirado"

        ], 401);

    }



    /*

     * Actualizamos último uso.

     */

    $updateUso = $conexion->prepare("

        UPDATE api_tokens

        SET fecha_ultimo_uso = NOW()

        WHERE id = :id

    ");



    $updateUso->execute([

        ':id' => $tokenData['token_id']

    ]);



    return [

        'token_id' => (int)$tokenData['token_id'],

        'cliente_id' => (int)$tokenData['cliente_id'],

        'nombre' => $tokenData['nombre'],

        'usuario' => $tokenData['usuario']

    ];

}





function verificarPermiso(

    PDO $conexion,

    int $clienteId,

    string $codigoPermiso

): void {



    $sql = "

        SELECT 1



        FROM api_cliente_permisos cp



        INNER JOIN api_permisos p

            ON p.id = cp.permiso_id



        WHERE cp.cliente_id = :cliente_id



          AND p.codigo = :codigo



          AND cp.activo = 1

          AND p.activo = 1



        LIMIT 1

    ";



    $stmt = $conexion->prepare($sql);



    $stmt->execute([

        ':cliente_id' => $clienteId,

        ':codigo' => $codigoPermiso

    ]);



    if (!$stmt->fetchColumn()) {



        jsonResponse([

            "success" => false,

            "message" => "El cliente no tiene permiso para esta operación"

        ], 403);

    }

}
