<?php //-->

/**
 * File Upload (supporting job)
 *
 * @param Request  $request
 * @param Response $response
 */
$this('event')->on('file-upload', function ($request, $response) {
  //get data
  $data = $request->getStage('data');
  $path = $request->getStage('path') ?? null;

  $data = $this('file')->upload($data, $path);

  $response->setError(false)->setResults([
    'data' => $data
  ]);
});
