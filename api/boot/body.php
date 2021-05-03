<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use HTTPQuest\HTTPQuest;
use HTTPQuest\HTTPQuestOptions;
use HTTPQuest\Requests;
use HTTPQuest\ContentTypes;

/**
 * Fixes PHP PUT
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function(RequestInterface $request, ResponseInterface $response) {
  if (!trim($request->get('body'))) {
    return;
  }

  $HTTPQuest = new HTTPQuest();
  $data = [];
  $HTTPQuest->decode($data, $_FILES);

  if (empty($data)) {
    return;
  }

  $request->set('put', $data)->setStage($data);
};
