<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use Storm\SqlFactory;
use Incept\Framework\Schema;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Storm Installer
 *
 * $ incept storm-install
 * $ incept storm-install -f | --force
 * $ incept storm-install -h 127.0.0.1 -u root -p 123
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-install', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error, or skip
  if ($response->isError() || $request->hasStage('skip-sql')) {
    //dont continue
    return;
  }

  //load some packages
  $emitter = $this('event');
  $terminal = $this('terminal');

  //make a private payload
  $payload = $this->makePayload(true);
  //ask some questions
  $emitter->call('sql-install-questions',
    $payload['request'],
    $payload['response']
  );

  //move results to stage
  $config = $payload['response']->getResults();
  $payload['request']->setStage('config', $config);

  //SQL
  $terminal->system('Setting up SQL...');

  //save the database config
  $emitter->call('sql-install-config', $payload['request'], $response);
  //no errors here
  //build the database
  $emitter->call('sql-build', $payload['request'], $response);

  if ($response->isError()) {
    return;
  }

  $response->setResults('recommended', 'storm', 'bin/incept sql populate');
});

/**
 * Storm Installer - Questions
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-install-questions', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $terminal = $this('terminal');

  //whether to ask questions
  $force = $request->hasStage('f') || $request->hasStage('force');

  $config = [
    'name' => false,
    'host' => false,
    'user' => false,
    'pass' => false
  ];

  //name
  if ($request->hasStage('name')) {
    $config['name'] = $request->getStage('name');
  } else if ($request->hasStage('n')) {
    $config['name'] = $request->getStage('n');
  }

  //host
  if ($request->hasStage('h')) {
    $config['host'] = $request->getStage('h');
  } else if ($request->hasStage('host')) {
    $config['host'] = $request->getStage('host');
  }

  //user
  if ($request->hasStage('u')) {
    $config['user'] = $request->getStage('u');
  } else if ($request->hasStage('user')) {
    $config['user'] = $request->getStage('user');
  }

  //pass
  if ($request->hasStage('p')) {
    $config['pass'] = $request->getStage('p');
  } else if ($request->hasStage('password')) {
    $config['pass'] = $request->getStage('password');
  }

  $questions = [
    'name' => [
      'prompt' => 'What is the name of the SQL database to install?(testing_db)',
      'default' => 'testing_db'
    ],
    'host' => [
      'prompt' => 'What is the SQL server address?(127.0.0.1)',
      'default' => '127.0.0.1'
    ],
    'user' => [
      'prompt' => 'What is the SQL server user name?(root)',
      'default' => 'root'
    ],
    'pass' => [
      'prompt' => 'What is the SQL server password?(enter for none)',
      'default' => ''
    ]
  ];

  foreach ($questions as $name => $question) {
    if ($config[$name]) {
      continue;
    }

    if ($force) {
      $config[$name] = $question['default'];
      continue;
    }

    $config[$name] = $terminal->input(
      $question['prompt'],
      $question['default']
    );
  }

  $response->setResults($config);
});

/**
 * Storm Installer - Save Configuration
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('sql-install-config', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if there's already an error
  if ($response->isError()) {
    //dont continue
    return;
  }

  //load some packages
  $pdo = $this('pdo');
  $storm = $this('storm');
  $config = $this('config');

  //get the configuration
  $main = $request->getStage('config');
  //default type is mysql (sorry)
  if (!isset($main['type'])) {
    $main['type'] = 'mysql';
  }

  $build = [
    'type' => $main['type'],
    'host' => $main['host'],
    'user' => $main['user'],
    'pass' => $main['pass']
  ];

  //hard coding sql-main. is there a better way to do this?
  $settings = $config->get('settings');
  $settings['pdo'] = 'sql-main';

  $config
    ->purge()
    //build config
    ->set('services', 'sql-build', $build)
    //main config
    ->set('services', 'sql-main', $main)
    //default database
    ->set('settings', $settings);

  //setup PDO
  $pdo
    ->register('sql-build', $build)
    ->register('sql-main', $main);

  //make this the default database
  $storm->load('sql-main');

  //all good now
  $response->setError(false);
});
