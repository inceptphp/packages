<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;


use Incept\Http\Request;
use Incept\Framework\Schema;
use Incept\Framework\SystemException;

/**
 * Registers webhooks routes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
return function (RequestInterface $request, ResponseInterface $response) {
  //declare packages
  $config = $this('config');
  $api = $this->package('inceptphp/packages/api');

  try { //first test for webhook
    $schema = Schema::load('webhook');
  } catch (Exception $e) {
    //if no webhook schema
    return;
  }

  //before we do any webhook processing,
  if ($config->get('settings', 'disable_webhooks')) {
    //lets do the responsible thing
    return;
  }

  //if we are in CLI mode
  if (php_sapi_name() === 'cli') {
    $organization = $request->getStage('org_id');
  } else {
    //try to get it from the path
    $key = $request->get('path', 'array', 1);
    $id = $request->get('path', 'array', 2);
    if ($key === 'org' && is_numeric($id)) {
      $organization = $id;
    }

    if (!isset($organization)) {
      $organization = $request->getSession('me', 'organization', 'id');
    }
  }

  //if we can't find an org
  if (!$organization) {
    //we cant do anything else
    return;
  }

  //get all webhooks
  //WARNING: Too many webhooks will slow down the system
  $webhooks = $emitter->call('webhook-valid-search', [
    'org_id' => $organization
  ])['rows'];

  //if no webhooks no need to continue
  if (empty($webhooks)) {
    return;
  }

  foreach ($webhooks as $webhook) {
    $handler = function($request, $response) use ($webhook) {
      if ($response->isError() || $request->hasStage('no_webhooks')) {
        return;
      }

      //if the parameters dont exist in the stage
      if(is_array($webhook['webhook_parameters'])
        && !$api->validStage($webhook['webhook_parameters'], $request->getStage())
      ) {
        //stop
        return;
      }

      $results = $response->getResults();

      $bulk = [];
      //now we need to call the calls
      foreach ($webhook['calls'] as $call) {
        //is it a user webhook?
        if ($webhook['webhook_type'] === 'user'
          && (//and
            //if there is no profile id
            !isset($results['profile_id'])
            //or the profile ids dont match
            || $results['profile_id'] !== $call['profile']
          )
        ) {
          //dont call the webhook
          continue;
        }

        //setup the payload
        $bulk[] = [
          'url' => $call['url'],
          'action' => $webhook['webhook_action'],
          'method' => $webhook['webhook_method'],
          'results' => $results
        ];
      }

      //if nothing
      if (empty($bulk)) {
        return;
      }

      $bulk[0] = 'webhook-call-bulk';
      $emitter->call('queue', $bulk, $response);
      //if we werent able to queue
      if ($response->isError()) {
        $data = $request->getStage();
        //send manually after the connection
        $this->postprocess(function ($request, $response) use ($bulk) {
          $this('event')->call('webhook-call-bulk', $bulk, $response);
        });
      }
    };

    $emitter->on($webhook['webhook_event'], $handler, -100000);
  }
};
