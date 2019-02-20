<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:32 AM
 */

// Include configuration file
require_once (dirname(__FILE__) . "/../config.php");

// Register Class Inclusion Script
spl_autoload_register(
    function($className)
    {
        /** @noinspection PhpIncludeInspection */
        require_once("../" . str_replace("\\", "/", $className) . ".class.php");
    }
);

header('Content-type: application/vnd.api+json');
echo \controllers\FrontController::processRequest();