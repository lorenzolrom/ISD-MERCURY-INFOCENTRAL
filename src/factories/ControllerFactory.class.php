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
    // Core controller routes
    private const CONTROLLERS = array(
        'history' => 'controllers\HistoryController',
        'users' => 'controllers\UserController',
        'roles' => 'controllers\RoleController',
        'permissions' => 'controllers\PermissionController',
        'currentUser' => 'controllers\CurrentUserController',
        'authenticate' => 'controllers\AuthenticateController',
        'bulletins' => 'controllers\BulletinController',
        'secrets' => 'controllers\SecretController',
        'tokens' => 'controllers\TokenController',
        'badlogins' => 'controllers\BadLoginController',
        'notifications' => 'controllers\NotificationController'
    );

    /**
     * @param HTTPRequest $request
     * @return Controller
     * @throws ControllerNotFoundException
     */
    public static function getController(HTTPRequest $request): Controller
    {
        // Hold all controllers
        $controllers = self::CONTROLLERS;

        // Import routes from extensions
        foreach(\Config::OPTIONS['enabledExtensions'] as $extension)
        {
            // Check for ExtConfig.class inside extension
            $extConfig = "extensions\\$extension\\ExtConfig";

            // If it doesn't exist, skip the extension
            if(!class_exists($extConfig))
                continue;

            // Merge ROUTES from ExtConfig into $controllers
            $extConfig = new $extConfig();

            $controllers = array_merge($controllers, $extConfig::ROUTES);
        }

        // Find controller
        $route = $request->next();

        if(!in_array($route, array_keys($controllers)))
            throw new ControllerNotFoundException($route);

        $controller = $controllers[$route];

        return new $controller($request);
    }
}