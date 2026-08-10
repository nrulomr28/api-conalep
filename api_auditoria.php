<?php



declare(strict_types=1);



session_start();



if (!isset($_SESSION["usuario_google"])) {

    header("Location: login.php");

    exit;

}



require_once __DIR__ . '/config.php';



$clienteId = trim($_GET['cliente_id'] ?? '');

$endpoint = trim($_GET['endpoint'] ?? '');



$sql = "

    SELECT

        a.id,

        a.fecha,

        a.cliente_id,

        c.nombre AS cliente_nombre,

        c.usuario AS cliente_usuario,

        a.token_id,

        a.metodo,

        a.endpoint,

        a.operacion,

        a.criterio,

        a.cantidad_resultados,

        a.codigo_http,

        a.ip,

        a.duracion_ms,

        a.detalle



    FROM api_auditoria a



    INNER JOIN api_clientes c

        ON c.id = a.cliente_id



    WHERE 1 = 1

";



$parametros = [];



if ($clienteId !== '') {



    $sql .= "

        AND a.cliente_id = :cliente_id

    ";



    $parametros[':cliente_id'] = $clienteId;

}



if ($endpoint !== '') {



    $sql .= "

        AND a.endpoint LIKE :endpoint

    ";



    $parametros[':endpoint'] = '%' . $endpoint . '%';

}



$sql .= "

    ORDER BY a.id DESC

    LIMIT 500

";



$stmt = $conexion->prepare($sql);

$stmt->execute($parametros);



$auditoria = $stmt->fetchAll();





$clientes = $conexion->query("

    SELECT

        id,

        nombre,

        usuario

    FROM api_clientes

    ORDER BY nombre

")->fetchAll();





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



<meta name="viewport"

      content="width=device-width, initial-scale=1">



<title>Bitácora API CONALEP</title>



<link

href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

rel="stylesheet">



</head>



<body class="bg-light">



<div class="container-fluid py-4">



    <div class="d-flex justify-content-between mb-4">



        <div>



            <h2>

                Bitácora API CONALEP

            </h2>



            <small class="text-muted">

                Trazabilidad de consultas

            </small>



        </div>



        <div>



            <a

                href="api_admin.php"

                class="btn btn-outline-primary">



                Tokens



            </a>



        </div>



    </div>





    <div class="card shadow-sm mb-4">



        <div class="card-body">



            <form method="GET">



                <div class="row align-items-end">



                    <div class="col-md-4">



                        <label class="form-label">

                            Cliente

                        </label>



                        <select

                            name="cliente_id"

                            class="form-select">



                            <option value="">

                                Todos

                            </option>



                            <?php foreach ($clientes as $cliente): ?>



                                <option

                                    value="<?= (int)$cliente['id'] ?>"

                                    <?= $clienteId == $cliente['id']

                                        ? 'selected'

                                        : '' ?>>



                                    <?= e($cliente['nombre']) ?>



                                </option>



                            <?php endforeach; ?>



                        </select>



                    </div>





                    <div class="col-md-4">



                        <label class="form-label">

                            Endpoint

                        </label>



                        <input

                            type="text"

                            name="endpoint"

                            class="form-control"

                            value="<?= e($endpoint) ?>"

                            placeholder="/api/v1/alumnos">



                    </div>





                    <div class="col-md-4">



                        <button class="btn btn-primary">

                            Consultar

                        </button>



                        <a

                            href="api_auditoria.php"

                            class="btn btn-outline-secondary">



                            Limpiar



                        </a>



                    </div>



                </div>



            </form>



        </div>



    </div>





    <div class="card shadow-sm">



        <div class="card-header">



            <strong>

                Últimas 500 operaciones

            </strong>



        </div>



        <div class="card-body p-0">



            <div class="table-responsive">



                <table class="table table-sm table-striped table-hover mb-0">



                    <thead class="table-dark">



                        <tr>



                            <th>ID</th>

                            <th>Fecha</th>

                            <th>Cliente</th>

                            <th>Token</th>

                            <th>Método</th>

                            <th>Endpoint</th>

                            <th>Operación</th>

                            <th>Criterio</th>

                            <th>Resultados</th>

                            <th>HTTP</th>

                            <th>IP</th>

                            <th>ms</th>



                        </tr>



                    </thead>



                    <tbody>



                    <?php foreach ($auditoria as $a): ?>



                        <tr>



                            <td>

                                <?= (int)$a['id'] ?>

                            </td>



                            <td>

                                <?= e($a['fecha']) ?>

                            </td>



                            <td>



                                <?= e($a['cliente_nombre']) ?>



                                <br>



                                <small class="text-muted">

                                    <?= e($a['cliente_usuario']) ?>

                                </small>



                            </td>



                            <td>

                                #<?= (int)$a['token_id'] ?>

                            </td>



                            <td>

                                <?= e($a['metodo']) ?>

                            </td>



                            <td>

                                <code>

                                    <?= e($a['endpoint']) ?>

                                </code>

                            </td>



                            <td>

                                <?= e($a['operacion']) ?>

                            </td>



                            <td>

                                <code>

                                    <?= e($a['criterio']) ?>

                                </code>

                            </td>



                            <td class="text-center">

                                <?= (int)$a['cantidad_resultados'] ?>

                            </td>



                            <td>



                                <?php if ((int)$a['codigo_http'] >= 200 &&

                                          (int)$a['codigo_http'] < 300): ?>



                                    <span class="badge bg-success">

                                        <?= (int)$a['codigo_http'] ?>

                                    </span>



                                <?php elseif ((int)$a['codigo_http'] >= 400): ?>



                                    <span class="badge bg-danger">

                                        <?= (int)$a['codigo_http'] ?>

                                    </span>



                                <?php else: ?>



                                    <?= (int)$a['codigo_http'] ?>



                                <?php endif; ?>



                            </td>



                            <td>

                                <?= e($a['ip']) ?>

                            </td>



                            <td>

                                <?= (int)$a['duracion_ms'] ?>

                            </td>



                        </tr>



                    <?php endforeach; ?>



                    </tbody>



                </table>



            </div>



        </div>



    </div>



</div>



</body>



</html>
