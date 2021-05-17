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
 * File Downloading
 *
 * @param Request  $request
 * @param Response $response
 */
$this('http')->get('/download/:file_id/:file_name', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //get the file
  $this('event')->emit('system-object-file-detail', $request, $response);
  //if there's an error
  if ($response->isError()) {
    //let it 404
    return $response->remove('json');
  }

  //get file data
  $file = $response->getResults();
  //get mime type
  $mime = $this('file')->getMimeFromLink($file['file_data']);
  // set file type
  $type = sprintf('%s; charset=UTF-8', $mime);

  $response
    ->addHeader('Content-Encoding', 'UTF-8')
    ->addHeader('Content-Type', $type);

  // force download?
  if ($request->hasStage('force')) {
    // let the browser download the file
    $response->addHeader('Content-Disposition', 'attachment; filename=' . $file['file_name']);
  } else {
    // this is just a preview
    $response->addHeader('Content-Disposition', 'inline; filename=' . $file['file_name']);
  }

  // load the file
  $response->setContent(file_get_contents($file['file_data']));
});
