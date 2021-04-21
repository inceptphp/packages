<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Handlebars;

use Exception;

/**
 * Handlebars exceptions
 *
 * @package  Incept
 * @category Package
 * @standard PSR-2
 */
class HandlebarsException extends Exception
{
  /**
   * @const ERROR_FILE_NOT_FOUND Error template
   */
  const ERROR_FILE_NOT_FOUND = 'File %s was not found';

  /**
   * @const ERROR_FOLDER_NOT_FOUND Error template
   */
  const ERROR_FOLDER_NOT_FOUND = 'Folder %s was not found';

  /**
   * @const ERROR_FOLDER_NOT_SET Error template
   */
  const ERROR_FOLDER_NOT_SET = 'Folder not set. Try $config->setTemplateFolder(string).';

  /**
   * Create a new exception for file not found
   *
   * @param *string $path
   *
   * @return HandlebarsException
   */
  public static function forFileNotFound(string $path): HandlebarsException
  {
    return new static(sprintf(static::ERROR_FILE_NOT_FOUND, $path));
  }

  /**
   * Create a new exception for folder not found
   *
   * @param *string $path
   *
   * @return HandlebarsException
   */
  public static function forFolderNotFound(string $path): HandlebarsException
  {
    return new static(sprintf(static::ERROR_FOLDER_NOT_FOUND, $path));
  }

  /**
   * Create a new exception for folder not set
   *
   * @return HandlebarsException
   */
  public static function forFolderNotSet(): HandlebarsException
  {
    return new static(sprintf(static::ERROR_FOLDER_NOT_SET));
  }
}
