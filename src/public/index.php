<?php
/**
 * LLR Technologies
 * part of LLR Enterprises - www.llrweb.com/tech
 *
 * Mercury Application Platform
 * InfoCentral
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:06 PM
 */

spl_autoload_register(
    function($className)
    {
        $class = '../' . str_replace("\\", "/", $className) . ".class.php";

        if(file_exists($class))
        {
            /** @noinspection PhpIncludeInspection */
            require_once($class);
        }
    }
);

\controllers\FrontController::processRequest();
