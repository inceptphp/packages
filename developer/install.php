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
 * $ incept install
 * $ incept install -f | --force
 * $ incept install --skip-sql
 * $ incept install --skip-versioning
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //load some packages
  $emitter = $this('event');
  $terminal = $this('terminal');

  //setup the configs
  if (!$request->hasStage('skip-configs')) {
    $terminal->system('Setting up configuration...');
    $emitter->emit('install-configs', $request, $response);
  }

  if (!$request->hasStage('skip-mkdir')) {
    $terminal->system('Setting up folders...');
    $emitter->emit('install-mkdir', $request, $response);
  }

  if (!$request->hasStage('skip-chmod')) {
    $terminal->system('Setting up file permissions...');
    $emitter->emit('install-chmod', $request, $response);
  }

  if (!$request->hasStage('skip-packages')) {
    $terminal->system('Installing Packages ...');
    $emitter->emit('install-packages', $request, $response);
  }

  if ($response->isError()) {
    return;
  }

  $recommended = $response->getResults('recommended');
  if (is_array($recommended)) {
    $terminal->info('Recommended actions:');
    foreach ($recommended as $recommendation) {
      $terminal->info(sprintf(' - %s', $recommendation));
    }
    $terminal->info(' - yarn build');
  }
});

/**
 * Copies sample files to config files
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('install-configs', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //load some packages
  $terminal = $this('terminal');

  //whether to ask questions
  $force = $request->hasStage('f') || $request->hasStage('force');
  $root = new RecursiveDirectoryIterator(INCEPT_CWD . '/config');
  $files = new RegexIterator(
    new RecursiveIteratorIterator($root),
    '/.+\.sample\.php$/',
    RegexIterator::GET_MATCH
  );

  foreach($files as $file) {
    $source = $file[0];
    $destination = str_replace('.sample.php', '.php', $source);

    $message = sprintf('Overwrite %s?(y)', $destination);
    if (!$force
      && file_exists($destination)
      && $terminal->input($message, 'y') !== 'y'
    ) {
      $message = sprintf('Skipping %s', $destination);
      $terminal->warning($message);
      continue;
    }

    if(!copy($source, $destination)) {
      $message = sprintf('Failed copying %s to %s', $source, $destination);
      $terminal->error($message, false);
      continue;
    }

    $message = sprintf('Creating %s', $destination);
    $terminal->info($message);
  }
});

/**
 * Makes directories to be used for other things...
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('install-mkdir', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //load some packages
  $terminal = $this('terminal');

  $folders = [
    INCEPT_CWD . '/cache',
    INCEPT_CWD . '/cache/compiled',
    INCEPT_CWD . '/cache/log',
    INCEPT_CWD . '/config',
    INCEPT_CWD . '/config/language',
    INCEPT_CWD . '/config/schema',
    INCEPT_CWD . '/config/fieldset',
    INCEPT_CWD . '/public/upload'
  ];

  foreach ($folders as $folder) {
    if (!is_dir($folder)) {
      $terminal->system(sprintf('Making %s', $folder));
      mkdir($folder, 0777);
    }
  }
});

/**
 * Makes folders writable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('install-chmod', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //load some packages
  $terminal = $this('terminal');

  $folders = [
    INCEPT_CWD . '/cache',
    INCEPT_CWD . '/cache/compiled',
    INCEPT_CWD . '/cache/log',
    INCEPT_CWD . '/config',
    INCEPT_CWD . '/config/language',
    INCEPT_CWD . '/config/schema',
    INCEPT_CWD . '/config/fieldset',
    INCEPT_CWD . '/public/upload'
  ];

  foreach ($folders as $folder) {
    if (is_dir($folder)) {
      $terminal->info(sprintf('Setting permissions for %s', $folder));
      if(!@chmod($folder, 0777)) {
        $message = sprintf('Failed. try `chmod -R 777 %s`', $folder);
        $terminal->error($message, false);
      }
    }
  }

  // special case for config folder
  $configDirectories = glob(INCEPT_CWD . '/config/*', GLOB_ONLYDIR);

  // map each directories
  foreach ($configDirectories as $directory) {
    @chmod($directory, 0777);
  }

  $root = new RecursiveDirectoryIterator(INCEPT_CWD . '/config');
  $files = new RegexIterator(
    new RecursiveIteratorIterator($root),
    '/.+\.php$/',
    RegexIterator::GET_MATCH
  );

  foreach($files as $file) {
    $source = $file[0];
    @chmod($source, 0777);
  }
});

/**
 * Installs Packages
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('install-packages', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //load some packages
  $emitter = $this('event');
  $terminal = $this('terminal');

  //for each packages
  foreach ($this->getPackages() as $name => $package) {
    $terminal->info(sprintf('Installing %s', $name));
    $event = sprintf('package-%s-install', $name);
    $emitter->emit($event, $request, $response);
  }
});
