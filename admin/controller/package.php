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
 * Manages installed packages
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/package/search', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data['rows'] = $this('config')->get('packages');

  foreach ($data['rows'] as $i => $row) {
    //get the real path
    $path = $this($row['name'])->getPackagePath();
    //if no path
    if (!$path) {
      //skip
      continue;
    }

    //make the file name
    $file = sprintf('%s/.incept.json', $path);
    //if file does not exists
    if (!file_exists($file)) {
      //try another file name
      $file = sprintf('%s/composer.json', $path);
    }

    //if file exists
    if (file_exists($file)) {
      //parse the file
      $info = json_decode(file_get_contents($file), true);
      //add to rows
      $data['rows'][$i]['info'] = $info;
    }
  }

  //----------------------------//
  // 2. Render Template
  $data['title'] = $this('lang')->translate('Packages');

  $template = dirname(__DIR__) . '/template/package';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('search_head')
    ->registerPartialFromFolder('search_row')
    ->renderFromFolder('search', $data);

  //set content
  $response
    ->setPage('title', $data['title'])
    ->setPage('class', 'page-admin-package-search page-admin')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $this('admin')->render($request, $response);
});

/**
 * Renders package disable screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/package/enable/:index', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $index = $data['index'];
  $packages = $this('config')->get('packages');

  if (!isset($packages[$index])) {
    $response->setError(true, 'Invalid Package ID');
    return $this('admin')->invalid($response);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Disable %s',
      $packages[$index]['name']
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to enable %s ?',
      $packages[$index]['name']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf('/admin/spa/package/enable/%s/confirmed', $index);
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/package';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the package disable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/package/enable/:index/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $index = $request->getStage('index');
  $packages = $this('config')->get('packages');

  if (!isset($packages[$index])) {
    return $response->setError(true, 'Invalid Package ID');
  }

  //----------------------------//
  // 2. Process Request
  $packages[$index]['active'] = true;
  $this('config')->set('packages', $packages);
  $response->setError(false);
});

/**
 * Renders package disable screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/package/disable/:index', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $index = $data['index'];
  $packages = $this('config')->get('packages');

  if (!isset($packages[$index])) {
    $response->setError(true, 'Invalid Package ID');
    return $this('admin')->invalid($response);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Disable %s',
      $packages[$index]['name']
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to disable %s ?',
      $packages[$index]['name']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf('/admin/spa/package/disable/%s/confirmed', $index);
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/package';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the package disable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/package/disable/:index/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $index = $request->getStage('index');
  $packages = $this('config')->get('packages');

  if (!isset($packages[$index])) {
    return $response->setError(true, 'Invalid Package ID');
  }

  //----------------------------//
  // 2. Process Request
  $packages[$index]['active'] = false;
  $this('config')->set('packages', $packages);
  $response->setError(false);
});

/**
 * Renders package disable screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/package/uninstall/:index', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $index = $data['index'];
  $packages = $this('config')->get('packages');

  if (!isset($packages[$index])) {
    $response->setError(true, 'Invalid Package ID');
    return $this('admin')->invalid($response);
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate(
      'Uninstall %s',
      $packages[$index]['name']
    );
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to uninstall %s ? This cannot be undone.',
      $packages[$index]['name']
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf('/admin/spa/package/uninstall/%s/confirmed', $index);
  }
  //after submit what then?
  if (!isset($data['after'])) {
    $data['after'] = 'reload';
  }

  //----------------------------//
  // 2. Render Template
  $template = dirname(__DIR__) . '/template/package';
  if (is_dir($response->getPage('template_root'))) {
    $template = $response->getPage('template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->renderFromFolder('confirm', $data);

  //set content
  $response->setContent($body);
});

/**
 * Process the package disable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/package/uninstall/:index/confirmed', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $index = $request->getStage('index');
  $packages = $this('config')->get('packages');

  if (!isset($packages[$index])) {
    return $response->setError(true, 'Invalid Package ID');
  }

  //----------------------------//
  // 2. Process Request
  unset($packages[$index]);
  $this('config')->set('packages', array_values($packages));
  $response->setError(false);
});
