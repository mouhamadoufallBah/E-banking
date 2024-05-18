<?php

namespace Moohamad\EBankingApi\Core;
class Middleware
{
    public function midleware()
    {
        if (isset($_SERVER['Authorization'])) {
            $token = trim($_SERVER['Authorization']);
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeader = apache_request_headers();

            if (isset($requestHeader['Authorization'])) {
                $token = trim($requestHeader['Authorization']);
            }
        }

        if (!isset($token) || !preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            http_response_code(400);

            echo json_encode(['message' => 'Token introuvable']);

            exit;
        }

        $token = str_replace('Bearer ', '', $token);

        $jwt = new Jwt();

        if (!$jwt->isValid($token)) {
            http_response_code(400);

            echo json_encode(['message' => 'Token invalide']);

            exit;
        }

        if (!$jwt->check($token, SECRET)) {
            http_response_code(403);

            echo json_encode(['message' => 'Token invalid']);
            exit;
        }

        if ($jwt->isExpired($token)) {
            http_response_code(403);

            echo json_encode(['message' => 'Token a expiré']);
            exit;
        }

        return $jwt->getPayload($token);
    }
}