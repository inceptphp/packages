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
 * $ incept inceptphp/packages/website ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/website', function (
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

  $name = 'inceptphp/packages/website';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages/website help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/website-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('website Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/website install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/website update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages/website uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages/website install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/website-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/website')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-install', $request, $response);

  $response->setResults('recommended', 'website', 'bin/incept inceptphp/packages/role populate');
});

/**
 * $ incept inceptphp/packages/website update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/website-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/website')
    ->setStage('install', __DIR__ . '/install');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-update', $request, $response);
});

/**
 * $ incept inceptphp/packages/website uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/website-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $request
    ->setStage('name', 'inceptphp/packages/website')
    ->setStage('schema', __DIR__ . '/schema');
  //just do the default installer
  $this('event')->emit('inceptphp/packages-uninstall', $request, $response);
});

/**
 * $ incept inceptphp/packages/website populate
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages/website-populate', function (
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

    //get emitter
    $emitter = $this('event');
    foreach($data['fixtures'] as $fixture) {
      $payload = $request
        ->clone(true)
        ->setStage($fixture)
        ->setStage('schema', $data['name']);

      $emitter->call('system-object-create', $payload);
    }
  }

  //setting up combining variables
  $combineFlag = 0;
  $combinedQuery = "";
  //Going through each file in fixtures
  $database = $this('pdo')->get('sql-main');
  foreach (scandir(__DIR__ . '/fixtures') as $file) {
    //if it's not an sql file
    if(substr($file, -4) !== '.sql') {
      //skip
      continue;
    }
    //put all lines of the file into an array
    $sql = file(sprintf('%s/fixtures/%s', __DIR__, $file), FILE_IGNORE_NEW_LINES);

    //to check for the final iteration for the array
    $len = count($sql);

    //working on every line that was read. i is used as a key. query is used as the value
    foreach($sql as $i=>$query) {
      //skips everything that is either empty or contains a comment
      if ($query == "" || preg_match_all("/(^--.*)/m", $query)) {
        $combineFlag = 0;
        //if combinedQuery is not empty, work on it. Empty after. If it is, don't work on it.
        if ($combinedQuery != ""){
          //combinedQuery is not empty means that it just finished combining.
          try {
            $test = $database->query($combinedQuery);
          } catch (Throwable $e){
            continue;
          }

          $combinedQuery = "";
        }
        continue;
      } else if ($query != "" && $combineFlag == 1) {
        $combinedQuery = $combinedQuery . " " . $query;
        if ($i == $len - 1){
          //last combinedQuery in the file gets worked on here. This is the very last line if it's combined.
          try {
            $test = $database->query($combinedQuery);
          } catch (Throwable $e){
            continue;
          }
        }
        continue;
      }

      if (preg_match_all("/(.*);/m", $query)) {
        if ($combineFlag == 1){ //it's the end of the query since we see ; Time to execute
          try {
           $test = $database->query($combinedQuery);
           $combineFlag = 0;
         } catch (Throwable $e){
           continue;
         }
       } else { //It's already complete even without the combine.
          try {
           $test = $database->query($query);
         } catch (Throwable $e){
           continue;
         }
       }
     } else { //we have to combine since we don't see a ;
       $combineFlag = 1; //becomes true to combine
       $combinedQuery = $query; //make it the start
       continue;
     }

    }
    //reset values for the next
    $combineFlag = 0;
    $combinedQuery = "";
  }

  if ($this->isPackage('inceptphp/packages/role')) {
    $role = $emitter->call('system-object-role-detail', [
      'role_slug' => 'developer'
    ]);

    if (isset($role['role_admin_menu'])) {
      $exists = false;
      foreach ($role['role_admin_menu'] as $item) {
        if ($item['path'] === 'menu-content') {
          $exists = true;
          break;
        }
      }

      if (!$exists) {
        $menu = [];
        foreach ($role['role_admin_menu'] as $item) {
          $menu[] = $item;
          if ($item['path'] !== '/admin') {
            continue;
          }

          $menu[] = [
            'icon' => 'fas fa-newspaper',
            'path' => 'menu-content',
            'label' => 'Content',
            'submenu' => [
              [
                'path' => '/admin/system/object/post/search',
                'label' => 'Posts'
              ],
              [
                'path' => '/admin/system/object/file/search',
                'label' => 'Files'
              ]
            ]
          ];

          $menu[] = [
            'icon' => 'fas fa-th-large',
            'path' => 'menu-layout',
            'label' => 'Layout',
            'submenu' => [
              [
                'path' => '/admin/system/object/page/search',
                'label' => 'Pages'
              ],
              [
                'path' => '/admin/system/object/block/search',
                'label' => 'Blocks'
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
