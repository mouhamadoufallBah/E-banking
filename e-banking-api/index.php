<?php
require_once __DIR__ . "/src/Core/Router.php";

use Moohamad\EBankingApi\Core\Router;

$app = new Router();
$app->start();