<?php

namespace Moohamad\EBankingApi\Core;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(200);
    exit;
}

class Router
{
    public function start()
    {
        //recperer l'url
        $uri = $_SERVER['REQUEST_URI'];

        //on verifie si l'url ecrite se termine par un slash et si oui on l'enleve
        if (!empty($uri) && $uri != '/' && $uri[-1] === "/") {
            $uri = substr($uri, 0, -1);

            //on envoie un code de redirection permanent
            http_response_code(301);

            //on redirige vers le lien demandé
            header('Location: ' . $uri);
        }

        //Recuper les paramatre pour ensuite le scinder puis la 1ers elmt sera le controller, 2em methode, le reste les paramatre
        $params = explode('/', $_GET['p']);

        if ($params[0] !== '') {

            $controllerName = (array_shift($params));

            $controllerFilePath =  'src/controllers' . '/' . ucfirst($controllerName) . 'Controller.php';

            if (file_exists($controllerFilePath)) {
                // Inclure le fichier du contrôleur
                require_once $controllerFilePath;

                // Construire le nom de la classe du contrôleur
                $controllerClassName = $controllerName . 'Controller';
              
                // Instancier le contrôleur
                $controller = new $controllerClassName();

                $methode = (isset($params[0])) ? array_shift($params) : 'index';

                if (method_exists($controller, $methode)) {
                    (isset($params[0])) ? call_user_func_array([$controller, $methode], $params) : $controller->$methode();
                } else {
                    echo "page not found";
                }
            } else {
                echo "page not found";
            }
        } else {
            echo "pas de parametre";
        }
    }
}
