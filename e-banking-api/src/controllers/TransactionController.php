<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

require_once('./src/core/JWT.php');
require_once("./src/core/Database.php");
require_once("./src/model/TransactionModel.php");
require_once("./src/model/UtilisateurModel.php");

class TransactionController
{

    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $database = new Moohamad\EBankingApi\Core\Database();

            $db = $database->getConnexion();

            $transaction = new Moohamad\EBankingApi\Model\Transaction($db);

            $statement = $transaction->getAll();

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
                    ['message' => "Aucun Utilsateur disponible"]
                );
            }
        } else {
            http_response_code(405);
            echo json_encode(
                ['message' => "Ce methode n'est pas autorisée"]
            );
        }
    }

    public function getTransactionByIdUser(int $idUser)
    {
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();

            $transaction = new Moohamad\EBankingApi\Model\Transaction($db);

            $statement = $transaction->getAllTransactionByIdUser($idUser);

            if ($statement->rowCount() > 0) {
                $data[] = $statement->fetchAll();
                http_response_code(200);

                echo json_encode([
                    "data" => $data,
                    "status_code" => 200,
                    "Message" => "Récupération de la liste des transaction effectuer avec succées"
                ]);
            } else {
                http_response_code(204);
                echo json_encode([
                    "status_code" => 204,
                    "Message" => "Récupération de la liste des transaction effectuer avec succées"
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

            $transaction = new Moohamad\EBankingApi\Model\Transaction($db);
            $utilisateur = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $data = json_decode(file_get_contents("php://input"));
            if (!empty($data->expediteur_id) && !empty($data->destinataire_id) && !empty($data->montant) && !empty($data->code_pin)) {
                $transaction->expediteur_id = intval($data->expediteur_id);
                $transaction->destinataire_id = intval($data->destinataire_id);
                $transaction->montant = intval($data->montant);

                $expediteur = $utilisateur->get($transaction->expediteur_id)->fetch();
                $destinataire = $utilisateur->get($transaction->destinataire_id)->fetch();



                //    var_dump($utilisateur['code_pin']); die();
                if ($expediteur['code_pin'] == intVal($data->code_pin) && $expediteur['solde'] > $transaction->montant) {
                    $result = $transaction->dynamicInsert();

                    $expediteur['solde'] -= $transaction->montant;
                    $destinataire['solde'] += $transaction->montant;

                    $utilisateur->updateSolde($expediteur['id'], $expediteur['solde']);
                    $utilisateur->updateSolde($destinataire['id'], $destinataire['solde']);

                    http_response_code(200);

                    echo json_encode(
                        [
                            'status_code' => 200,
                            'message' => "Transaction fait avec succées",
                            'data' => $result,
                            'exp' => $expediteur,
                            'dest' => $destinataire,
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

    public function cancel(int $idTransaction)
    {
        $allowed_methods = ['PUT', 'PATCH'];
        $request_method = strtoupper($_SERVER['REQUEST_METHOD']);

        if (in_array($request_method, $allowed_methods)) {
            $database = new Moohamad\EBankingApi\Core\Database();
            $db = $database->getConnexion();

            $transaction = new Moohamad\EBankingApi\Model\Transaction($db);
            $utilisateur = new Moohamad\EBankingApi\Model\Utilisateurs($db);

            $data = json_decode(file_get_contents("php://input"));
            if(!empty($data->code_pin)) {
                $currentTransaction = $transaction->get($idTransaction)->fetch();
                if($currentTransaction['status'] != 0) {
                    $expediteur = $utilisateur->get($currentTransaction['expediteur_id'])->fetch();
                    $destinataire = $utilisateur->get($currentTransaction['destinataire_id'])->fetch();
    
                    if($expediteur['code_pin'] == $data->code_pin){
                        $transaction->cancelTransaction($idTransaction);
    
                        $expediteur['solde'] += $currentTransaction['montant'];
                        $destinataire['solde'] -= $currentTransaction['montant'];
                        $utilisateur->updateSolde($expediteur['id'], $expediteur['solde']);
                        $utilisateur->updateSolde($destinataire['id'], $destinataire['solde']);
    
                        http_response_code(200);
    
                        echo json_encode(
                            [
                                'status_code' => 200,
                                'message' => "Transaction a été annuler",
                                'exp' => $expediteur,
                                'dest' => $destinataire,
                            ]
                        );
                    }else{
                        http_response_code(400);
    
                        echo json_encode(
                            [
                                'status_code' => 400,
                                'message' => "Veuillez entrer le bon code pin"
                            ]
                        );
                    }
                }else{
                    echo json_encode(
                        [
                            'status_code' => 400,
                            'message' => "Cette transaction à dèja été annuler"
                        ]
                    );
                }
            }else{
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
