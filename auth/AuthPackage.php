<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Auth;

use Incept\Package\PackageTrait;
use Incept\Framework\Framework;

use UGComponents\IO\Request\RequestInterface;

/**
 * Auth package methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class AuthPackage
{
  use PackageTrait;

  /**
   * @var *PackageHandler $handler
   */
  protected $handler;

  /**
   * Add handler for scope when routing
   *
   * @param *PackageHandler $handler
   */
  public function __construct(Framework $handler)
  {
    $this->handler = $handler;
  }

  /**
  * A helper to manage login attempts
  *
  * @param RequestInterface $request
  *
  * @return array
  */
  public function addAttempt(RequestInterface $request): array
  {
    $attempts = $this->getAttempts($request);
    array_unshift($attempts, time());
    $request->setSession('auth_attempts', $attempts);
    return $attempts;
  }

  /**
  * A helper to manage login attempts
  *
  * @param RequestInterface $request
  *
  * @return AuthPackage
  */
  public function clearAttempts(RequestInterface $request): AuthPackage
  {
    $request->removeSession('auth_attempts');
    return $this;
  }

  /**
  * Returns how long someone should wait before logging in again
  *
  * @return array
  */
  public function config(): array
  {
    $config = $this->handler->package('config')->get('auth', 'submission');

    if (!is_array($config)) {
      $config = [];
    }

    if (!isset($config['captcha'])) {
      $config['captcha'] = 2;
    }

    if (!isset($config['lockout'])) {
      $config['lockout'] = 5;
    }

    if (!isset($config['wait'])) {
      $config['wait'] = 5;
    }

    return $config;
  }

  /**
  * A helper to manage login attempts
  *
  * @param RequestInterface $request
  *
  * @return array
  */
  public function getAttempts(RequestInterface $request): array
  {
    $attempts = $request->getSession('auth_attempts');

    if (!is_array($attempts)) {
      $attempts = [];
    }

    return $attempts;
  }

  /**
   * Transform a title to a URL slug
   *
   * @param *string $string
   * @param string  $string
   *
   * @return string
   */
  public function slugger(string $string, string $suffix = null): string
  {
    // replace space with +
    $slug = preg_replace('/\s+/is', '+', $string);

    // replace non letter or digits by -
    $slug = preg_replace('~[^\pL\d\+]+~u', '-', $slug);

    // transliterate
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);

    // remove unwanted characters
    $slug = preg_replace('~[^-\w\+]+~', '', $slug);

    // trim
    $slug = trim($slug, '-');

    // remove duplicate -
    $slug = preg_replace('~-+~', '-', $slug);

    if (strlen($slug) > 100) {
      $slug = substr($slug, 0, 100);
    }

    if (is_string($suffix)) {
      return strtolower($slug . '-' . $suffix);
    }

    // lowercase
    return strtolower($slug);
  }

  /**
  * Returns how long someone should wait before logging in again
  *
  * @param RequestInterface $request
  *
  * @return int
  */
  public function waitFor(RequestInterface $request): int
  {
    $config = $this->config();
    $attempts = $this->getAttempts($request);

    //allow a few attempts
    if (count($attempts) < $config['lockout']) {
      return 0;
    }

    $wait = ($attempts[0] +  (60 * $config['wait'])) - time();

    if ($wait < 0) {
      $wait = 0;
    }

    return $wait;
  }
}
