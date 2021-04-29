<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Loads up the default settings
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function (RequestInterface $request, ResponseInterface $response) {
  //get the name of the main database
  $key = $this('config')->get('settings', 'pdo');
  if (!$key) {
    return;
  }

  $config = $this('config')->get('services', $key);
  if (!$config) {
    return;
  }

  //setup PDO
  $this('pdo')->register($key, $config);
  //setup storm and make this the default database
  $this('storm')->load($key);
};
