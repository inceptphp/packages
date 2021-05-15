<?php //-->
/**
 * This file is part of a package designed for the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use Cradle\Framework\Schema;

/**
 * Converts pages to routes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function(RequestInterface $request, ResponseInterface $response) {
  //prevent starting session in cli mode
  if (php_sapi_name() === 'cli') {
    return;
  }

  //----------------------------//
  // 1. Declare Packages
  $http = $this('http');
  $emitter = $this('event');

  //----------------------------//
  // 1. Load all the Pages
  //get page based on path
  $pages = $emitter->call('system-collection-page-search', [
    'range' => 0
  ]);

  foreach ($pages['rows'] as $page) {
    $route = '/' . trim($page['page_path']);

    //----------------------------//
    // 2. Set SEO Meta Data
    //make sure this is the first route
    //to allow programmatic routes to override
    $http->get($route, function($request, $response) use ($page) {
      $response->set('page', 'title', $page['page_title']);

      if ($page['page_summary']) {
        $response->set('page', 'meta', 'description', $page['page_summary']);
      }

      if (!empty($page['page_tags'])) {
        $response->set('page', 'meta', 'keywords', implode(',', $page['page_tags']));
      }

      if ($page['page_image']) {
        $response->set('page', 'meta', 'image', $page['page_image']);
      }
    }, 100);

    //----------------------------//
    // 3. Load the Page Content
    //make sure this is the last route
    $http->get($route, function($request, $response) use ($page) {
      //if there is already content
      if ($response->hasContent()) {
        //do nothing else
        return;
      }

      //trigger page event
      if (trim($page['page_event'])) {
        //make a payload
        $payload = $this->makePayload();
        if ($page['page_parameters']) {
          $payload['request']->setStage($page['page_parameters']);
        }

        $this('event')->emit(
          $page['page_event'],
          $payload['request'],
          $payload['response']
        );

        if ($payload['response']->isError()) {
          $response->setSession('flash', [
            'message' => $payload['response']->getMessage(),
            'type' => 'error'
          ]);
          $page['errors'] = $payload['response']->getValidation();
        }

        if ($payload['response']->hasResults()) {
          $page['results'] = $payload['response']->getResults();
        }
      }

      //if there's a template
      if (trim($page['page_template'])) {
        //prepare page content
        $data = ['page' => $page];

        if ($request->hasStage()) {
          $data = array_merge($data, $request->getStage());
        }

        if ($response->hasResults()) {
          $data = array_merge($data, $response->getResults());
        }

        if (trim($page['page_content_type'])) {
          $response->addHeader('Content-Type', $page['page_content_type']);
        }

        //set page body
        $template = $this('handlebars')->compile($page['page_template']);
        $response->setContent($template($data));
      }

      if (!trim($page['page_layout'])) {
        return;
      }

      $this('event')->emit('render-' . $page['page_layout'], $request, $response);
    }, -100);
  }
};
