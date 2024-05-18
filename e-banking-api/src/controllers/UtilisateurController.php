<?php

use const Moohamad\EBankingApi\Core\SECRET;

header('Access-Control-Allow-Origin: http://localhost:4200');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once('./src/core/JWT.php');
require_once("./src/core/Database.php");
require_once("./src/model/UtilisateurModel.php");

class UtilisateurController
{
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $database = new Moohamad\EBankingApi\Core\Database();

            $db = $database->getConnexion();

            $user = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $statement = $user->getAll();

            if ($statement->rowCount() > 0) {
                $data[] = $statement->fetchAll();

                http_response_code(200);

                echo json_encode([
                    [
                        "data" => $data,
                        "status_code" => 200
                    ]
                ]);
            } else {
                echo json_encode(
                    ['message' => "Aucun Admin disponible"]
                );
            }
        } else {
            http_response_code(405);
            echo json_encode(
                ['message' => "Ce methode n'est pas autorisée"]
            );
        }
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisé']);
        } else {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();
            $user = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->email) && !empty($data->password)) {
                $user->email = htmlspecialchars($data->email);
                $user->password = htmlspecialchars($data->password);

                $result = $user->login($data->email, $data->password);

                if ($result) {
                    $header = [
                        'alg' => 'HS256',
                        'typ' => 'JWT'
                    ];
                    $jwt = new Moohamad\EBankingApi\CORE\Jwt();
                    $token = $jwt->generate($header, $result, SECRET);

                    http_response_code(201);
                    echo json_encode(
                        [
                            'message' => "connexion avec succées",
                            'data' => $result,
                            'token' => $token
                        ]
                    );
                } else {
                    http_response_code(503);
                    echo json_encode(
                        ['message' => "Email ou mot de passe incorecte"]
                    );
                }
            } else {
                echo json_encode(
                    ['message' => "Les données ne sont pas au complet"]
                );
            }
        }
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisé']);
        } else {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();
            $user = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->nomComplet) && !empty($data->password) && !empty($data->code_pin) && !empty($data->telephone) && !empty($data->id_role)) {
                $user->nomComplet = htmlspecialchars($data->nomComplet);
                $user->email = !empty($data->email) ? $user->email = htmlspecialchars($data->email) : "";
                $user->password = htmlspecialchars($data->password);
                $user->telephone = htmlspecialchars($data->telephone);
                $user->code_pin = htmlspecialchars($data->code_pin);
                $user->id_role = intval($data->id_role);

                $result = $user->dynamicInsert();

                echo json_encode(
                    [
                        'status_code' => http_response_code(200),
                        'message' => "Inscription avec succées",
                        'data' => $result
                    ]
                );
            } else {
                echo json_encode(
                    ['message' => "Les données ne sont pas au complet"]
                );
            }
        }
    }

    public function update(int $id)
    {
        $allowed_methods = ['PUT', 'PATCH'];
        $request_method = strtoupper($_SERVER['REQUEST_METHOD']);

        if (!in_array($request_method, $allowed_methods)) {
            http_response_code(405);
            echo json_encode(['message' => 'Méthode non autorisée']);
            return;
        }

        $database = new Moohamad\EBankingApi\Core\Database();
        $db = $database->getConnexion();
        $user = new Moohamad\EBankingApi\Model\Utilisateurs($db);

        $data = json_decode(file_get_contents("php://input"));
        if ($id && !empty((array)$data)) { // Vérifie si l'ID est valide et si les données ne sont pas vides
            $user->id = $id;
            foreach ($data as $key => $value) {
                if (!empty($value) && property_exists($user, $key)) {
                    $user->{$key} = htmlspecialchars($value);
                }
            }

            $result = $user->dynamicUpdate($id);

            echo json_encode(
                [
                    'status_code' => http_response_code(200),
                    'message' => "Profil modifié avec succès",
                    'data' => $result
                ]
            );
        } else {
            echo json_encode(
                ['message' => "Les données ne sont pas au complet ou l'ID n'est pas valide"]
            );
        }
    }
}
