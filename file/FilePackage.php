<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\File;

use Incept\Package\PackageTrait;
use Incept\Framework\Framework;

/**
 * File package methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class FilePackage
{
  use PackageTrait;

  /**
   * @const string DEFAULT_MIME
   */
  const DEFAULT_MIME = 'application/octet-stream';

  /**
   * @const string DEFAULT_EXTENSION
   */
  const DEFAULT_EXTENSION = 'unknown';

  /**
   * @var array $extensions static list of mime to extensions
   */
  public static $extensions = [];

  /**
   * @var *PackageHandler $handler
   */
  protected $handler;

  /**
   * @var *string $upload
   */
  protected $upload;

  /**
   * @var *string $uri
   */
  protected $uri = '/';

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
   * Uploads base64 based data and
   * saves it to the upload folder
   *
   * @param *string $data
   * @param *string $destination
   * @param string|null $host
   *
   * @return string
   */
  public function upload(
    string|array $data,
    string $path = null
  ): string|array {
    if (is_array($data)) {
      foreach ($data as $key => $value) {
        $data[$key] = $this->upload($value, $path);
      }

      return $data;
    }

    //if not base 64
    if (strpos($data, ';base64,') === false) {
      //we don't need to convert
      return $data;
    }

    //make the destination
    if ($path && strpos($path, '/') !== 0) {
      $path = '/' . $path;
    }

    $extension = $this->getExtensionFromData($data);
    $file = sprintf('/%s.%s', md5(uniqid()), $extension);

    //if not folder
    if (!is_dir($this->folder . $path)) {
      //make one
      mkdir($this->folder . $path, 077);
    }

    //data:mime;base64,data
    $base64 = substr($data, strpos($data, ',') + 1);
    file_put_contents($this->folder . $path . $file, base64_decode($base64));

    $host = null;
    if (isset($_SERVER['HTTP_HOST'])) {
      $host = $this->handler->package('host')->name();
    }

    return $host . $this->uri . $path . $file;
  }

  /**
   * Determine the Extension from data
   *
   * @param string
   * @return string
   */
  public function getExtensionFromData($data)
  {
    $extension = static::DEFAULT_EXTENSION;

    $mime = $this->getMimeFromData($data);
    if (isset(self::$extensions[$mime])) {
      $extension = self::$extensions[$mime];
    }

    return $extension;
  }

  /**
   * Determine the Extension from a link
   *
   * @param string
   * @return string
   */
  public function getExtensionFromLink($link)
  {
    $extension = static::DEFAULT_EXTENSION;

    $path = explode('/', $link);
    $file = array_pop($path);

    if (strpos($file, '.') !== false) {
      $file = explode('.', $file);
      $extension = array_pop($file);
    }

    return $extension;
  }

  /**
   * Determine the Mime from data
   *
   * @param string
   * @return string
   */
  public function getMimeFromData($data)
  {
    $data = urldecode($data);
    //data:mime;base64,data
    $data = substr($data, 5);

    $chunks = explode(';base64,', $data);
    return array_shift($chunks);
  }

  /**
   * Determine the Extension from a link
   *
   * @param string
   * @return string
   */
  public function getMimeFromLink($link)
  {
    $mime  = static::DEFAULT_MIME;
    $extension = $this->getExtensionFromLink($link);

    //find out the extension
    foreach (self::$extensions as $key => $value) {
      if ($extension === $value) {
        $mime = $key;
        break;
      }
    }

    return $mime;
  }

  /**
   * Returns the upload folder
   *
   * @return string
   */
  public function getUploadFolder(): string
  {
    return $this->folder;
  }

  /**
   * Returns the URI path
   *
   * @return string
   */
  public function getUriPath(): string
  {
    return $this->uri;
  }

  /**
   * Sets the upload folder
   *
   * @param *string $upload
   *
   * @return FilePackage
   */
  public function setUploadFolder(string $folder): FilePackage
  {
    $this->folder = $folder;
    return $this;
  }

  /**
   * Sets the upload folder
   *
   * @param *string $uri
   *
   * @return FilePackage
   */
  public function setUriPath(string $uri): FilePackage
  {
    $this->uri = $uri;
    return $this;
  }
}
