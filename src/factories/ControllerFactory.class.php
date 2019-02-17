<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * FASTAPPS RESTful Service
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 10:43 AM
 */


namespace factories;


use controllers\Controller;

class ControllerFactory
{
    public static function getController(string $controllerClass): Controller
    {
        return new $controllerClass();
    }
}