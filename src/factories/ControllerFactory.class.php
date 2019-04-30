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

use controllers\Controller;
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
    private const CONTROLLERS = array(
        'hosts' => 'controllers\itsm\HostController',
        'commodities' => 'controllers\itsm\CommodityController',
        'warehouses' => 'controllers\itsm\WarehouseController',
        'assets' => 'controllers\itsm\AssetController',
        'vhosts' => 'controllers\itsm\VHostController',
        'registrars' => 'controllers\itsm\RegistrarController',
        'applications' => 'controllers\itsm\ApplicationController',
        'buildings' => 'controllers\facilities\BuildingController',
        'locations' => 'controllers\facilities\LocationController',
        'history' => 'controllers\HistoryController',
        'users' => 'controllers\UserController',
        'roles' => 'controllers\RoleController',
        'permissions' => 'controllers\PermissionController',
        'currentUser' => 'controllers\CurrentUserController',
        'authenticate' => 'controllers\AuthenticateController'
    );

    /**
     * @param HTTPRequest $request
     * @return Controller
     * @throws ControllerNotFoundException
     */
    public static function getController(HTTPRequest $request): Controller
    {
        $route = $request->next();

        if(!in_array($route, array_keys(self::CONTROLLERS)))
            throw new ControllerNotFoundException($route);

        $controller = self::CONTROLLERS[$route];

        return new $controller($request);
    }
}