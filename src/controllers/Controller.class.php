<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * MERCURY InfoCentral
 *
 * User: lromero
 * Date: 2/17/2019
 * Time: 12:10 PM
 */


namespace controllers;


abstract class Controller
{
    abstract public function processURI(string $uri): array;
}