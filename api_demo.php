<?php



declare(strict_types=1);



session_start();



if (!isset($_SESSION["usuario_google"])) {

    header("Location: login.php");

    exit;

}



$usuario = $_SESSION["usuario_google"];





/*

|--------------------------------------------------------------------------

| CONFIGURACIÓN

|--------------------------------------------------------------------------

*/



$baseUrl = 'https://puebla-conalep.net';





/*

|--------------------------------------------------------------------------

| VALORES DEL FORMULARIO

|--------------------------------------------------------------------------

*/



$token = trim($_POST['token'] ?? '');

$endpoint = trim($_POST['endpoint'] ?? 'curp');

$criterio = trim($_POST['criterio'] ?? '');



$resultado = null;

$error = null;



$httpCode = null;

$duracion = null;

$urlEjecutada = null;





/*

|--------------------------------------------------------------------------

| ENDPOINTS DISPONIBLES

|--------------------------------------------------------------------------

*/



$endpoints = [



    'curp' => [

        'nombre' => 'Consulta por CURP',

        'ruta' => '/api/v1/alumnos/curp.php',

        'parametro' => 'curp',

        'placeholder' => 'Ejemplo: VABY100327MPLZNMA1',

        'descripcion' => 'Consulta un alumno mediante su CURP.'

    ],



    'plantel' => [

        'nombre' => 'Consulta por Plantel',

        'ruta' => '/api/v1/alumnos/plantel.php',

        'parametro' => 'plantel',

        'placeholder' => 'Ejemplo: Conalep Atencingo',

        'descripcion' => 'Consulta alumnos pertenecientes a un plantel.'

    ],



    'estatus' => [

        'nombre' => 'Consulta por Estatus',

        'ruta' => '/api/v1/alumnos/estatus.php',

        'parametro' => 'estatus',

        'placeholder' => 'Ejemplo: vigente',

        'descripcion' => 'Consulta alumnos de acuerdo con su estatus.'

    ]



];





/*

|--------------------------------------------------------------------------

| EJECUTAR PRUEBA

|--------------------------------------------------------------------------

*/



if ($_SERVER['REQUEST_METHOD'] === 'POST') {



    /*

    |--------------------------------------------------------------------------

    | VALIDACIONES

    |--------------------------------------------------------------------------

    */



    if ($token === '') {



        $error = 'Ingrese un token Bearer para realizar la prueba.';



    } elseif (!isset($endpoints[$endpoint])) {



        $error = 'El endpoint seleccionado no es válido.';



    } elseif ($criterio === '') {



        $error = 'Ingrese el criterio de búsqueda.';



    } else {



        $configEndpoint = $endpoints[$endpoint];



        /*

        |--------------------------------------------------------------------------

        | CONSTRUCCIÓN DE URL

        |--------------------------------------------------------------------------

        */



        $urlEjecutada =

            $baseUrl .

            $configEndpoint['ruta'] .

            '?' .

            http_build_query([

                $configEndpoint['parametro'] => $criterio

            ]);





        /*

        |--------------------------------------------------------------------------

        | INICIO DEL CRONÓMETRO

        |--------------------------------------------------------------------------

        */



        $inicio = microtime(true);





        /*

        |--------------------------------------------------------------------------

        | PETICIÓN HTTP

        |--------------------------------------------------------------------------

        */



        $ch = curl_init($urlEjecutada);



        curl_setopt_array($ch, [



            CURLOPT_RETURNTRANSFER => true,



            CURLOPT_HTTPHEADER => [



                'Authorization: Bearer ' . $token,



                'Accept: application/json'



            ],



            CURLOPT_TIMEOUT => 30,



            CURLOPT_CONNECTTIMEOUT => 10,



            CURLOPT_SSL_VERIFYPEER => true,



            CURLOPT_SSL_VERIFYHOST => 2



        ]);





        $respuesta = curl_exec($ch);



        $curlError = curl_error($ch);



        $httpCode = (int)curl_getinfo(

            $ch,

            CURLINFO_HTTP_CODE

        );



        curl_close($ch);





        /*

        |--------------------------------------------------------------------------

        | TIEMPO

        |--------------------------------------------------------------------------

        */



        $duracion = round(

            (microtime(true) - $inicio) * 1000

        );





        /*

        |--------------------------------------------------------------------------

        | ERROR DE CONEXIÓN

        |--------------------------------------------------------------------------

        */



        if ($respuesta === false || $curlError !== '') {



            $error =

                'No fue posible comunicarse con el endpoint: ' .

                $curlError;



        } else {



            /*

            |--------------------------------------------------------------------------

            | JSON

            |--------------------------------------------------------------------------

            */



            $json = json_decode(

                $respuesta,

                true

            );





            if (json_last_error() === JSON_ERROR_NONE) {



                $resultado = json_encode(

                    $json,

                    JSON_PRETTY_PRINT |

                    JSON_UNESCAPED_UNICODE |

                    JSON_UNESCAPED_SLASHES

                );



            } else {



                $resultado = $respuesta;



            }



        }



    }



}





/*

|--------------------------------------------------------------------------

| FUNCIÓN HTML

|--------------------------------------------------------------------------

*/



function e(?string $valor): string

{

    return htmlspecialchars(

        $valor ?? '',

        ENT_QUOTES,

        'UTF-8'

    );

}



?>

<!DOCTYPE html>



<html lang="es">



<head>



    <meta charset="UTF-8">



    <meta

        name="viewport"

        content="width=device-width, initial-scale=1">



    <title>

        Consola de pruebas | API CONALEP

    </title>





    <!-- Bootstrap -->



    <link

        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

        rel="stylesheet">





    <!-- Bootstrap Icons -->



    <link

        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"

        rel="stylesheet">





    <style>



        body {

            background: #f4f6f8;

        }





        /*

        |--------------------------------------------------------------------------

        | HEADER

        |--------------------------------------------------------------------------

        */



        .portal-header {

            background: #198754;

            color: white;

        }





        /*

        |--------------------------------------------------------------------------

        | CARDS

        |--------------------------------------------------------------------------

        */



        .panel {

            border: 1px solid #dee2e6;

            border-radius: .65rem;

            background: #ffffff;

        }





        /*

        |--------------------------------------------------------------------------

        | JSON

        |--------------------------------------------------------------------------

        */



        .json-container {



            background: #212529;



            color: #f8f9fa;



            border-radius: .5rem;



            padding: 1.25rem;



            overflow-x: auto;



            min-height: 250px;



            max-height: 650px;



            font-family:

                Consolas,

                Monaco,

                monospace;



            font-size: .88rem;



            white-space: pre;



        }





        /*

        |--------------------------------------------------------------------------

        | ENDPOINT

        |--------------------------------------------------------------------------

        */



        .endpoint-url {



            font-family:

                Consolas,

                Monaco,

                monospace;



            font-size: .9rem;



            background: #f8f9fa;



            border: 1px solid #dee2e6;



            border-radius: .4rem;



            padding: .65rem .75rem;



        }





        /*

        |--------------------------------------------------------------------------

        | RESULTADO

        |--------------------------------------------------------------------------

        */



        .result-header {



            border-bottom:

                1px solid #dee2e6;



            padding-bottom: .75rem;



            margin-bottom: 1rem;



        }





        .http-success {

            color: #198754;

            font-weight: 600;

        }





        .http-error {

            color: #dc3545;

            font-weight: 600;

        }





        /*

        |--------------------------------------------------------------------------

        | AYUDA

        |--------------------------------------------------------------------------

        */



        .help-box {



            background: #f8f9fa;



            border-left:

                4px solid #198754;



            padding: 1rem;



            border-radius: .4rem;



        }



    </style>



</head>





<body>



<div class="container py-4">





    <!-- ============================================================

         HEADER

         ============================================================ -->



    <div class="card shadow-sm mb-4">



        <div class="portal-header">



            <div class="p-4">



                <div class="row align-items-center">





                    <div class="col-md-8">



                        <div class="fs-3 fw-semibold">



                            API CONALEP



                        </div>



                        <div>



                            Consola de pruebas



                        </div>



                    </div>





                    <div class="col-md-4 text-md-end mt-3 mt-md-0">



                        <div class="fw-semibold">



                            <?= e($usuario["nombre"] ?? '') ?>



                        </div>



                        <div class="small">



                            <?= e($usuario["correo"] ?? '') ?>



                        </div>



                        <a

                            href="logout.php"

                            class="btn btn-sm btn-light mt-2">



                            <i class="bi bi-box-arrow-right"></i>



                            Cerrar sesión



                        </a>



                    </div>



                </div>



            </div>



        </div>





        <!-- BREADCRUMB -->



        <div class="card-body py-3">



            <nav aria-label="breadcrumb">



                <ol class="breadcrumb mb-0">



                    <li class="breadcrumb-item">



                        <a href="index.php">



                            Inicio



                        </a>



                    </li>



                    <li class="breadcrumb-item active">



                        Consola de pruebas



                    </li>



                </ol>



            </nav>



        </div>



    </div>







    <!-- ============================================================

         INTRODUCCIÓN

         ============================================================ -->



    <div class="mb-4">



        <h4>



            <i class="bi bi-terminal"></i>



            Consola de pruebas API



        </h4>



        <p class="text-muted">



            Utilice esta herramienta para realizar pruebas

            controladas sobre los endpoints disponibles de

            la API CONALEP.



        </p>



    </div>







    <!-- ============================================================

         MENSAJE DE SEGURIDAD

         ============================================================ -->



    <div class="alert alert-info">



        <i class="bi bi-info-circle"></i>



        <strong>Importante:</strong>



        el token utilizado para la prueba se envía únicamente

        en la solicitud y no se almacena en esta aplicación.



    </div>







    <!-- ============================================================

         FORMULARIO

         ============================================================ -->



    <div class="panel shadow-sm mb-4">



        <div class="p-4">



            <h5 class="mb-4">



                Ejecutar solicitud



            </h5>





            <form method="POST">





                <!-- TOKEN -->



                <div class="mb-3">



                    <label class="form-label fw-semibold">



                        Token de acceso



                    </label>



                    <input

                        type="password"

                        name="token"

                        class="form-control font-monospace"

                        value="<?= e($token) ?>"

                        placeholder="Pegue aquí el token Bearer"

                        autocomplete="off">



                    <div class="form-text">



                        El token no se guarda en la base de datos

                        ni en la sesión.



                    </div>



                </div>







                <!-- ENDPOINT -->



                <div class="mb-3">



                    <label class="form-label fw-semibold">



                        Endpoint



                    </label>





                    <select

                        name="endpoint"

                        id="endpoint"

                        class="form-select">



                        <?php foreach ($endpoints as $key => $item): ?>



                            <option

                                value="<?= e($key) ?>"

                                <?= $endpoint === $key

                                    ? 'selected'

                                    : '' ?>>



                                <?= e($item['nombre']) ?>



                            </option>



                        <?php endforeach; ?>



                    </select>



                </div>







                <!-- URL -->



                <div class="mb-3">



                    <label class="form-label fw-semibold">



                        URL



                    </label>



                    <div

                        id="url-preview"

                        class="endpoint-url">



                    </div>



                </div>







                <!-- CRITERIO -->



                <div class="mb-4">



                    <label

                        for="criterio"

                        class="form-label fw-semibold">



                        Criterio de búsqueda



                    </label>



                    <input

                        type="text"

                        name="criterio"

                        id="criterio"

                        class="form-control"

                        value="<?= e($criterio) ?>"

                        placeholder="<?= e(

                            $endpoints[$endpoint]['placeholder']

                            ?? ''

                        ) ?>">



                    <div

                        id="endpoint-description"

                        class="form-text">



                        <?= e(

                            $endpoints[$endpoint]['descripcion']

                            ?? ''

                        ) ?>



                    </div>



                </div>







                <!-- BOTONES -->



                <div class="d-flex gap-2">



                    <button

                        type="submit"

                        class="btn btn-success">



                        <i class="bi bi-play-fill"></i>



                        Ejecutar consulta



                    </button>





                    <a

                        href="api_demo.php"

                        class="btn btn-outline-secondary">



                        <i class="bi bi-arrow-counterclockwise"></i>



                        Limpiar



                    </a>



                </div>





            </form>



        </div>



    </div>







    <!-- ============================================================

         ERROR

         ============================================================ -->



    <?php if ($error !== null): ?>



        <div class="alert alert-danger shadow-sm">



            <i class="bi bi-exclamation-triangle"></i>



            <strong>Error:</strong>



            <?= e($error) ?>



        </div>



    <?php endif; ?>







    <!-- ============================================================

         RESULTADO

         ============================================================ -->



    <?php if ($resultado !== null): ?>



        <div class="panel shadow-sm mb-4">



            <div class="p-4">





                <div class="result-header">



                    <div class="d-flex justify-content-between">



                        <h5 class="mb-0">



                            Resultado de la solicitud



                        </h5>





                        <div>



                            <?php if (

                                $httpCode >= 200 &&

                                $httpCode < 300

                            ): ?>



                                <span

                                    class="http-success">



                                    <i class="bi bi-check-circle-fill"></i>



                                    HTTP <?= $httpCode ?>



                                </span>



                            <?php else: ?>



                                <span

                                    class="http-error">



                                    <i class="bi bi-x-circle-fill"></i>



                                    HTTP <?= $httpCode ?>



                                </span>



                            <?php endif; ?>



                        </div>



                    </div>



                </div>







                <!-- INFORMACIÓN -->



                <div class="row mb-4">





                    <div class="col-md-8">



                        <small class="text-muted">



                            Endpoint ejecutado



                        </small>



                        <div class="endpoint-url mt-1">



                            <?= e($urlEjecutada) ?>



                        </div>



                    </div>





                    <div class="col-md-2">



                        <small class="text-muted">



                            HTTP



                        </small>



                        <div class="fs-5 fw-semibold">



                            <?= (int)$httpCode ?>



                        </div>



                    </div>





                    <div class="col-md-2">



                        <small class="text-muted">



                            Tiempo



                        </small>



                        <div class="fs-5 fw-semibold">



                            <?= (int)$duracion ?> ms



                        </div>



                    </div>



                </div>







                <!-- JSON -->



                <div class="mb-2">



                    <strong>



                        Respuesta JSON



                    </strong>



                </div>





                <div class="json-container">



                    <?= e($resultado) ?>



                </div>





            </div>



        </div>



    <?php endif; ?>







    <!-- ============================================================

         AYUDA

         ============================================================ -->



    <div class="help-box shadow-sm mb-4">



        <h6>



            <i class="bi bi-lightbulb"></i>



            Flujo de prueba



        </h6>



        <ol class="mb-0">



            <li>



                Obtenga un token mediante el endpoint de

                autenticación.



            </li>



            <li>



                Pegue el token en el campo correspondiente.



            </li>



            <li>



                Seleccione el endpoint que desea probar.



            </li>



            <li>



                Capture el criterio de búsqueda.



            </li>



            <li>



                Ejecute la solicitud.



            </li>



            <li>



                Revise el código HTTP y la respuesta JSON.



            </li>



        </ol>



    </div>







    <!-- ============================================================

         NAVEGACIÓN

         ============================================================ -->



    <div class="d-flex gap-2 mb-4">



        <a

            href="index.php"

            class="btn btn-outline-secondary">



            <i class="bi bi-house"></i>



            Inicio



        </a>





        <a

            href="consulta.php"

            class="btn btn-outline-success">



            <i class="bi bi-search"></i>



            Consulta web



        </a>





        <a

            href="api_admin.php"

            class="btn btn-outline-primary">



            <i class="bi bi-shield-lock"></i>



            Tokens



        </a>





        <a

            href="api_auditoria.php"

            class="btn btn-outline-secondary">



            <i class="bi bi-journal-text"></i>



            Bitácora



        </a>



    </div>







    <!-- ============================================================

         FOOTER

         ============================================================ -->



    <div class="text-center text-muted small">



        API CONALEP · V1 · Consola de pruebas



    </div>





</div>







<script>



const endpoints = <?= json_encode(

    $endpoints,

    JSON_UNESCAPED_UNICODE |

    JSON_UNESCAPED_SLASHES

) ?>;



const baseUrl = <?= json_encode(

    $baseUrl,

    JSON_UNESCAPED_SLASHES

) ?>;





const endpointSelect =

    document.getElementById('endpoint');



const criterioInput =

    document.getElementById('criterio');



const urlPreview =

    document.getElementById('url-preview');



const description =

    document.getElementById('endpoint-description');





function actualizarEndpoint()

{

    const key = endpointSelect.value;



    const config = endpoints[key];



    if (!config) {

        return;

    }





    criterioInput.placeholder =

        config.placeholder;





    description.textContent =

        config.descripcion;





    const criterio =

        criterioInput.value.trim();





    let url =

        baseUrl +

        config.ruta;





    if (criterio !== '') {



        url +=

            '?' +

            encodeURIComponent(config.parametro) +

            '=' +

            encodeURIComponent(criterio);



    }





    urlPreview.textContent = url;

}





endpointSelect.addEventListener(

    'change',

    actualizarEndpoint

);





criterioInput.addEventListener(

    'input',

    actualizarEndpoint

);





actualizarEndpoint();



</script>





</body>



</html>
