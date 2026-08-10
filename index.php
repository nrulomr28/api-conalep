<?php



declare(strict_types=1);



session_start();



if (!isset($_SESSION["usuario_google"])) {

    header("Location: login.php");

    exit;

}



$usuario = $_SESSION["usuario_google"];



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

        API CONALEP | Inicio

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

            color: #212529;

        }



        /*

        |--------------------------------------------------------------------------

        | ENCABEZADO

        |--------------------------------------------------------------------------

        */



        .portal-header {

            background: #198754;

            color: #ffffff;

        }



        .portal-title {

            font-size: 1.8rem;

            font-weight: 600;

            letter-spacing: .2px;

        }



        .portal-subtitle {

            opacity: .9;

        }



        .user-name {

            font-weight: 600;

        }



        /*

        |--------------------------------------------------------------------------

        | TARJETAS

        |--------------------------------------------------------------------------

        */



        .module-card {

            height: 100%;

            border: 1px solid #dee2e6;

            border-radius: .65rem;



            transition:

                transform .15s ease,

                box-shadow .15s ease,

                border-color .15s ease;



            background: #ffffff;

        }



        .module-card:hover {

            transform: translateY(-3px);



            box-shadow:

                0 .5rem 1rem rgba(0, 0, 0, .10);



            border-color: #b8cfc3;

        }



        .module-icon {

            width: 52px;

            height: 52px;



            display: flex;

            align-items: center;

            justify-content: center;



            border-radius: 10px;



            font-size: 1.5rem;



            background: #e8f5ee;

            color: #198754;



            margin-bottom: 1rem;

        }



        .module-card h5 {

            font-weight: 600;

            margin-bottom: .6rem;

        }



        .module-card p {

            color: #6c757d;

            min-height: 48px;

        }



        /*

        |--------------------------------------------------------------------------

        | ESTADO

        |--------------------------------------------------------------------------

        */



        .status-dot {

            width: 9px;

            height: 9px;



            display: inline-block;



            border-radius: 50%;



            background: #198754;



            margin-right: 6px;

        }



        /*

        |--------------------------------------------------------------------------

        | MAPA DEL SITIO

        |--------------------------------------------------------------------------

        */



        .site-map {

            background: #ffffff;



            border: 1px solid #dee2e6;

            border-left: 4px solid #198754;



            border-radius: .5rem;

        }



        .site-map-title {

            font-weight: 600;

        }



        .site-map ul {

            margin-bottom: 0;

        }



        .site-map li {

            margin-bottom: .35rem;

        }



        .site-map a {

            color: #198754;

            text-decoration: none;

        }



        .site-map a:hover {

            text-decoration: underline;

        }



        /*

        |--------------------------------------------------------------------------

        | BREADCRUMB

        |--------------------------------------------------------------------------

        */



        .breadcrumb {

            margin-bottom: 0;

        }



        .breadcrumb-item.active {

            color: #6c757d;

        }



        /*

        |--------------------------------------------------------------------------

        | FOOTER

        |--------------------------------------------------------------------------

        */



        .portal-footer {

            color: #6c757d;

            font-size: .85rem;

        }



    </style>



</head>





<body>



<div class="container py-4">





    <!-- ============================================================

         ENCABEZADO

         ============================================================ -->



    <div class="card shadow-sm mb-4">



        <div class="portal-header">



            <div class="p-4">



                <div class="row align-items-center">



                    <!-- TÍTULO -->



                    <div class="col-md-8">



                        <div class="portal-title">

                            API CONALEP

                        </div>



                        <div class="portal-subtitle">

                            Plataforma de consulta y administración

                        </div>



                    </div>





                    <!-- USUARIO -->



                    <div class="col-md-4 text-md-end mt-3 mt-md-0">



                        <div class="user-name">



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



                <ol class="breadcrumb">



                    <li class="breadcrumb-item active">



                        Inicio



                    </li>



                </ol>



            </nav>



        </div>



    </div>







    <!-- ============================================================

         INTRODUCCIÓN

         ============================================================ -->



    <div class="mb-4">



        <h4 class="mb-2">



            Centro de operación



        </h4>



        <p class="text-muted mb-0">



            Seleccione una de las opciones disponibles para

            consultar información, administrar el acceso a la

            API o realizar pruebas.



        </p>



    </div>







    <!-- ============================================================

         MÓDULOS

         ============================================================ -->



    <div class="row g-4">





        <!-- ========================================================

             CONSULTA

             ======================================================== -->



        <div class="col-md-6 col-lg-4">



            <a

                href="consulta.php"

                class="text-decoration-none text-dark">



                <div class="card module-card shadow-sm">



                    <div class="card-body p-4">



                        <div class="module-icon">



                            <i class="bi bi-search"></i>



                        </div>





                        <h5>



                            Consulta CONALEP



                        </h5>





                        <p>



                            Consulta información de alumnos

                            mediante CURP, plantel o estatus.



                        </p>





                        <span class="btn btn-success btn-sm">



                            <i class="bi bi-arrow-right"></i>



                            Ir a consulta



                        </span>



                    </div>



                </div>



            </a>



        </div>







        <!-- ========================================================

             ADMINISTRACIÓN API

             ======================================================== -->



        <div class="col-md-6 col-lg-4">



            <a

                href="api_admin.php"

                class="text-decoration-none text-dark">



                <div class="card module-card shadow-sm">



                    <div class="card-body p-4">



                        <div class="module-icon">



                            <i class="bi bi-shield-lock"></i>



                        </div>





                        <h5>



                            Administración API



                        </h5>





                        <p>



                            Consulte y administre el ciclo de

                            vida de los tokens de acceso.



                        </p>





                        <span class="btn btn-primary btn-sm">



                            <i class="bi bi-gear"></i>



                            Administrar API



                        </span>



                    </div>



                </div>



            </a>



        </div>







        <!-- ========================================================

             BITÁCORA

             ======================================================== -->



        <div class="col-md-6 col-lg-4">



            <a

                href="api_auditoria.php"

                class="text-decoration-none text-dark">



                <div class="card module-card shadow-sm">



                    <div class="card-body p-4">



                        <div class="module-icon">



                            <i class="bi bi-journal-text"></i>



                        </div>





                        <h5>



                            Bitácora de consultas



                        </h5>





                        <p>



                            Consulte las operaciones realizadas

                            mediante la API y su trazabilidad.



                        </p>





                        <span class="btn btn-secondary btn-sm">



                            <i class="bi bi-list-check"></i>



                            Ver bitácora



                        </span>



                    </div>



                </div>



            </a>



        </div>







        <!-- ========================================================

             CONSOLA DE PRUEBAS

             ======================================================== -->



        <div class="col-md-6 col-lg-4">



            <a

                href="api_demo.php"

                class="text-decoration-none text-dark">



                <div class="card module-card shadow-sm">



                    <div class="card-body p-4">



                        <div class="module-icon">



                            <i class="bi bi-terminal"></i>



                        </div>





                        <h5>



                            Consola de pruebas



                        </h5>





                        <p>



                            Realice pruebas controladas de los

                            endpoints disponibles de la API.



                        </p>





                        <span class="btn btn-warning btn-sm">



                            <i class="bi bi-play"></i>



                            Abrir consola



                        </span>



                    </div>



                </div>



            </a>



        </div>







        <!-- ========================================================

             DOCUMENTACIÓN

             ======================================================== -->



        <div class="col-md-6 col-lg-4">



            <div class="card module-card shadow-sm">



                <div class="card-body p-4">



                    <div class="module-icon">



                        <i class="bi bi-book"></i>



                    </div>





                    <h5>



                        Documentación API



                    </h5>





                    <p>



                        Documentación técnica, endpoints,

                        autenticación y ejemplos de consumo.



                    </p>





                    <span class="badge bg-secondary">



                        Próximamente



                    </span>



                </div>



            </div>



        </div>







        <!-- ========================================================

             ESTADO

             ======================================================== -->



        <div class="col-md-6 col-lg-4">



            <div class="card module-card shadow-sm">



                <div class="card-body p-4">



                    <div class="module-icon">



                        <i class="bi bi-activity"></i>



                    </div>





                    <h5>



                        Estado de API



                    </h5>





                    <p>



                        Estado general de los servicios,

                        conectividad y disponibilidad.



                    </p>





                    <span class="badge bg-success">



                        <span class="status-dot"></span>



                        API V1 operativa



                    </span>



                </div>



            </div>



        </div>



    </div>







    <!-- ============================================================

         MAPA DEL SITIO

         ============================================================ -->



    <div class="site-map shadow-sm mt-5">



        <div class="card-body p-4">



            <div class="site-map-title mb-3">



                <i class="bi bi-diagram-3"></i>



                Mapa del sitio



            </div>





            <div class="row">





                <!-- CONSULTA -->



                <div class="col-md-4 mb-4 mb-md-0">



                    <strong>



                        <i class="bi bi-search"></i>



                        Consulta



                    </strong>





                    <ul class="mt-2 ps-4">



                        <li>



                            <a href="consulta.php">



                                Consulta web



                            </a>



                        </li>



                    </ul>



                </div>







                <!-- API -->



                <div class="col-md-4 mb-4 mb-md-0">



                    <strong>



                        <i class="bi bi-braces"></i>



                        API V1



                    </strong>





                    <ul class="mt-2 ps-4">



                        <li>

                            Autenticación

                        </li>



                        <li>

                            Consulta por CURP

                        </li>



                        <li>

                            Consulta por plantel

                        </li>



                        <li>

                            Consulta por estatus

                        </li>



                    </ul>



                </div>







                <!-- ADMIN -->



                <div class="col-md-4">



                    <strong>



                        <i class="bi bi-shield-check"></i>



                        Administración



                    </strong>





                    <ul class="mt-2 ps-4">



                        <li>



                            <a href="api_admin.php">



                                Administración de tokens



                            </a>



                        </li>



                        <li>



                            <a href="api_auditoria.php">



                                Bitácora de consultas



                            </a>



                        </li>



                        <li>



                            <a href="api_demo.php">



                                Consola de pruebas



                            </a>



                        </li>



                    </ul>



                </div>



            </div>



        </div>



    </div>







    <!-- ============================================================

         INFORMACIÓN DE SESIÓN

         ============================================================ -->



    <div class="text-center portal-footer mt-4">



        API CONALEP · V1



        <span class="mx-2">|</span>



        Sesión autenticada



    </div>





</div>





<!-- Bootstrap JS -->



<script

    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">

</script>



</body>



</html>
