<?php



use Firebase\JWT\JWT;

use Firebase\JWT\Key;



class JwtHelper

{

    public static function generarToken(

        string $usuario,

        string $secret,

        int $expiration

    ): string

    {

        $payload = [

            'sub' => $usuario,

            'iat' => time(),

            'exp' => time() + $expiration

        ];



        return JWT::encode(

            $payload,

            $secret,

            'HS256'

        );

    }



    public static function validarToken(

        string $token,

        string $secret

    )

    {

        return JWT::decode(

            $token,

            new Key($secret, 'HS256')

        );

    }

}
