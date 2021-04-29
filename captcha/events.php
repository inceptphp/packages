<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

use UGComponents\Curl\CurlHandler;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

/**
 * Loads captcha token in stage
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('captcha-load', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $config = $this('config')->get('services', 'captcha-main');

  //if no config
  if (!$config
    || !isset($config['token'], $config['secret'], $config['host'])
    || !trim($config['token'])
    || !trim($config['secret'])
    || !trim($config['host'])
    || $config['host'] === '<GOOGLE CAPTCHA HOST>'
    || $config['token'] === '<GOOGLE CAPTCHA TOKEN>'
    || $config['secret'] === '<GOOGLE CAPTCHA SECRET>'
  ) {
    //let it pass
    return;
  }

  //render the key
  $key = $config['token'];
  $response->setResults('captcha', $key);
});

/**
 * Validates Captcha
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('captcha-validate', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  $actual = $request->getStage('g-recaptcha-response');
  $config = $this('config')->get('services', 'captcha-main');

  //if no config
  if (!$config
    || !isset($config['token'], $config['secret'], $config['host'])
    || !trim($config['token'])
    || !trim($config['secret'])
    || !trim($config['host'])
    || $config['host'] === '<GOOGLE CAPTCHA HOST>'
    || $config['token'] === '<GOOGLE CAPTCHA TOKEN>'
    || $config['secret'] === '<GOOGLE CAPTCHA SECRET>'
  ) {
    //let it pass
    return;
  }

  $result = CurlHandler::i()
    ->setUrl('https://www.google.com/recaptcha/api/siteverify')
    ->verifyHost(false)
    ->verifyPeer(false)
    ->setPostFields(http_build_query(array(
      'secret' => $config['secret'],
      'response' => $actual
    )))
    ->getJsonResponse();

  if (!isset($result['success']) || !$result['success']) {
    //prepare to error
    $message = $this('lang')->translate('Captcha Failed');
    $response->setError(true, $message);
  }

  //it passed
});
