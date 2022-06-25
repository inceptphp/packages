<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/* Search Routes
-------------------------------- */

/**
 * Render the language search page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/language/search', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = [];

  $folder = $this('config')->getFolder('language');
  //loop through all the php files
  foreach (glob(sprintf('%s/*.php', $folder)) as $file) {
    $data['rows'][] = basename($file, '.php');
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/language';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('search_head', 'html', true)
    ->registerPartialFromFolder('search_links', 'html', true)
    ->registerPartialFromFolder('search_row', 'html', true)
    ->renderFromFolder('search', $data);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //set content
  $response
    ->set('page', 'title', $this('lang')->translate('Languages'))
    ->set('page', 'class', 'page-admin-language-search page-admin')
    ->setContent($body);

  //render page
  $this('admin')->render($request, $response);
});

/* Create Routes
-------------------------------- */

/**
 * Render the language create page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/language/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $data['item']['translations'] = [];

  //look through each package
  $packages = $this->getPackages();
  foreach ($packages as $package) {
    //get the file path of the package
    $path = $package->getPackagePath();
    //if no path found
    if (!$path) {
      //skip
      continue;
    }

    //get all the html files
    $directory = new RecursiveDirectoryIterator($path);
    $iterator = new RegexIterator(
      new RecursiveIteratorIterator($directory),
      '/^.+\.html$/i',
      RecursiveRegexIterator::GET_MATCH
    );

    //for each match
    foreach ($iterator as $file) {
      //get the contents of the file
      $contents = file_get_contents($file[0]);
      //look for {{_ 'foo'}}
      preg_match_all('/(\{\#*_ \'([^\']+)\')|(\{\#*_ "([^"]+)")/', $contents, $matches);
      //combine the matches as translation keys
      $keys = array_merge($matches[2], $matches[4]);
      //loop through the keys found
      foreach ($keys as $key) {
        //if blank key
        if (!trim($key)) {
          //skip
          continue;
        }
        //add it to the master translation list
        if (!isset($data['item']['translations'][$key])) {
          $data['item']['translations'][$key] = $key;
        }
      }
    }
  }

  //if we only want the data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Add Language');
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = '/admin/spa/language/create';
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/language';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  //render the body
  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('form', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the language create page
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/language/create', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  //----------------------------//
  // 2. Validate Data
  if (!isset($data['filename']) || !trim($data['filename'])) {
    return $response->setError(true, 'Language code is required');
  }

  $translations = $this('config')->get('language/' . $data['filename']);

  if (is_array($translations)) {
    return $response->setError(true, 'Language exists');
  }

  if (!isset($data['translations']) || !is_array($data['translations'])) {
    $data['translations'] = [];
  }

  //----------------------------//
  // 3. Process Data
  $this('config')->set('language/' . $data['filename'], $data['translations']);

  //add a flash
  $response->setSession('flash', [
    'message' => $this('lang')->translate('Language %s added.', $data['filename']),
    'type' => 'success'
  ]);

  $response->setError(false);
});

/* Update Routes
-------------------------------- */

/**
 * Render the language update screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/language/update/:filename', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = [ 'item' => $request->getStage() ];

  $data['item']['translations'] = $this('config')->get(sprintf(
    'language/%s',
    $data['item']['filename']
  ));

  if (!is_array($data['item']['translations'])) {
    $response->setError(true, 'Invalid Language');
    return $this('admin')->invalid($response);
  }

  //look through each package
  $packages = $this->getPackages();
  foreach ($packages as $package) {
    //get the file path of the package
    $path = $package->getPackagePath();
    //if no path found
    if (!$path) {
      //skip
      continue;
    }

    //get all the html files
    $directory = new RecursiveDirectoryIterator($path);
    $iterator = new RegexIterator(
      new RecursiveIteratorIterator($directory),
      '/^.+\.html$/i',
      RecursiveRegexIterator::GET_MATCH
    );

    //for each match
    foreach ($iterator as $file) {
      //get the contents of the file
      $contents = file_get_contents($file[0]);
      //look for {{_ 'foo'}}
      preg_match_all('/(\{\#*_ \'([^\']+)\')|(\{\#*_ "([^"]+)")/', $contents, $matches);
      //combine the matches as translation keys
      $keys = array_merge($matches[2], $matches[4]);
      //loop through the keys found
      foreach ($keys as $key) {
        //if blank key
        if (!trim($key)) {
          //skip
          continue;
        }
        //add it to the master translation list
        if (!isset($data['item']['translations'][$key])) {
          $data['item']['translations'][$key] = $key;
        }
      }
    }
  }

  //if we only want the data
  if ($request->getStage('render') === 'false') {
    return $response->setResults($data);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Update Language');
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/language/update/%s',
      $data['item']['filename']
    );
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/language';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  //render the body
  $body = $this('handlebars')
    ->setTemplateFolder($template)
    //->registerPartialFromFolder('form_tabs')
    ->renderFromFolder('form', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the language update
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/language/update/:filename', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  //----------------------------//
  // 2. Validate Data
  if (!isset($data['filename']) || !trim($data['filename'])) {
    return $response->setError(true, 'Language code is required');
  }

  if (!isset($data['translations']) || !is_array($data['translations'])) {
    $data['translations'] = [];
  }

  //----------------------------//
  // 3. Process Data
  $this('config')->set('language/' . $data['filename'], $data['translations']);

  //add a flash
  $response->setSession('flash', [
    'message' => $this('lang')->translate('Language %s updated.', $data['filename']),
    'type' => 'success'
  ]);

  $response->setError(false);
});

/* Confirm Routes
-------------------------------- */

/**
 * Renders remove language screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/language/remove/:filename', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $translations = $this('config')->get(sprintf('language/%s', $data['filename']));

  if (!is_array($translations)) {
    $response->setError(true, 'Invalid Language');
    return $this('admin')->invalid($response);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Remove %s', $data['filename']);
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to remove %s ? This cannot be undone.',
      $data['filename']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf(
      '/admin/spa/language/remove/%s/confirmed',
      $data['filename']
    );
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/object';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the remove language
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/language/remove/:filename/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();
  $path = sprintf('/language/%s.php', $data['filename']);
  $file = $this('config')->getFolder($path);

  //----------------------------//
  // 2. Process Request
  if (file_exists($file)) {
    unlink($file);
  }

  $response->setError(false);
});
