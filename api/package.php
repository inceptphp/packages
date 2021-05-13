<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * $ incept inceptphp/packages/api ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/api', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $event = 'help';

  if($request->hasStage(0)) {
    $event = $request->getStage(0);
    $request->removeStage(0);
  }

  if($request->hasStage()) {
    $data = [];
    $stage = $request->getStage();
    foreach($stage as $key => $value) {
      if(!is_numeric($key)) {
        $data[$key] = $value;
      } else {
        $data[$key - 1] = $value;
      }

      $request->removeStage($key);
    }

    $request->setStage($data);
  }

  $name = 'inceptphp/packages/api';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages/api help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/api-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('Profile Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/api install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/api update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/api uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/api populate')
    ->info(' Populates the first set of apis (developer, admin, guest)')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages/api install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/api-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/api')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-install', $request, $response);

  $response->setResults('recommended', 'api', 'bin/incept inceptphp/packages/api populate');
});

/**
 * $ incept inceptphp/packages/api update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/api-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/api')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-update', $request, $response);
});

/**
 * $ incept inceptphp/packages/api uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/api-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/api')
    ->setStage('schema', __DIR__ . '/schema');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-uninstall', $request, $response);
});

/**
 * $ incept inceptphp/packages/api populate
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/api-populate', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //get emitter
  $emitter = $this('event');
  //scan through each file
  foreach (scandir(__DIR__ . '/schema') as $file) {
    //if it's not a php file
    if(substr($file, -4) !== '.php') {
      //skip
      continue;
    }

    //get the schema data
    $data = include sprintf('%s/schema/%s', __DIR__, $file);

    //if no name
    if (!isset($data['name'], $data['fixtures'])
      || !is_array($data['fixtures'])
    ) {
      //skip
      continue;
    }

    foreach($data['fixtures'] as $fixture) {
      $payload = $request
        ->clone(true)
        ->setStage($fixture)
        ->setStage('schema', $data['name']);

      $emitter->call('system-object-create', $payload);
    }
  }

  if ($this->isPackage('inceptphp/packages/role')) {
    $role = $emitter->call('system-object-role-detail', [
      'role_slug' => 'developer'
    ]);

    if (isset($role['role_admin_menu'])) {
      $exists = false;
      foreach ($role['role_admin_menu'] as $item) {
        if ($item['path'] === 'menu-api') {
          $exists = true;
          break;
        }
      }

      if (!$exists) {
        $menu = [];
        foreach ($role['role_admin_menu'] as $item) {
          $menu[] = $item;
          if ($item['path'] !== 'menu-admin') {
            continue;
          }echo 'pass';

          $menu[] = [
            'icon' => 'fas fa-code',
            'path' => 'menu-api',
            'label' => 'API',
            'submenu' => [
                [
                  'path' => '/admin/system/object/app/search',
                  'label' => 'Applications'
                ],
                [
                  'path' => '/admin/system/object/session/search',
                  'label' => 'Sessions'
                ],
                [
                  'path' => '/admin/system/object/scope/search',
                  'label' => 'Scopes'
                ],
                [
                  'path' => '/admin/system/object/rest/search',
                  'label' => 'REST Calls'
                ],
                [
                  'path' => '/admin/system/object/webhook/search',
                  'label' => 'Webhooks'
                ]
            ]
          ];
        }

        $emitter->call('system-object-role-update', [
          'role_id' => $role['role_id'],
          'role_admin_menu' => json_encode($menu, JSON_PRETTY_PRINT)
        ]);
      }
    }
  }
});
