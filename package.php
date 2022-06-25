<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Incept\Framework\Schema;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * $ incept inceptphp/packages ...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages', function (
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

  $name = 'inceptphp/packages';
  $event = sprintf('%s-%s', $name, $event);

  $this('event')->emit($event, $request, $response);
});

/**
 * $ incept inceptphp/packages help
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages-help', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $this('terminal')
    ->warning('Package Commands:')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages install')
    ->info(' Installs this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages update')
    ->info(' Updates this package')

    ->output(PHP_EOL)

    ->success('incept inceptphp/packages uninstall')
    ->info(' Removes this package')

    ->output(PHP_EOL);
});

/**
 * $ incept inceptphp/packages install
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $config = $this('config');
  $terminal = $this('terminal');

  //custom name of this package
  $name = 'inceptphp/packages';
  if ($request->hasStage('name')) {
    $name = $request->getStage('name');
  }

  //get the package
  $package = $config->get('packages', $name);
  //get the current version
  $current = null;
  // if version is set
  if (is_array($package) && isset($package['version'])) {
    // get the current version
    $current = $package['version'];
  }

  //if it's already installed
  if ($current) {
    return $terminal->error(sprintf('%s is already installed', $name), false);
  }

  // install package
  $version = '0.0.0';
  //if there's an install package
  if ($request->hasStage('install')) {
    //run it through the install method
    $install = $request->getStage('install');
    $version = $this($name)->install($install, '0.0.0');
  }

  // update the config
  $config->set('packages', $name, [
    'version' => $version,
    'active' => true,
    'locked' => false
  ]);

  $terminal->success(sprintf('%s %s installed', $name, $version));
  $response->setError(false);
});

/**
 * $ incept inceptphp/packages update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages-update', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $config = $this('config');
  $terminal = $this('terminal');

  //custom name of this package
  $name = 'inceptphp/packages';
  if ($request->hasStage('name')) {
    $name = $request->getStage('name');
  }

  //get the current version
  $current = $config->get('packages', $name);
  // if version is set
  if (is_array($current) && isset($current['version'])) {
    // get the current version
    $current = $current['version'];
  } else {
    $current = null;
  }

  //if it's not installed
  if (!$current) {
    $message = sprintf('%s is not installed', $name);
    return $terminal->error($message, false);
  }

  // get available version
  $version = $current;
  //if there's an install package
  if ($request->hasStage('install')) {
    //run it through the install method
    $install = $request->getStage('install');
    $version = $this($name)->version($install);
    //if available < current
    if (version_compare($version, $current, '<')) {
      $message = sprintf('%s - %s < %s', $name, $version, $current);
      return $terminal->error($message, false);
    //if available = current
    } else if (version_compare($version, $current, '=')) {
      $message = sprintf('%s - %s = %s', $name, $version, $current);
      return $terminal->error($message, false);
    }

    // update package
    $version = $this($name)->install($install, $current);
  }

  //if available = current
  if (version_compare($version, $current, '=')) {
    $message = sprintf('%s - %s = %s', $name, $version, $current);
    return $terminal->error($message, false);
  }

  // update the config
  $config->set('packages', $name, [
    'version' => $version,
    'active' => true,
    'locked' => false
  ]);

  $terminal->success(sprintf('%s updated to %s', $name, $version));
  $response->setError(false);
});

/**
 * $ incept inceptphp/packages uninstall
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('inceptphp/packages-uninstall', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $config = $this('config');
  $emitter = $this('event');
  $terminal = $this('terminal');

  //custom name of this package
  $name = 'inceptphp/packages';
  if ($request->hasStage('name')) {
    $name = $request->getStage('name');
  }

  // if it's not installed
  if (!$config->get('packages', $name)) {
    $message = sprintf('%s is not installed', $name);
    return $terminal->error($message, false);
  }

  //if there is a schema folder
  if ($request->hasStage('schema')) {
    $schema = $request->getStage('schema');
    //scan through each file in the schema folder
    foreach (scandir($schema) as $file) {
      //if it's not a php file
      if(substr($file, -4) !== '.php') {
        //skip
        continue;
      }

      //get the original schema data (in this package)
      $original = include sprintf('%s/%s', $schema, $file);

      //get the schema in the project
      $file = sprintf('%s/%s.php', Schema::getFolder(), $original['name']);
      //if no schema file
      if (!file_exists($file)) {
        //there's nothing to remove
        continue;
      }

      //get the data from schema in the project
      $data = include $file;
      //if the schema in the project is different
      if ($original !== $data) {
        //dont remove
        $terminal->error(sprintf(
          '%s schema could not be removed because it has changed',
          $original['name']
        ), false);

        continue;
      }

      //----------------------------//
      // 1. Prepare Data
      //setup a new RnR
      $payload = $this->makePayload();

      $payload['request']
        ->setStage('mode', 'permanent')
        ->setStage('schema', $data['name']);

      //----------------------------//
      // 2. Process Request
      $emitter->emit(
        'system-schema-remove',
        $payload['request'],
        $payload['response']
      );

      //----------------------------//
      // 3. Interpret Results
      if ($payload['response']->isError()) {
        $terminal->error(sprintf(
          '%s - %s',
          $data['name'],
          $payload['response']->getMessage()
        ), false);

        continue;
      }

      $terminal->success(sprintf('%s was removed', $data['name']));
    }
  }

  // get package config
  $packages = $config->get('packages');

  // remove package from config
  if (isset($packages[$name])) {
    unset($packages[$name]);
    // update package config
    $config->set('packages', $packages);
  }

  $terminal->success(sprintf('%s uninstalled', $name));
  $response->setError(false);
});
