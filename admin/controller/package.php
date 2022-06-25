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
  $data = $request->getStage();

  if (!isset($data['filter'])) {
    $data['filter'] = 'all';
  }

  $rows = $this('config')->get('packages');

  foreach ($rows as $name => $row) {
    if (($data['filter'] === 'default' && strpos($name, 'inceptphp/') !== 0)
      || ($data['filter'] === 'custom' && strpos($name, 'inceptphp/') === 0)
    ) {
      continue;
    }

    //get the real path
    if ($row['active']) {
      $path = $this($name)->getPackagePath();
    } else {
      $path = null;
      //if it starts with / like /foo/bar
      if (strpos($name, '/') === 0) {
        //it's a root package
        $path = INCEPT_CWD . $name;
      //if theres a slash like foo/bar
      } else if (strpos($name, '/') !== false) {
        //it's vendor package
        $path = sprintf('%s/vendor/%s', INCEPT_CWD, $name);
      }
    }

    //if path
    if ($path) {
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
        $rows[$name]['info'] = $info;

        if (isset($rows[$name]['info']['settings'])) {
          $rows[$name]['info']['open'] = strpos(
            (string) $rows[$name]['info']['settings'],
            '/admin/spa/'
          ) === 0;
        }
      }
    }

    if ($data['filter'] === 'settings'
      && !isset($rows[$name]['info']['settings'])
    ) {
      continue;
    }

    $data['rows'][$name] = $rows[$name];
  }

  //----------------------------//
  // 2. Render Template
  $data['title'] = $this('lang')->translate('Packages');

  $template = dirname(__DIR__) . '/template/package';
  if (is_dir($response->get('page', 'template_root') ?? '')) {
    $template = $response->get('page', 'template_root');
  }

  $body = $this('handlebars')
    ->setTemplateFolder($template)
    ->registerPartialFromFolder('search_head', 'html', true)
    ->registerPartialFromFolder('search_row', 'html', true)
    ->registerPartialFromFolder('search_tabs', 'html', true)
    ->renderFromFolder('search', $data);

  //set content
  $response
    ->set('page', 'title', $data['title'])
    ->set('page', 'class', 'page-admin-package-search page-admin')
    ->setContent($body);

  //if we only want the body
  if ($request->getStage('render') === 'body') {
    return;
  }

  //render page
  $this('admin')->render($request, $response);
});

/* Confirm Routes
-------------------------------- */

/**
 * Renders package disable screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/package/enable/**', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $name = implode('/', $request->get('route', 'variables'));
  $package = $this('config')->get('packages', $name);

  if (!$package) {
    $response->setError(true, 'Invalid Package ID');
    return $this('admin')->invalid($response);
  }

  //get the real path
  if ($package['active']) {
    $path = $this($name)->getPackagePath();
  } else {
    $path = null;
    //if it starts with / like /foo/bar
    if (strpos($name, '/') === 0) {
      //it's a root package
      $path = INCEPT_CWD . $name;
    //if theres a slash like foo/bar
    } else if (strpos($name, '/') !== false) {
      //it's vendor package
      $path = sprintf('%s/vendor/%s', INCEPT_CWD, $name);
    }
  }

  //determine the label
  $label = $name;

  //if path
  if ($path) {
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
      if (isset($info['name'])) {
        $label = $info['name'];
      }
    }
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Disable %s', $label);
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to enable %s ?',
      $label
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf('/admin/spa/package/enable/%s', $name);
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
 * Process the package disable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/package/enable/**', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $name = implode('/', $request->get('route', 'variables'));
  $package = $this('config')->get('packages', $name);

  if (!$package) {
    return $response->setError(true, 'Invalid Package ID');
  }

  //----------------------------//
  // 2. Process Request
  $this('config')->set('packages', $name, 'active', true);
  $response->setError(false);
});

/**
 * Renders package disable screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/package/disable/**', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $name = implode('/', $request->get('route', 'variables'));
  $package = $this('config')->get('packages', $name);

  if (!$package) {
    $response->setError(true, 'Invalid Package ID');
    return $this('admin')->invalid($response);
  }

  //get the real path
  if ($package['active']) {
    $path = $this($name)->getPackagePath();
  } else {
    $path = null;
    //if it starts with / like /foo/bar
    if (strpos($name, '/') === 0) {
      //it's a root package
      $path = INCEPT_CWD . $name;
    //if theres a slash like foo/bar
    } else if (strpos($name, '/') !== false) {
      //it's vendor package
      $path = sprintf('%s/vendor/%s', INCEPT_CWD, $name);
    }
  }

  //determine the label
  $label = $name;

  //if path
  if ($path) {
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
      if (isset($info['name'])) {
        $label = $info['name'];
      }
    }
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Disable %s', $label);
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to disable "%s"?',
      $label
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf('/admin/spa/package/disable/%s', $name);
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
 * Process the package disable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/package/disable/**', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $name = implode('/', $request->get('route', 'variables'));
  $package = $this('config')->get('packages', $name);

  if (!$package) {
    return $response->setError(true, 'Invalid Package ID');
  }

  //----------------------------//
  // 2. Process Request
  $this('config')->set('packages', $name, 'active', false);
  $response->setError(false);
});

/**
 * Renders package disable screen
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/admin/spa/package/uninstall/**', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $data = $request->getStage();

  $name = implode('/', $request->get('route', 'variables'));
  $package = $this('config')->get('packages', $name);

  if (!$package) {
    $response->setError(true, 'Invalid Package ID');
    return $this('admin')->invalid($response);
  }

  //get the real path
  if ($package['active']) {
    $path = $this($name)->getPackagePath();
  } else {
    $path = null;
    //if it starts with / like /foo/bar
    if (strpos($name, '/') === 0) {
      //it's a root package
      $path = INCEPT_CWD . $name;
    //if theres a slash like foo/bar
    } else if (strpos($name, '/') !== false) {
      //it's vendor package
      $path = sprintf('%s/vendor/%s', INCEPT_CWD, $name);
    }
  }

  //determine the label
  $label = $name;

  //if path
  if ($path) {
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
      if (isset($info['name'])) {
        $label = $info['name'];
      }
    }
  }

  //set title
  if (!isset($data['title'])) {
    $data['title'] = $this('lang')->translate('Uninstall %s', $label);
  }
  //set message
  if (!isset($data['message'])) {
    $data['message'] = $this('lang')->translate(
      'Are you sure you want to uninstall %s ? This cannot be undone.',
      $label
    );
  }
  //set the action
  if (!isset($data['action'])) {
    $data['action'] = sprintf('/admin/spa/package/uninstall/%s', $name);
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
 * Process the package disable
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/admin/spa/package/uninstall/**', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //----------------------------//
  // 1. Prepare Data
  $name = implode('/', $request->get('route', 'variables'));
  $packages = $this('config')->get('packages');

  if (!isset($packages[$name])) {
    return $response->setError(true, 'Invalid Package ID');
  }

  //----------------------------//
  // 2. Process Request
  unset($packages[$name]);
  $this('config')->set('packages', $packages);
  $response->setError(false);
});
