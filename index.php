<?php



ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);



header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Origin: *");



require_once 'config.php';


/*
|--------------------------------------------------------------------------
| API KEY
|--------------------------------------------------------------------------
*/

$apiKeyRecibida = $_SERVER['HTTP_X_API_KEY']
    ?? $_GET['apikey']
    ?? '';

if ($apiKeyRecibida !== $API_KEY) {

    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "API Key inválida"
    ]);

    exit;
}


try {



    /*

    |--------------------------------------------------------------------------

    | PARAMETROS

    |--------------------------------------------------------------------------

    */



    $curp = isset($_GET['curp'])

        ? trim($_GET['curp'])

        : null;



    $plantel = isset($_GET['plantel'])

        ? trim($_GET['plantel'])

        : null;



    $estatus = isset($_GET['estatus'])

        ? trim($_GET['estatus'])

        : null;



    /*

    |--------------------------------------------------------------------------

    | BUSQUEDA POR CURP

    |--------------------------------------------------------------------------

    */



    if (!empty($curp)) {



        $sql = "

            SELECT

                id_conalep_1_2026,

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

            WHERE curp_alumno = :curp

            LIMIT 1

        ";



        $stmt = $conexion->prepare($sql);



        $stmt->bindParam(':curp', $curp);



        $stmt->execute();



        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);



        echo json_encode([

            "success" => !empty($resultado),

            "tipo" => "consulta_curp",

            "data" => $resultado

        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



        exit;

    }



    /*

    |--------------------------------------------------------------------------

    | BUSQUEDA POR PLANTEL Y/O ESTATUS

    |--------------------------------------------------------------------------

    */



    if (!empty($plantel) || !empty($estatus)) {



        $sql = "

            SELECT

                id_conalep_1_2026,

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

            WHERE 1 = 1

        ";



        $params = [];



        /*

        |--------------------------------------------------------------------------

        | FILTRO PLANTEL

        |--------------------------------------------------------------------------

        */



        if (!empty($plantel)) {



            $sql .= " AND plantel_alumno = :plantel ";



            $params[':plantel'] = $plantel;

        }



        /*

        |--------------------------------------------------------------------------

        | FILTRO ESTATUS

        |--------------------------------------------------------------------------

        */



        if (!empty($estatus)) {



            $sql .= " AND estatus_alumno = :estatus ";



            $params[':estatus'] = $estatus;

        }



        //$sql .= " LIMIT 100 ";



        $stmt = $conexion->prepare($sql);



        $stmt->execute($params);



        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);



        echo json_encode([

            "success" => true,

            "tipo" => "consulta_filtros",

            "total" => count($resultado),

            "filtros" => [

                "plantel" => $plantel,

                "estatus" => $estatus

            ],

            "data" => $resultado

        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



        exit;

    }



    /*

    |--------------------------------------------------------------------------

    | SIN PARAMETROS

    |--------------------------------------------------------------------------

    */



    echo json_encode([

        "success" => false,

        "message" => "Debe enviar parámetros válidos",


    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



} catch (PDOException $e) {



    http_response_code(500);



    echo json_encode([

        "success" => false,

        "message" => "Error PDO",

        "error" => $e->getMessage()

    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



} catch (Exception $e) {



    http_response_code(500);



    echo json_encode([

        "success" => false,

        "message" => "Error general",

        "error" => $e->getMessage()

    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

}

?>
