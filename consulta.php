<?php

session_start();

if (!isset($_SESSION["usuario_google"])) {

    header("Location: login.php");

    exit;

}

$usuario = $_SESSION["usuario_google"];

require_once "config.php";

$resultados = [];

$totalResultados = 0;

$buscar = false;
$mensaje = "";

$curp = trim($_GET["curp"] ?? "");

$plantel = trim($_GET["plantel"] ?? "");

$estatus = trim($_GET["estatus"] ?? "");

if ($curp != "" || $plantel != "" || $estatus != "") {

    if ($curp != "" && strlen($curp) < 10) {

        $mensaje = "Para buscar por CURP debe ingresar al menos 10 caracteres.";

    } else {

        $buscar = true;

        $sql = "SELECT
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
                WHERE 1=1";

        $parametros = [];

        if ($curp != "") {

            $sql .= " AND curp_alumno LIKE :curp";
            $parametros["curp"] = "%" . strtoupper($curp) . "%";

        }

        if ($plantel != "") {

            $sql .= " AND UPPER(plantel_alumno) LIKE :plantel";
            $parametros["plantel"] = "%" . strtoupper($plantel) . "%";

        }

        if ($estatus != "") {

            $sql .= " AND estatus_alumno = :estatus";
            $parametros["estatus"] = $estatus;

        }

        $sql .= " ORDER BY apaterno_alumno,nombre_alumno LIMIT 100";

        $stmt = $conexion->prepare($sql);
        $stmt->execute($parametros);

        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalResultados = count($resultados);

        if ($totalResultados == 0) {

            $mensaje = "No se encontraron registros para los criterios capturados.";

        }

    }

}

$jsonVisible = isset($_GET["json"]);

?>


<!DOCTYPE html>

<html lang="es">

<head>



<meta charset="utf-8">



<title>Consulta CONALEP</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


</head>



<body class="bg-light">

<div class="small text-muted mt-2">

<b>CURP:</b> b√∫squeda parcial (m√≠nimo 10 caracteres).<br>

<b>Plantel:</b> b√∫squeda parcial.<br>

<b>Estatus:</b> b√∫squeda exacta.

</div>

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-success text-white">

<div class="d-flex justify-content-between align-items-center">

<div>


<h4 class="mb-0">Consulta de Alumnos CONALEP</h4>

<small>Sistema de consulta</small>

</div>

<div class="text-end">

<strong><?= htmlspecialchars($usuario["nombre"]) ?></strong><br>

<small><?= htmlspecialchars($usuario["correo"]) ?></small>

</div>

</div>

</div>

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">CURP</label>

<input
type="text"
name="curp"
maxlength="18"
placeholder="M√≠nimo 10 caracteres"
class="form-control"
value="<?= htmlspecialchars($curp) ?>">


</div>


<div class="col-md-4 mb-3">

<label class="form-label">Plantel</label>

<input
type="text"
name="plantel"
placeholder="B√∫squeda parcial"
class="form-control"
value="<?= htmlspecialchars($plantel) ?>">


</div>

<div class="col-md-4 mb-3">

<label class="form-label">Estatus</label>

<input
type="text"
name="estatus"
class="form-control"

value="<?= htmlspecialchars($estatus) ?>">


</div>

</div>

<button class="btn btn-success">

Consultar

</button>

<button
type="submit"
name="json"
value="1"
class="btn btn-info">

Mostrar JSON

</button>



<a href="logout.php" class="btn btn-outline-secondary">

Cerrar sesi√≥n

</a>

</form>

<hr>


<?php if (!$buscar): ?>
<div class="alert alert-secondary">
Ingrese al menos un criterio de b√∫squeda (CURP, Plantel o Estatus).
</div>
<?php else: ?>
<div class="alert alert-info">
<strong><?= number_format($totalResultados) ?></strong>
<?= $totalResultados == 1 ? "registro encontrado." : "registros encontrados."; ?>
</div>


<?php if ($totalResultados == 0): ?>

<div class="alert alert-warning">
No se encontraron registros.
</div>
<?php else: ?>

<div class="table-responsive">

	<table class="table table-striped table-bordered table-hover table-sm align-middle">

		<thead class="table-dark">

		<tr>

			<th>CURP</th>

			<th>NIA</th>

			<th>Nombre</th>

			<th>Plantel</th>

			<th>Estatus</th>

			<th>Tutor</th>

			<th>Tel√©fono</th>


		</tr>

		</thead>

<tbody>

<?php foreach($resultados as $r): ?>

<tr>
	<td><?= htmlspecialchars($r["curp_alumno"]) ?></td>
	<td><?= htmlspecialchars($r["nia_alumno"]) ?></td>
<td>


<?= htmlspecialchars(

$r["apaterno_alumno"] .

" " .

$r["amaterno_alumno"] .

" " .

$r["nombre_alumno"]

) ?>


</td>


<td><?= htmlspecialchars($r["plantel_alumno"]) ?></td>

<td><?= htmlspecialchars($r["estatus_alumno"]) ?></td>

<td><?= htmlspecialchars($r["nombre_tutor"]) ?></td>

<td><?= htmlspecialchars($r["telefono_tutor"]) ?></td>

<td>


</td>

</tr>



<?php endforeach; ?>


</tbody>


</table>
<?php if($buscar && $jsonVisible): ?>

<hr>

<div class="card mt-4">

<div class="card-header bg-dark text-white">

Respuesta JSON del WebService

</div>

<div class="card-body">

<textarea
id="jsonRespuesta"
class="form-control"
rows="20"
readonly
style="
font-family:Consolas;
background:#1E1E1E;
color:#00FF88;
">

<?= json_encode(
[
    "success"=>true,
    "total"=>$totalResultados,
    "data"=>$resultados
],
JSON_PRETTY_PRINT |
JSON_UNESCAPED_UNICODE |
JSON_UNESCAPED_SLASHES
) ?>

</textarea>

<br>

<button
class="btn btn-primary"
onclick="copiarJson()">

 Copiar JSON

</button>

</div>

</div>

<?php endif; ?>


</div>
<?php endif; ?>
<?php endif; ?>
</div>

</div>

</div>

<div class="modal fade" id="jsonModal" tabindex="-1">

<div class="modal-dialog modal-lg">



<div class="modal-content">



<div class="modal-header bg-primary text-white">



<h5 class="modal-title">



Respuesta JSON del Web Service



</h5>



<button

type="button"

class="btn-close btn-close-white"

data-bs-dismiss="modal">

</button>



</div>



<div class="modal-body">



<pre

id="jsonViewer"

style="

background:#1E1E1E;

color:#00FF88;

padding:15px;

border-radius:8px;

max-height:500px;

overflow:auto;

font-size:13px;

"></pre>



</div>



<div class="modal-footer">



<button

class="btn btn-success"

onclick="copiarJson()">

Ì†ΩÌ≥ã Copiar JSON

</button>

<button

class="btn btn-secondary"

data-bs-dismiss="modal">

Cerrar

</button>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<script>

function mostrarJson(registro)
{

    let respuesta = {

        success: true,

        total: 1,

        data: registro

    };

    document.getElementById("jsonViewer").textContent =
        JSON.stringify(respuesta, null, 4);

    new bootstrap.Modal(
        document.getElementById("jsonModal")
    ).show();
</script>

<script>

function copiarJson(){

    let t=document.getElementById("jsonRespuesta");

    t.select();

    document.execCommand("copy");

    alert("JSON copiado.");

}

</script>


</body>



</html>
