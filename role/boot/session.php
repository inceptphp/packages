<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Adds role to session
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function (RequestInterface $request, ResponseInterface $response) {
  //prevent session in cli mode
  if (php_sapi_name() === 'cli') {
    return;
  }

  $loggedin = $request->hasSession('me');
  //if there's already a role
  if ($request->hasSession('role')) {
    //if logged in
    if ($loggedin) {
      //compare the logged in role with the role assigned
      //we do this instead of listening for login or logout
      $test1 = $request->getSession('role', 'role_slug');
      $test2 = $request->getSession('me', 'role', 'role_slug');
      //if they are the same then we good
      if ($test1 === $test2) {
        //pass
        return;
      }
    //they logged out, so if role assigned is guest, then we good
    } else if ($request->getSession('role', 'role_slug') === 'guest') {
      //pass
      return;
    }
  }

  //so at this point either
  // - the logged in role and the role assigned are not the same
  // - or they are logged out and the role isnt a guest

  //get the role
  $slug = $request->getSession('me', 'auth_role') ?? 'guest';

  //look up the role
  $role = $this('event')->call(
    'system-object-role-detail',
    $request->clone(true)->setStage('role_slug', $slug)
  );

  //if no role found
  if (!$role || empty($role)) {
    $role = $this('event')->call(
      'system-object-role-detail',
      $request->clone(true)->setStage('role_slug', 'guest')
    );
  }

  //assign the role
  $response->setSession('role', $role);
};
