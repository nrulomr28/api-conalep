<?php



class AuditHelper

{

    public static function registrar(

        PDO $db,

        string $usuario,

        string $endpoint,

        string $resultado

    ): void

    {

        $sql = "

            INSERT INTO api_auditoria

            (

                fecha,

                usuario,

                ip_origen,

                endpoint,

                metodo,

                parametros,

                resultado

            )

            VALUES

            (

                NOW(),

                :usuario,

                :ip,

                :endpoint,

                :metodo,

                :parametros,

                :resultado

            )

        ";



        $stmt = $db->prepare($sql);



        $stmt->execute([

            ':usuario' => $usuario,

            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',

            ':endpoint' => $_SERVER['REQUEST_URI'] ?? '',

            ':metodo' => $_SERVER['REQUEST_METHOD'] ?? '',

            ':parametros' => json_encode($_GET),

            ':resultado' => $resultado

        ]);

    }

}
