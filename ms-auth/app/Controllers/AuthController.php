<?php

namespace App\Controllers;

use App\Models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function login(Request $request, Response $response)
    {
        $datos = $request->getParsedBody();
        $usuario = $datos['usuario'] ?? '';
        $contrasena = $datos['contrasena'] ?? '';

        $user = Usuario::where(function ($q) use ($usuario) {
            $q->where('usuario', $usuario)->orWhere('correo', $usuario);
        })->where('contrasena', $contrasena)->where('estado', 'activo')->first();

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales incorrectas']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $token = bin2hex(random_bytes(16));
        $user->token = $token;
        $user->sesion_activa = true;
        $user->save();

        $response->getBody()->write(json_encode([
            'mensaje' => 'Login exitoso',
            'token' => $token,
            'rol' => $user->rol,
            'nombre' => $user->nombre
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function logout(Request $request, Response $response)
    {
        $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));
        $user = Usuario::where('token', $token)->first();

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Token invalido']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $user->token = null;
        $user->sesion_activa = false;
        $user->save();

        $response->getBody()->write(json_encode(['mensaje' => 'Sesion cerrada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function validar(Request $request, Response $response)
    {
        $token = str_replace('Bearer ', '', $request->getHeaderLine('Authorization'));
        $user = Usuario::where('token', $token)->where('sesion_activa', true)->first();

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'No autorizado']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $response->getBody()->write(json_encode(['valido' => true, 'rol' => $user->rol, 'nombre' => $user->nombre]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
