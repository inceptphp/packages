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
 * Builds the admin menu
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function (RequestInterface $request, ResponseInterface $response) {
  //prevent session in cli mode
  if (php_sapi_name() === 'cli') {
    return;
  }

  //merge the current admin menu with the menu found in the users role
  $menu = array_merge(
    $response->get('admin_menu') ?? [],
    $request->getSession('role', 'role_admin_menu') ?? []
  );

  $response->set('admin_menu', $menu);
};
