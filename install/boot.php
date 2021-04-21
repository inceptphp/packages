<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

return function(RequestInterface $request, ResponseInterface $response) {
  //set the initial steps
  $response->setPage('install_steps', [
    'permissions' => [
      'label' => 'Permissions',
      'icon' => 'fa-lock',
      'path' => '/install'
    ],
    'database' => [
      'label' => 'Database Setup',
      'icon' => 'fa-database',
      'path' => '/install/database'
    ],
    'settings' => [
      'label' => 'Settings',
      'icon' => 'fa-cog',
      'path' => '/install/settings'
    ],
  ]);

  //set the partials
  $this('handlebars')->registerPartialFromFile(
    'install_head',
    __DIR__ . '/template/_head.html'
  );

  $this('handlebars')->registerPartialFromFile(
    'install_left',
    __DIR__ . '/template/_left.html'
  );
};
