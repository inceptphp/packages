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
 * Render the install page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/install', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //there could be no active step
  $activeStep = -1;
  //get all the install steps so we can configure the state for each
  $steps = array_values($response->getPage('install_steps'));
  //for each step
  foreach($steps as $i => $step) {
    //if this is the path
    if ($step['path'] === '/install') {
      //mark the step and break out
      $activeStep = $i;
      break;
    }
  }

  //for each step (again)
  foreach($steps as $i => $step) {
    //if the step is before the active step
    if ($i < $activeStep) {
      //the state is passed
      $steps[$i]['passed'] = true;
    //if the step is the current step
    } else if ($i === $activeStep) {
      //the state is active
      $steps[$i]['active'] = true;
    }
  }

  //save the updated install steps
  $response->setPage('install_steps', $steps);

  $redirect = '/admin';
  if (isset($steps[$data['step'] + 1]['path'])) {
    $redirect = $steps[$data['step'] + 1]['path'];
  }

  $folders = [
    '/config',
    '/config/schema',
    '/config/fieldset',
    '/cache',
    '/cache/compiled',
    '/cache/log',
    '/public/upload'
  ];

  foreach ($folders as $folder) {
    if (!is_dir(INCEPT_CWD . $folder)) {
      $data['errors'][] = sprintf('%s%s not found.', INCEPT_CWD, $folder);
    } else if (!is_writable(INCEPT_CWD . $folder)) {
      $data['errors'][] = sprintf('%s%s not writable.', INCEPT_CWD, $folder);
    }
  }

  if (!isset($data['errors']) || empty($data['errors'])) {
    return $this('http')->redirect($redirect);
  }

  //----------------------------//
  // 2. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('head')
    ->registerPartialFromFolder('left')
    ->renderFromFolder('permissions', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->setPage('title', $this('lang')->translate('Install - Permission Check'))
    ->setPage('class', 'page-install')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response, 'blank');
});

/**
 * Start installing
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/install', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //load some packages
  $config = $this('config');
  $emitter = $this('event');
  $terminal = $this('terminal');

  //get the settings so it's not overrided
  $settings = $config->get('settings');
  //get the services so it's not overrided
  $services = $config->get('services');

  //dont print the output
  $terminal->verbose(false);
  //dont ask questions
  $request->setStage('force', 1);
  //set database settings
  $request->setStage(0, $services[$settings['pdo']]['name']);
  $request->setStage('host', $services[$settings['pdo']]['host']);
  $request->setStage('user', $services[$settings['pdo']]['user']);
  $request->setStage('pass', $services[$settings['pdo']]['pass']);

  //get all the install steps so we can configure the state for each
  $steps = array_values($response->getPage('install_steps'));

  //for each step
  foreach($steps as $i => $step) {
    //the state is passed
    $steps[$i]['passed'] = true;
  }

  //save the updated install steps
  $response->setPage('install_steps', $steps);

  //----------------------------//
  // 2. Process Data
  // copy samples to config files
  $emitter->emit('install-configs', $request, $response);
  //get errors
  $errors = $terminal->getErrors();
  //if no errors so far
  if (empty($errors)) {
    //soft set the new settings
    $config->set('settings', array_merge(
      $config->get('settings'),
      $settings
    ));

    $emitter->emit('install-packages', $request, $response);
  }

  //get all the logs
  $data['logs'] = $terminal->getLogs();
  //get all the errors separately
  $data['errors'] = $terminal->getErrors();

  //if no errors, lastly disable this package
  if (empty($data['errors'])) {
    //get all the packages
    $packages = $config->get('packages');
    foreach ($packages as $i => $package) {
      if ($package['path'] === 'inceptphp/packages/install') {
        $packages[$i]['active'] = false;
      }
    }

    //save the packages
    $config->set('packages', $packages);
  }

  //----------------------------//
  // 3. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('install', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->setPage('title', $this('lang')->translate('Install'))
    ->setPage('class', 'page-install')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response, 'blank');
});

/**
 * Render the install database page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/install/database', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //there could be no active step
  $activeStep = -1;
  //get all the install steps so we can configure the state for each
  $steps = array_values($response->getPage('install_steps'));
  //for each step
  foreach($steps as $i => $step) {
    //if this is the path
    if ($step['path'] === '/install/database') {
      //mark the step and break out
      $activeStep = $i;
      break;
    }
  }

  //for each step (again)
  foreach($steps as $i => $step) {
    //if the step is before the active step
    if ($i < $activeStep) {
      //the state is passed
      $steps[$i]['passed'] = true;
    //if the step is the current step
    } else if ($i === $activeStep) {
      //the state is active
      $steps[$i]['active'] = true;
    }
  }

  //save the updated install steps
  $response->setPage('install_steps', $steps);

  $data = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'name' => 'incept',
    'user' => 'root',
    'pass' => ''
  ];

  //get the default key
  $key = $this('config')->get('settings', 'pdo');
  //get the current configiguration
  $config = $this('config')->get('services', $key);
  //if theres a host
  if (isset($config['host'])) {
    $host = explode(':', $config['host']);
    if (isset($host[0]) && trim($host[0])) {
      $data['host'] = $host[0];
    }

    if (isset($host[1]) && trim($host[1])) {
      $data['port'] = $host[1];
    }
  }

  //if theres a name
  if (isset($config['name']) && trim($config['name'])) {
    $data['name'] = $config['name'];
  }

  //if theres a user
  if (isset($config['user']) && trim($config['user'])) {
    $data['user'] = $config['user'];
  }

  //if theres a pass
  if (isset($config['pass']) && trim($config['pass'])) {
    $data['pass'] = $config['pass'];
  }

  //----------------------------//
  // 2. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('database', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->setPage('title', $this('lang')->translate('Install - Database Setup'))
    ->setPage('class', 'page-install-database page-install')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response, 'blank');
});

/**
 * Process the install settings page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/install/database', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //get the name of the main database
  $key = $this('config')->get('settings', 'pdo');
  $services = $this('config')->get('services');

  $services[$key]['type'] = 'mysql';
  $services[$key]['host'] = sprintf(
    '%s:%s',
    $request->getStage('host'),
    $request->getStage('port')
  );
  $services[$key]['name'] = $request->getStage('name');
  $services[$key]['user'] = $request->getStage('user');
  $services[$key]['pass'] = $request->getStage('pass');

  //----------------------------//
  // 2. Process Data
  $this('config')->set('services', $services);

  //next: figure out where to go next

  //there could be no active step
  $activeStep = -1;
  //get all the install steps so we can figure out where to redirect after
  $steps = array_values($response->getPage('install_steps'));
  //for each step
  foreach($steps as $i => $step) {
    //if this is the path
    if ($step['path'] === '/install/database') {
      //mark the step and break out
      $activeStep = $i;
      break;
    }
  }

  if (isset($steps[$activeStep + 1]['path'])) {
    return $this('http')->redirect($steps[$activeStep + 1]['path']);
  }

  $this('http')->routeTo('post', '/install', $request, $response);
});

/**
 * Render the install settings page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/install/settings', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  //there could be no active step
  $activeStep = -1;
  //get all the install steps so we can configure the state for each
  $steps = array_values($response->getPage('install_steps'));
  //for each step
  foreach($steps as $i => $step) {
    //if this is the path
    if ($step['path'] === '/install/database') {
      //mark the step and break out
      $activeStep = $i;
      break;
    }
  }

  //for each step (again)
  foreach($steps as $i => $step) {
    //if the step is before the active step
    if ($i < $activeStep) {
      //the state is passed
      $steps[$i]['passed'] = true;
    //if the step is the current step
    } else if ($i === $activeStep) {
      //the state is active
      $steps[$i]['active'] = true;
    }
  }

  //save the updated install steps
  $response->setPage('install_steps', $steps);

  $data = $this('config')->get('settings');

  //----------------------------//
  // 2. Render Template
  $template = __DIR__ . '/template';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('settings', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->setPage('title', $this('lang')->translate('Install - Settings'))
    ->setPage('class', 'page-install-settings page-install')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response, 'blank');
});

/**
 * Process the install settings page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/install/settings', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $settings = $this('config')->get('settings');

  $settings['name'] = $request->getStage('name');
  $settings['email'] = $request->getStage('email');
  $settings['timezone'] = $request->getStage('timezone');
  $settings['language'] = $request->getStage('language');
  $settings['currency'] = $request->getStage('currency');

  //----------------------------//
  // 2. Process Data
  $this('config')->set('settings', $settings);

  //next: figure out where to go next

  //there could be no active step
  $activeStep = -1;
  //get all the install steps so we can figure out where to redirect after
  $steps = array_values($response->getPage('install_steps'));
  //for each step
  foreach($steps as $i => $step) {
    //if this is the path
    if ($step['path'] === '/install/settings') {
      //mark the step and break out
      $activeStep = $i;
      break;
    }
  }

  if (isset($steps[$activeStep + 1]['path'])) {
    return $this('http')->redirect($steps[$activeStep + 1]['path']);
  }

  $this('http')->routeTo('post', '/install', $request, $response);
});
