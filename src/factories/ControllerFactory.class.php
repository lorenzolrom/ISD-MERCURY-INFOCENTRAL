<?php
/**
 * LLR Technologies & Associated Services
 * Information Systems Development
 *
 * INS WEBNOC API
 *
 * User: lromero
 * Date: 4/05/2019
 * Time: 4:12 PM
 */


namespace factories;


use controllers\AuthenticateController;
use controllers\bookworm\VolumeController;
use controllers\Controller;
use controllers\CurrentUserController;
use controllers\facilities\BuildingController;
use controllers\facilities\LocationController;
use controllers\itsm\ApplicationController;
use controllers\itsm\RegistrarController;
use controllers\itsm\VHostController;
use controllers\PermissionController;
use controllers\RoleController;
use controllers\UserController;
use exceptions\ControllerNotFoundException;
use models\HTTPRequest;

/**
 * Class ControllerFactory
 *
 * This class defines routes and what controller handles them
 *
 * @package factories
 */
class ControllerFactory
{
    /**
     * @param HTTPRequest $request
     * @return Controller
     * @throws ControllerNotFoundException
     */
    public static function getController(HTTPRequest $request): Controller
    {
        $route = $request->next();
        switch($route)
        {
            case "volumes": // TODO: remove when project is completed
                return new VolumeController($request);
            case "vhosts":
                return new VHostController($request);
            case "registrars":
                return new RegistrarController($request);
            case "applications":
                return new ApplicationController($request);
            case "buildings":
                return new BuildingController($request);
            case "locations":
                return new LocationController($request);
            case "users":
                return new UserController($request);
            case "roles":
                return new RoleController($request);
            case "permissions":
                return new PermissionController($request);
            case "currentUser":
                return new CurrentUserController($request);
            case "authenticate":
                return new AuthenticateController($request);
            default:
                throw new ControllerNotFoundException($route);
        }
    }
}