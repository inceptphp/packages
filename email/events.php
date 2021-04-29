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
 * Send Mail Job
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('event')->on('email-send', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if error, or no driver
  if ($response->isError() || !class_exists('Swift_SmtpTransport')) {
    //do nothing
    return;
  }

  //get the config
  $config = $this('config')->get('services', 'email-main');
  //if no config
  if (!$config || (isset($config['active']) && !$config['active'])) {
    //do nothing
    return;
  }

  $from = [];
  $from[$config['user']] = $config['name'];

  $to = $request->getStage('to');
  if (!$to) {
    $response->invalidate('to', 'To is required');
  }

  $subject = $request->getStage('subject');
  if (!$subject) {
    $response->invalidate('subject', 'Subject is required');
  }

  $text = $request->getStage('text');
  if (!$text) {
    $response->invalidate('text', 'Text is required');
  }

  $cc = $request->getStage('cc') ?? [];
  $bcc = $request->getStage('bcc') ?? [];
  $html = $request->getStage('html');
  $attachments = $request->getStage('attachments') ?? [];

  if (!is_array($attachments)) {
    $attachments = [$attachments];
  }

  foreach (['to', 'cc', 'bcc'] as $recipients) {
    if (!is_array($$recipients)) {
      $$recipients = [$$recipients];
    }

    //from: [[name => John Doe, 'address' => john@doe.com], jane@doe.com]
    //to: [john@doe.com => John Doe, jane@doe.com => null]
    foreach ($$recipients as $i => $recipient) {
      if (is_string($recipient)) {
        $recipient = ['name' => null, 'address' => $recipient];
      }

      unset($$recipients[$i]);
      $$recipients[$recipient['address']] = $recipient['name'];
    }
  }

  //send mail
  $message = new Swift_Message($subject);
  $message->setFrom($from);
  $message->setTo($to);

  if (!empty($cc)) {
    $message->setCc($cc);
  }

  if (!empty($bcc)) {
    $message->setBcc($bcc);
  }

  if ($html) {
    $message->addPart($text, 'text/plain');
    $message->setBody($html, 'text/html');
  } else {
    $message->setBody($text, 'text/plain');
  }

  foreach($attachments as $attachment) {
    $message->attach(Swift_Attachment::fromPath($attachment));
  }

  $transport = new Swift_SmtpTransport($config['host'], $config['port']);
  $transport->setEncryption($config['type']);
  $transport->setUsername($config['user']);
  $transport->setPassword($config['pass']);

  $swift = new Swift_Mailer($transport);
  if (!$swift->send($message, $failures)) {
    return $response
      ->setError(true, 'Did not send all emails')
      ->invalidate('email', $failures);
  }

  $response->setError(false);
});

/**
 * Send OTP
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this->on('email-otp-send', function (
  RequestInterface $request,
  ResponseInterface $response
) {
  //if error
  if ($response->isError()) {
    //do nothing
    return;
  }

  //if no to
  if (!$request->getStage('to')) {
    //send back an error
    return $response
      ->setError(true, 'Invalid parameters')
      ->invalidate('to', 'To is required');
  }

  //determine name
  $name = $this('config')->get('settings', 'name') ?? 'Incept';
  //determine pin code
  $pin = rand(1111, 9999);
  //set the subject
  $subject = $this('lang')->translate('Email Verification from %s', $name);
  //set the body
  $body = $this('lang')->translate('Verification Code is %s', $pin);

  //set event
  $request->setStage(0, 'email-send');
  //set subject
  $request->setStage('subject', $subject);
  //set the text
  $request->setStage('text', $body);
  //set the html
  $request->setStage('html', $body);

  //----------------------------//
  // 3. Process Data
  $this('event')->emit('queue', $request, $response);

  //if we werent able to queue
  if ($response->isError()) {
    $data = $request->getStage();
    //send manually after the connection
    $this->postprocess(function ($request, $response) use ($data) {
      $this('event')->call('email-send', $data, $res);
    });
  }

  //set the otp pin
  $response
    ->setError(false)
    ->setResults('otp', $pin);
});
