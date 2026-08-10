<?php



declare(strict_types=1);



session_start();



if (!isset($_SESSION["usuario_google"])) {

    header("Location: login.php");

    exit;

}



$usuario = $_SESSION["usuario_google"];



require_once __DIR__ . '/config.php';



$mensaje = null;

$error = null;





/*

|--------------------------------------------------------------------------

| REVOCAR TOKEN

|--------------------------------------------------------------------------

*/



if (

    $_SERVER['REQUEST_METHOD'] === 'POST' &&

    ($_POST['accion'] ?? '') === 'revocar'

) {



    $tokenId = filter_input(

        INPUT_POST,

        'token_id',

        FILTER_VALIDATE_INT

    );



    if (!$tokenId) {



        $error = "Token inválido.";



    } else {



        try {



            $stmt = $conexion->prepare("

                UPDATE api_tokens

                SET

                    activo = 0,

                    fecha_revocacion = NOW(),

                    motivo_revocacion = 'Revocado desde administración'

                WHERE id = :id

                  AND activo = 1

            ");



            $stmt->execute([

                ':id' => $tokenId

            ]);



            if ($stmt->rowCount() > 0) {

                $mensaje = "Token revocado correctamente.";

            } else {

                $error = "El token no estaba activo o no existe.";

            }



        } catch (Throwable $e) {



            $error = "No fue posible revocar el token.";

        }

    }

}





/*

|--------------------------------------------------------------------------

| FILTROS

|--------------------------------------------------------------------------

*/



$clienteId = trim($_GET['cliente_id'] ?? '');

$estado = trim($_GET['estado'] ?? '');





/*

|--------------------------------------------------------------------------

| TOKENS

|--------------------------------------------------------------------------

*/



$sql = "

    SELECT

        t.id,

        t.cliente_id,



        c.nombre AS cliente_nombre,

        c.usuario AS cliente_usuario,



        t.activo,

        t.fecha_creacion,

        t.fecha_ultimo_uso,

        t.fecha_expiracion,

        t.fecha_revocacion,

        t.motivo_revocacion,



        CASE



            WHEN t.activo = 0

                 AND t.fecha_revocacion IS NOT NULL

                THEN 'REVOCADO'



            WHEN t.activo = 0

                 AND t.fecha_expiracion <= NOW()

                THEN 'EXPIRADO'



            WHEN t.activo = 1

                 AND t.fecha_expiracion <= NOW()

                THEN 'EXPIRADO'



            WHEN t.activo = 1

                THEN 'ACTIVO'



            ELSE 'INACTIVO'



        END AS estado



    FROM api_tokens t



    INNER JOIN api_clientes c

        ON c.id = t.cliente_id



    WHERE 1 = 1

";



$parametros = [];





if ($clienteId !== '') {



    $sql .= "

        AND t.cliente_id = :cliente_id

    ";



    $parametros[':cliente_id'] = $clienteId;

}





if ($estado !== '') {



    if ($estado === 'ACTIVO') {



        $sql .= "

            AND t.activo = 1

            AND t.fecha_expiracion > NOW()

        ";



    } elseif ($estado === 'REVOCADO') {



        $sql .= "

            AND t.activo = 0

            AND t.fecha_revocacion IS NOT NULL

        ";



    } elseif ($estado === 'EXPIRADO') {



        $sql .= "

            AND t.fecha_expiracion <= NOW()

        ";

    }

}





$sql .= "

    ORDER BY t.id DESC

    LIMIT 200

";





$stmt = $conexion->prepare($sql);



$stmt->execute($parametros);



$tokens = $stmt->fetchAll();





/*

|--------------------------------------------------------------------------

| CLIENTES

|--------------------------------------------------------------------------

*/



$stmtClientes = $conexion->query("

    SELECT

        id,

        nombre,

        usuario,

        activo

    FROM api_clientes

    ORDER BY nombre

");



$clientes = $stmtClientes->fetchAll();





/*

|--------------------------------------------------------------------------

| ESTADÍSTICAS

|--------------------------------------------------------------------------

*/



$estadisticas = $conexion->query("

    SELECT



        COUNT(*) AS total,



        SUM(

            CASE

                WHEN activo = 1

                     AND fecha_expiracion > NOW()

                THEN 1

                ELSE 0

            END

        ) AS activos,



        SUM(

            CASE

                WHEN fecha_expiracion <= NOW()

                THEN 1

                ELSE 0

            END

        ) AS expirados,



        SUM(

            CASE

                WHEN activo = 0

                     AND fecha_revocacion IS NOT NULL

                THEN 1

                ELSE 0

            END

        ) AS revocados



    FROM api_tokens

")->fetch();





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



<title>Administración API CONALEP</title>



<link

    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"

    rel="stylesheet">



</head>



<body class="bg-light">



<div class="container-fluid py-4">



    <div class="d-flex justify-content-between align-items-center mb-4">



        <div>



            <h2 class="mb-0">

                Administración API CONALEP

            </h2>



            <small class="text-muted">

                Seguridad, tokens y trazabilidad

            </small>



        </div>



        <div class="text-end">



            <strong>

                <?= e($usuario["nombre"] ?? '') ?>

            </strong>



            <br>



            <small>

                <?= e($usuario["correo"] ?? '') ?>

            </small>



            <br>



            <a

                href="logout.php"

                class="btn btn-sm btn-outline-secondary mt-2">

                Cerrar sesión

            </a>



        </div>



    </div>





    <?php if ($mensaje): ?>



        <div class="alert alert-success">

            <?= e($mensaje) ?>

        </div>



    <?php endif; ?>





    <?php if ($error): ?>



        <div class="alert alert-danger">

            <?= e($error) ?>

        </div>



    <?php endif; ?>





    <!-- ESTADÍSTICAS -->



    <div class="row g-3 mb-4">



        <div class="col-md-3">



            <div class="card shadow-sm">



                <div class="card-body">



                    <small class="text-muted">

                        Tokens totales

                    </small>



                    <h3>

                        <?= number_format((int)$estadisticas['total']) ?>

                    </h3>



                </div>



            </div>



        </div>





        <div class="col-md-3">



            <div class="card shadow-sm border-success">



                <div class="card-body">



                    <small class="text-muted">

                        Activos

                    </small>



                    <h3 class="text-success">

                        <?= number_format((int)$estadisticas['activos']) ?>

                    </h3>



                </div>



            </div>



        </div>





        <div class="col-md-3">



            <div class="card shadow-sm border-warning">



                <div class="card-body">



                    <small class="text-muted">

                        Expirados

                    </small>



                    <h3 class="text-warning">

                        <?= number_format((int)$estadisticas['expirados']) ?>

                    </h3>



                </div>



            </div>



        </div>





        <div class="col-md-3">



            <div class="card shadow-sm border-danger">



                <div class="card-body">



                    <small class="text-muted">

                        Revocados

                    </small>



                    <h3 class="text-danger">

                        <?= number_format((int)$estadisticas['revocados']) ?>

                    </h3>



                </div>



            </div>



        </div>



    </div>





    <!-- FILTROS -->



    <div class="card shadow-sm mb-4">



        <div class="card-header">



            <strong>

                Consulta de tokens

            </strong>



        </div>



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

                                    —

                                    <?= e($cliente['usuario']) ?>



                                </option>



                            <?php endforeach; ?>



                        </select>



                    </div>





                    <div class="col-md-3">



                        <label class="form-label">

                            Estado

                        </label>



                        <select

                            name="estado"

                            class="form-select">



                            <option value="">

                                Todos

                            </option>



                            <option

                                value="ACTIVO"

                                <?= $estado === 'ACTIVO'

                                    ? 'selected'

                                    : '' ?>>

                                Activos

                            </option>



                            <option

                                value="REVOCADO"

                                <?= $estado === 'REVOCADO'

                                    ? 'selected'

                                    : '' ?>>

                                Revocados

                            </option>



                            <option

                                value="EXPIRADO"

                                <?= $estado === 'EXPIRADO'

                                    ? 'selected'

                                    : '' ?>>

                                Expirados

                            </option>



                        </select>



                    </div>





                    <div class="col-md-5">



                        <button

                            class="btn btn-primary">



                            Consultar



                        </button>



                        <a

                            href="api_admin.php"

                            class="btn btn-outline-secondary">



                            Limpiar



                        </a>



                    </div>



                </div>



            </form>



        </div>



    </div>





    <!-- TOKENS -->



    <div class="card shadow-sm">



        <div class="card-header">



            <strong>

                Historial de tokens

            </strong>



        </div>



        <div class="card-body p-0">



            <div class="table-responsive">



                <table class="table table-hover table-striped mb-0">



                    <thead class="table-dark">



                        <tr>



                            <th>ID</th>

                            <th>Cliente</th>

                            <th>Estado</th>

                            <th>Creación</th>

                            <th>Último uso</th>

                            <th>Expiración</th>

                            <th>Revocación</th>

                            <th>Motivo</th>

                            <th></th>



                        </tr>



                    </thead>



                    <tbody>



                    <?php if (!$tokens): ?>



                        <tr>



                            <td

                                colspan="9"

                                class="text-center text-muted py-4">



                                No hay tokens para mostrar.



                            </td>



                        </tr>



                    <?php endif; ?>





                    <?php foreach ($tokens as $token): ?>



                        <tr>



                            <td>

                                <strong>

                                    #<?= (int)$token['id'] ?>

                                </strong>

                            </td>





                            <td>



                                <?= e($token['cliente_nombre']) ?>



                                <br>



                                <small class="text-muted">



                                    <?= e($token['cliente_usuario']) ?>



                                </small>



                            </td>





                            <td>



                                <?php if ($token['estado'] === 'ACTIVO'): ?>



                                    <span class="badge bg-success">

                                        ACTIVO

                                    </span>



                                <?php elseif ($token['estado'] === 'REVOCADO'): ?>



                                    <span class="badge bg-danger">

                                        REVOCADO

                                    </span>



                                <?php elseif ($token['estado'] === 'EXPIRADO'): ?>



                                    <span class="badge bg-warning text-dark">

                                        EXPIRADO

                                    </span>



                                <?php else: ?>



                                    <span class="badge bg-secondary">

                                        <?= e($token['estado']) ?>

                                    </span>



                                <?php endif; ?>



                            </td>





                            <td>

                                <?= e($token['fecha_creacion']) ?>

                            </td>





                            <td>

                                <?= e($token['fecha_ultimo_uso']) ?: '—' ?>

                            </td>





                            <td>

                                <?= e($token['fecha_expiracion']) ?>

                            </td>





                            <td>

                                <?= e($token['fecha_revocacion']) ?: '—' ?>

                            </td>





                            <td>

                                <?= e($token['motivo_revocacion']) ?: '—' ?>

                            </td>





                            <td>



                                <?php if ($token['estado'] === 'ACTIVO'): ?>



                                    <form

                                        method="POST"

                                        onsubmit="return confirm(

                                            '¿Revocar este token? Esta acción no se puede deshacer.'

                                        );">



                                        <input

                                            type="hidden"

                                            name="accion"

                                            value="revocar">



                                        <input

                                            type="hidden"

                                            name="token_id"

                                            value="<?= (int)$token['id'] ?>">



                                        <button

                                            class="btn btn-sm btn-outline-danger">



                                            Revocar



                                        </button>



                                    </form>



                                <?php endif; ?>



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
