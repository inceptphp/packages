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
 * Forwards the supplier styles to the DMZ
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/styles/docs.css', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //get the asset path
  $assetPath = dirname(__DIR__) . '/assets';
  //set content type
  $response->addHeader('Content-Type', 'text/css');
  //get the file and set to content
  $response->setContent(file_get_contents($assetPath . '/docs.css'));
});
