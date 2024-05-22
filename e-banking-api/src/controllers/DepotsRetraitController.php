<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once('./src/core/JWT.php');
require_once("./src/core/Database.php");
require_once("./src/model/DepotsRetraitModel.php");
require_once("./src/model/UtilisateurModel.php");

class DepotsRetraitController
{

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $database = new Moohamad\EBankingApi\Core\Database();

            $db = $database->getConnexion();

            $depotRetrait = new Moohamad\EBankingApi\Model\DepotsRetrait($db);

            $statement = $depotRetrait->getAll();

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
                http_response_code(201);
                echo json_encode(
                    ['message' => "Pas de donnée disponible", "status_code" => 201]
                );
            }
        } else {
            http_response_code(405);
            echo json_encode(
                ['message' => "Ce methode n'est pas autorisée"]
            );
        }
    }

    public function getDepotRetraitByIdUser(int $idUser)
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();

            $depotRetrait = new Moohamad\EBankingApi\Model\DepotsRetrait($db);

            $statement = $depotRetrait->getAlldepotRetraitByIdAgent($idUser);

            if ($statement->rowCount() > 0) {
                $data[] = $statement->fetchAll();
                http_response_code(200);

                echo json_encode([
                    "data" => $data,
                    "status_code" => 200,
                    "Message" => "Récupération de la liste des depot & Retrait effectuer avec succées"
                ]);
            } else {
                http_response_code(204);
                echo json_encode([
                    "status_code" => 204,
                    "Message" => "Récupération de la liste des depot & Retrait effectuer avec succées"
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                "status_code" => 405,
                'message' => "Ce methode n'est pas autorisée"
            ]);
        }
    }

    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();

            $depotRetrait = new Moohamad\EBankingApi\Model\DepotsRetrait($db);
            $utilisateur = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->agent_id) && !empty($data->utilisateur_id) && !empty($data->montant) && !empty($data->type)  && !empty($data->code_pin)) {
                $depotRetrait->agent_id = intval($data->agent_id);
                $depotRetrait->utilisateur_id = intval($data->utilisateur_id);
                $depotRetrait->type = intval($data->type);
                $depotRetrait->montant = intval($data->montant);

                $agent = $utilisateur->get($depotRetrait->agent_id)->fetch();
                $client = $utilisateur->get($depotRetrait->utilisateur_id)->fetch();

                if ($agent['code_pin'] == intVal($data->code_pin) &&$agent['solde'] > $depotRetrait->montant) {
                    $result = $depotRetrait->dynamicInsert();

                    $agent['solde'] -= $depotRetrait->montant;
                    $client['solde'] += $depotRetrait->montant;

                    $utilisateur->updateSolde($agent['id'], $agent['solde']);
                    $utilisateur->updateSolde($client['id'], $client['solde']);

                    http_response_code(200);

                    echo json_encode(
                        [
                            'status_code' => 200,
                            'message' => "depotRetrait fait avec succées",
                            'data' => $result,
                            'exp' => $agent,
                            'dest' => $client,
                        ]
                    );
                } else {
                    http_response_code(400);

                    echo json_encode(
                        [
                            'status_code' => 400,
                            'message' => "Veuillez entrer le bon code pin Ou vérifier votre solde"
                        ]
                    );
                }
            } else {
                http_response_code(400);
                echo json_encode([
                    "status_code" => 400,
                    'message' => "Les données ne sont pas au complet"
                ]);
            }
        } else {
            http_response_code(405);
            echo json_encode([
                "status_code" => 405,
                'message' => "Ce methode n'est pas autorisée"
            ]);
        }
    }

    public function cancel(int $iddepotRetrait)
    {
        $allowed_methods = ['PUT', 'PATCH'];
        $request_method = strtoupper($_SERVER['REQUEST_METHOD']);

        if (in_array($request_method, $allowed_methods)) {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();

            $depotRetrait = new Moohamad\EBankingApi\Model\DepotsRetrait($db);
            $utilisateur = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->code_pin)) {
                $currentdepotRetrait = $depotRetrait->get($iddepotRetrait)->fetch();
                if ($currentdepotRetrait['status'] != 0) {
                    $agent = $utilisateur->get($currentdepotRetrait['agent_id'])->fetch();
                    $client = $utilisateur->get($currentdepotRetrait['utilisateur_id'])->fetch();

                    if ($agent['code_pin'] == $data->code_pin) {
                        $depotRetrait->canceldepotRetrait($iddepotRetrait);

                        $agent['solde'] += $currentdepotRetrait['montant'];
                        $client['solde'] -= $currentdepotRetrait['montant'];
                        $utilisateur->updateSolde($agent['id'], $agent['solde']);
                        $utilisateur->updateSolde($client['id'], $client['solde']);

                        http_response_code(200);

                        echo json_encode(
                            [
                                'status_code' => 200,
                                'message' => "depotRetrait a été annuler",
                                'exp' => $agent,
                                'dest' => $client,
                            ]
                        );
                    } else {
                        http_response_code(400);

                        echo json_encode(
                            [
                                'status_code' => 400,
                                'message' => "Veuillez entrer le bon code pin"
                            ]
                        );
                    }
                } else {
                    echo json_encode(
                        [
                            'status_code' => 400,
                            'message' => "Cette depotRetrait à dèja été annuler"
                        ]
                    );
                }
            } else {
                http_response_code(400);

                echo json_encode(
                    [
                        'status_code' => 400,
                        'message' => "Veuillez entrer votre code pin"
                    ]
                );
            }
        } else {
            http_response_code(405);
            echo json_encode([
                "status_code" => 405,
                'message' => "Ce methode n'est pas autorisée"
            ]);
        }
    }
}
