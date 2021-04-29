<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Role;

use Incept\Package\Admin\AdminPackage;

use Incept\Package\PackageTrait;
use Incept\Framework\Framework;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use Throwable;

/**
 * Role package methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class RolePackage extends AdminPackage
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
   * Error Processor
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param *Throwable          $error
   *
   * @return mixed
   */
  public function error(
    RequestInterface $request,
    ResponseInterface $response,
    Throwable $error
  ) {
    // if an exception was thrown from the role package
    if (!($error instanceof RoleException)) {
      return;
    }

    //prevent starting session in cli mode
    if (php_sapi_name() === 'cli') {
      return;
    }

    // set default redirect
    $redirect = $this->handler->package('config')->get('settings', 'home') ?? '/';

    // let them know
    //add a flash
    $response->setSession('flash', [
      'message' => $error->getMessage(),
      'type' => 'error'
    ]);

    // redirect
    return $this->handler->package('http')->redirect($redirect);
  }
}
