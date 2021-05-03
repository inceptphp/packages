<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;
use UGComponents\Curl\CurlHandler;

use Incept\Framework\Schema;

use Storm\SqlException;
use Storm\SqlFactory;

/**
 * Gets all the rest calls given the source scopes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this->on('webhook-valid-search', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $global = $this->package('global');
  $results = [];

  $resource = $database = SqlFactory::load($this('config')->get('sql-main'));

  //if there is no resource found
  if (!$resource) {
    return $response->setResults(['rows' => [], 'total' => 0]);
  }

  try {
    //try to only get the webhooks that are being
    //listened to and have valid webhook URLS
    $rows = $resource
      ->search('app_webhook')
      ->innerJoinUsing('app', 'app_id')
      ->innerJoinUsing('webhook', 'webhook_id')
      ->innerJoinUsing('app_profile', 'app_id')
      ->addFilter('app_webhook IS NOT NULL AND app_webhook !=\'\'')
      ->filterByAppActive(1)
      ->filterByWebhookActive(1)
      ->getRows();
  //if there's an SQL error, then ignore
  } catch (SqlException $e) {
    return $response->setResults(['rows' => [], 'total' => 0]);
  }

  foreach ($rows as $row) {
    $row['webhook_parameters'] = json_decode(
      $row['webhook_parameters'],
      true
    );

    if (!is_array($row['webhook_parameters'])) {
      $row['webhook_parameters'] = [];
    }

    $id = $row['webhook_id'];

    //add the webhook
    if (!isset($results[$id])) {
      $results[$id] = $row;
      foreach ($results[$id] as $key => $value) {
        if (strpos($key, 'app_') === 0
          || strpos($key, 'profile_') === 0
        ) {
          unset($results[$id][$key]);
        }
      }
    }

    //add to app
    $results[$id]['calls'][$row['app_id']] = [
      'url' => $row['app_webhook'],
      'profile' => $row['profile_id']
    ];
  }

  //clean up results
  $results = array_values($results);
  foreach ($results as $i => $webhook) {
    $calls = [];
    //this logic is to reduce the minimum calls performed
    foreach ($results[$i]['calls'] as $call) {
      //by default unique by url
      $id = $call['url'];
      //if this webhook is a user type
      if ($webhook['webhook_type'] === 'user') {
        //unique by url + profile
        $id = $call['profile'] . $call['url'];
      }

      $calls[$id] = $call;
    }

    $results[$i]['calls'] = array_values($calls);
  }

  $response->setResults([
    'rows' => $results,
    'total' => count($results)
  ]);
});

/**
 * Gets all the rest calls given the source scopes
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this->on('webhook-call', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $url = $request->getStage('url');
  $action = $request->getStage('action');
  $method = $request->getStage('method');
  $results = $request->getStage('results');

  $payload = [
    'action' => $action,
    'data' => $results
  ];

  CurlHandler::i()
    ->setUrl($url)
    ->when(
      strpos($url, 'https') === 0,
      function () {
        $this
          ->verifyPeer(false)
          ->verifyHost(false);
      }
    )
    ->setCustomRequest(strtoupper($method))
    ->when(
      $method === 'get' || $method === 'delete',
      function () use (&$url, &$payload) {
        $query = http_build_query($payload);
        $separator = '?';
        if (strpos($url, '?') !== false) {
          $separator = '&';
        }

        $this->setUrl($url . $separator . $query);

      },
      //else (post or put)
      function () use (&$payload) {
        $this->setPostFields($payload);
      }
    )
    ->getResponse();
});

/**
 * Bulk webhook call
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this->on('webhook-call-bulk', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $data  = $request->getStage();
  // call webhook 1 by 1
  foreach ($data as $webhook) {
    $this('event')->call('webhook-call', $webhook);
  }
});
