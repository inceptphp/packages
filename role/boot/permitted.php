<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Package\Role\RoleException;

use UGComponents\Http\Router;
use UGComponents\Event\EventEmitter;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Checks if route is permitted based on permission roles
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function (RequestInterface $request, ResponseInterface $response) {
  //prevent session in cli mode
  if (php_sapi_name() === 'cli') {
    return;
  }

  $permissions = $request->getSession('role', 'role_permissions');

  //make sure permissions is an array
  if (!is_array($permissions) || empty($permissions)) {
    // allow front end access even without session
    $permissions = [
      [
        'path' => '(?!/(admin))/**',
        'label' => 'All Front End Access',
        'method' => 'all'
      ]
    ];
  }

  // path
  $home = $this('config')->get('settings', 'home') ?? '/';

  //at least allow the home page
  if (!trim((string) $request->getPath('string'))
    || $request->getPath('string') === $home
  ) {
    return true;
  }

  // initialize router
  $router = new Router;

  // iterate on each permissions
  foreach($permissions as $permission) {
    // validate route
    $router->route(
      $permission['method'],
      $permission['path'],
      function($request, $response) {
        //if good, let's end checking
        return false;
      }
    );
  }

  // process router
  $router->process($request, $response);

  //let's interpret the results
  if($router->getEventEmitter()->getMeta() === EventEmitter::STATUS_INCOMPLETE) {
    //the role passes
    return true;
  }

  //changed the route to something impossible
  $request->setPath(sprintf('/%s', uniqid('role-error')));

  throw RoleException::forNotPermitted();
};
