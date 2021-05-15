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
 * Sets up the data amd SEO for a post detail
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/post/detail/:post_slug', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //firgure out the redirect
  $redirect = $request->getStage('redirect_uri')
    ?? $this('config')->get('settings', 'home');

  //setup the data needed for getting the post
  $payload = $this->makePayload();

  //get the post
  $this('event')->emit(
    'system-object-post-detail',
    $payload['request'],
    $payload['response']
  );

  //if there's an error, redirect
  if ($payload['response']->isError()) {
    $response->setSession('flash', [
      'message' => $payload['response']->getMessage(),
      'type' => 'error'
    ]);

    return $this('http')->redirect($redirect);
  }

  //get the sub results
  $results = $payload['response']->getResults();

  //if the post is not approved
  if ($results['post_status'] !== 'approved') {
    $response->setSession('flash', [
      'message' => 'Post is still in draft.',
      'type' => 'error'
    ]);

    return $this('http')->redirect($redirect);
  }

  //if the post is not published yet
  if (!$results['post_published']
    || time() < strtotime($results['post_published'])
  ) {
    $response->setSession('flash', [
      'message' => 'Post is not published yet.',
      'type' => 'error'
    ]);

    return $this('http')->redirect($redirect);
  }

  //set the results to the global response
  $response->setResults('post', $results);

  //Soft set the SEO
  $response
    ->set('page', 'title', $results['post_title'])
    ->set('page', 'class', 'page-post');

  if ($results['post_summary']) {
    $response->set('page', 'meta', 'description', $results['post_summary']);
  }

  if (!empty($results['post_tags'])) {
    $response->set('page', 'meta', 'keywords', implode(',', $results['post_tags']));
  }

  if ($results['post_banner']) {
    $response->set('page', 'meta', 'image', $results['post_banner']);
  }

  //the page object should handle the rest
});

/**
 * Sets up the data amd SEO for a post category detail
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->get('/post/category/:category_slug', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  $redirect = $request->getStage('redirect_uri')
    ?? $this('config')->get('settings', 'home');

  //setup the data needed for getting the post
  $payload = $this->makePayload();

  //get the post
  $this('event')->emit(
    'system-object-category-detail',
    $payload['request'],
    $payload['response']
  );

  //if there's an error, redirect
  if ($payload['response']->isError()) {
    $response->setSession('flash', [
      'message' => $payload['response']->getMessage(),
      'type' => 'error'
    ]);

    return $this('http')->redirect($redirect);
  }

  //get the sub results
  $results = $payload['response']->getResults();

  $response->set('page', 'title', $results['category_title']);

  if ($results['category_summary']) {
    $response->set('page', 'meta', 'description', $results['category_summary']);
  }

  if (!empty($results['category_tags'])) {
    $response->set('page', 'meta', 'keywords', implode(',', $results['category_tags']));
  }

  if ($results['category_banner']) {
    $response->set('page', 'meta', 'image', $results['category_banner']);
  }

  //the page object should handle the rest
});

/**
 * Process Add Comment
 *
 * @param RequestInterface $request
 * @param ResponseInterface $response
 */
$this('http')->post('/post/detail/:post_slug', function(
  RequestInterface $request,
  ResponseInterface $response
) {
  //get the slug
  $postSlug = $request->getStage('post_slug');
  //setup the routing path
  $route = sprintf('/post/detail/%s', $postSlug);

  //make sure this is a valid post
  $this('event')->emit('system-object-post-detail', $request, $response);

  //if there's an error
  if ($response->isError()) {
    $response->setError(true, $response->getMessage());
    return $this->routeTo('get', $route, $request, $response);
  }

  //get the post id
  $postId = $response->getResults('post_id');

  //get the session profile id
  $profileId = $request->getSession('me', 'profile_id');

  if (!$profileId) {
    if (!$request->hasPost('profile_name')) {
      $response->setError(true, 'Must be logged in to comment.');
      // go back to GET /article/:article_id route
      return $this('http')->routeTo('get', $route, $request, $response);
    }

    $payload = $this->makePayload();
    $profile = $this('event')->call(
      'system-object-profile-create',
      $payload['request'],
      $payload['response']
    );

    if ($payload['response']->isError()) {
      $response->setSession('flash', [
        'message' => $payload['response']->getMessage(),
        'type' => 'error'
      ]);

      return $this('http')->redirect($redirect);
    }

    $profileId = $profile['profile_id'];
  }

  //create the comment
  $request
    ->setStage('profile_id', $profileId)
    ->setStage('post_id', $postId);

  $this('event')->emit('system-object-comment-create', $request, $response);

  //if there was an error creating the comment
  if ($response->isError()) {
    // go back to GET /article/:article_id route
    return $this('http')->routeTo('get', $route, $request, $response);
  }

  //if there was an error linking the article to the comment
  if ($response->isError()) {
    // go back to GET /article/:article_id route
    return $this('http')->routeTo('get', $route, $request, $response);
  }

  //it was good
  //add a happy message
  $response->setSession('flash', [
    'message' => 'Comment Added',
    'type' => 'success'
  ]);

  //redirect to /article/:article_id
  $this('http')->redirect($route);
});
