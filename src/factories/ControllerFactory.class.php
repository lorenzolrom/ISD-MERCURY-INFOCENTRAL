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
        // Core
        'history' => 'controllers\HistoryController',
        'users' => 'controllers\UserController',
        'roles' => 'controllers\RoleController',
        'permissions' => 'controllers\PermissionController',
        'currentUser' => 'controllers\CurrentUserController',
        'authenticate' => 'controllers\AuthenticateController',
        'bulletins' => 'controllers\BulletinController',
        'secrets' => 'controllers\SecretController',
        'tokens' => 'controllers\TokenController',
        'notifications' => 'controllers\NotificationController'
    );

    /**
     * @param HTTPRequest $request
     * @return Controller
     * @throws ControllerNotFoundException
     */
    public static function getController(HTTPRequest $request): Controller
    {
        $route = $request->next();

        $controllers = array_merge(self::CONTROLLERS, \Config::OPTIONS['additionalRoutes']);

        if(!in_array($route, array_keys($controllers)))
            throw new ControllerNotFoundException($route);

        $controller = $controllers[$route];

        return new $controller($request);
    }
}