<?php



declare(strict_types=1);



require_once __DIR__ . '/../../../config.php';

require_once __DIR__ . '/../../../helpers/response.php';

require_once __DIR__ . '/../../../helpers/auth.php';

require_once __DIR__ . '/../../../helpers/audit.php';



$inicio = microtime(true);



$auth = autenticarToken($conexion);



verificarPermiso(

    $conexion,

    $auth['cliente_id'],

    'ALUMNOS_CURP'

);



$curp = strtoupper(trim($_GET['curp'] ?? ''));



if ($curp === '') {



    jsonResponse([

        "success" => false,

        "message" => "Debe proporcionar una CURP"

    ], 400);

}



$sql = "

    SELECT

        curp_alumno,

        nia_alumno,

        apaterno_alumno,

        amaterno_alumno,

        nombre_alumno,

        plantel_alumno,

        direccion_alumno,

        estatus_alumno,

        menorEdad_alumno,

        nombre_tutor,

        curp_tutor,

        telefono_tutor

    FROM conalep_1_2026

    WHERE UPPER(curp_alumno) = :curp


";



$stmt = $conexion->prepare($sql);



$stmt->execute([

    ':curp' => $curp

]);



$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);



$cantidad = count($resultados);



$duracion = (int)round(

    (microtime(true) - $inicio) * 1000

);



registrarAuditoria(

    $conexion,

    $auth['cliente_id'],

    $auth['token_id'],

    'GET',

    '/api/v1/alumnos/curp',

    'ALUMNOS_CURP',

    $curp,

    $cantidad,

    200,

    $_SERVER['REMOTE_ADDR'] ?? null,

    $_SERVER['HTTP_USER_AGENT'] ?? null,

    $duracion

);



jsonResponse([

    "success" => true,

    "message" => $cantidad > 0

        ? "Consulta realizada correctamente"

        : "No se encontraron registros",

    "total" => $cantidad,

    "data" => $resultados

]);
