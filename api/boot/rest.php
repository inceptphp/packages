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
 * Registers REST routes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function(RequestInterface $request, ResponseInterface $response) {
  //declare pages
  $http = $this('http');
  $emitter = $this('event');

  $path = $request->getPath('string');
  //normalize the pat
  if (substr($path, -1) !== '/') {
    $path .= '/';
  }

  //if the path is not a /rest/ path or it's /rest/access
  if (strpos($path, '/rest/') !== 0 || $path === '/rest/access/') {
    //this is not for testing
    return;
  }

  //check permissions
  if ($request->hasStage('client_id')) {
    $emitter->emit('rest-source-app-detail', $request, $response);
  } else {
    $emitter->emit('rest-source-session-detail', $request, $response);
  }

  //save the response as source
  if ($response->hasResults()) {
    $request->set('source', $response->getResults());
  }

  //get all the routes
  $emitter->emit('rest-route-search', $request, $response);

  //loop through the routes
  foreach ($response->getResults('rows') as $rest) {
    //format the method and path
    $path = '/rest' . $rest['rest_path'];
    $method = strtolower($rest['rest_method']);

    //and manually add the route
    $http->route($method, $path, function(
      RequestInterface $request,
      ResponseInterface $response
    ) use ($rest) {
      //add the rest parameters to stage
      if (is_array($rest['rest_parameters'])) {
        foreach ($rest['rest_parameters'] as $key => $value) {
          $request->setStage($key, $value);
        }
      }

      //if the call is a user call
      if ($rest['rest_type'] === 'user') {
        $request
          ->setStage('profile_id', $request->get('source', 'profile_id'))
          ->setStage('filter', 'profile_id', $request->get('source', 'profile_id'));
      }

      //now trigger the event
      $this('event')->emit($rest['rest_event'], $request, $response);

      //remove client_id, client_secret
      $response
        ->removeResults('client_id')
        ->removeResults('client_secret');
    });
  }

  //undo the results
  $response->remove('json');

  //set the content type
  $response->addHeader('Content-Type', 'text/json');
};
