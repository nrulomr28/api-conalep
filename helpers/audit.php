<?php



declare(strict_types=1);



function registrarAuditoria(

    PDO $conexion,

    ?int $clienteId,

    ?int $tokenId,

    string $metodo,

    string $endpoint,

    ?string $operacion,

    ?string $criterio,

    ?int $cantidadResultados,

    int $codigoHttp,

    ?string $ip,

    ?string $userAgent,

    ?int $duracionMs,

    ?string $detalle = null

): void {



    $sql = "

        INSERT INTO api_auditoria (

            cliente_id,

            token_id,

            metodo,

            endpoint,

            operacion,

            criterio,

            cantidad_resultados,

            codigo_http,

            ip,

            user_agent,

            duracion_ms,

            detalle

        )

        VALUES (

            :cliente_id,

            :token_id,

            :metodo,

            :endpoint,

            :operacion,

            :criterio,

            :cantidad_resultados,

            :codigo_http,

            :ip,

            :user_agent,

            :duracion_ms,

            :detalle

        )

    ";



    $stmt = $conexion->prepare($sql);



    $stmt->execute([

        ':cliente_id' => $clienteId,

        ':token_id' => $tokenId,

        ':metodo' => $metodo,

        ':endpoint' => $endpoint,

        ':operacion' => $operacion,

        ':criterio' => $criterio,

        ':cantidad_resultados' => $cantidadResultados,

        ':codigo_http' => $codigoHttp,

        ':ip' => $ip,

        ':user_agent' => $userAgent,

        ':duracion_ms' => $duracionMs,

        ':detalle' => $detalle

    ]);

}
