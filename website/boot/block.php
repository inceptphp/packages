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
 * Considers use of blocks as a handlebars helper
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function(RequestInterface $request, ResponseInterface $response) {
  //prevent starting session in cli mode
  if (php_sapi_name() === 'cli') {
    return;
  }

  $handler = $this;

  //create helper
  $this('handlebars')->registerHelper('block', function($keyword) use ($handler) {
    $block = $handler('event')->call('system-object-block-detail', [
      'block_keyword' => $keyword
    ]);

    if (!$block) {
      return '';
    }

    $payload = $handler->makePayload();
    if (trim($block['block_event'])) {
      if ($block['block_parameters']) {
        $payload['request']->setStage($block['block_parameters']);
      }

      $handler('event')->emit(
        $block['block_event'],
        $payload['request'],
        $payload['response']
      );
    }

    $data = [];
    if ($payload['request']->hasStage()) {
      $data = array_merge($data, $payload['request']->getStage());
    }

    if ($payload['response']->hasResults()) {
      $data = array_merge($data, $payload['response']->getResults());
    }

    return $handler('handlebars')->compile($block['block_template'])($data);
  });
};
