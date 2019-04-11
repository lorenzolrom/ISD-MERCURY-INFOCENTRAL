<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:06 PM
 */

spl_autoload_register(
    function($className)
    {
        /** @noinspection PhpIncludeInspection */
        require_once('../' . str_replace("\\", "/", $className) . ".class.php");
    }
);

\controllers\FrontController::processRequest();