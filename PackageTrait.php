<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package;

use Incept\Framework\Framework;

/**
 * General Package Methods
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
trait PackageTrait
{
  /**
   * Performs an install
   *
   * @param *string      $path
   * @param ?string      $current
   * @param ?string|null $type
   *
   * @return string The current version
   */
  public function install(
    string $path,
    string $current = '0.0.0',
    string $type = null
  ): string {
    //collect and organize all the versions
    $versions = [];
    $files = scandir($path);
    foreach ($files as $file) {
      if ($file === '.' || $file === '..' || is_dir($path . '/' . $file)) {
        continue;
      }

      //get extension
      $extension = pathinfo($file, PATHINFO_EXTENSION);

      //valid extensions
      if (!in_array($extension, ['php', 'sh', 'sql'])) {
        continue;
      }

      //only run updates on a following type
      if ($type && $type !== $extension) {
        continue;
      }

      //get base as version
      $version = pathinfo($file, PATHINFO_FILENAME);

      //validate version
      if (!preg_match('/^[0-9\.]+$/', $version)
        || version_compare($version, '0.0.1', '<')
      ) {
        continue;
      }

      $versions[$version][] = [
        'script' => $path . '/' . $file,
        'mode' => $extension
      ];
    }

    if (empty($versions)) {
      return '0.0.0';
    }

    //sort versions
    uksort($versions, 'version_compare');

    //prepare incase
    $key = $this->handler->package('config')->get('settings', 'pdo');
    $database = $this->handler->package('pdo')->get($key);

    //now run the scripts in order of version
    foreach ($versions as $version => $files) {
      //if 0.0.0 >= 0.0.1
      if (version_compare($current, $version, '>=')) {
        continue;
      }

      //run the scripts
      foreach ($files as $file) {
        switch ($file['mode']) {
          case 'php':
            include $file['script'];
            break;
          case 'sql':
            $query = file_get_contents($file['script']);
            $database->query($query);
            break;
          case 'sh':
            exec($file['script']);
            break;
        }
      }
    }

    //if 0.0.0 < 0.0.1
    if (version_compare($current, $version, '<')) {
      $current = $version;
    }

    return $current;
  }

  /**
   * Either returns the current available version
   * or the next version
   *
   * @param *string $path
   * @param bool    $next
   *
   * @return string
   */
  public function version(string $path, bool $next = false): string
  {
    //collect and organize all the versions
    $versions = [];
    $files = scandir($path, 0);
    foreach ($files as $file) {
      if ($file === '.'
        || $file === '..'
        || is_dir($path . '/' . $file)
      ) {
        continue;
      }

      //get extension
      $extension = pathinfo($file, PATHINFO_EXTENSION);

      if ($extension !== 'php'
        && $extension !== 'sh'
        && $extension !== 'sql'
      ) {
        continue;
      }

      //get base as version
      $version = pathinfo($file, PATHINFO_FILENAME);

      //validate version
      if (!preg_match('/^[0-9\.]+$/', $version)
        || version_compare($version, '0.0.1', '<')
      ) {
        continue;
      }

      $versions[] = $version;
    }

    if (empty($versions)) {
      return '0.0.0';
    }

    //sort versions
    usort($versions, 'version_compare');

    $version = array_pop($versions);

    if (!$next) {
      return $version;
    }

    $revisions = explode('.', $version);
    $revisions = array_reverse($revisions);

    $found = false;
    foreach ($revisions as $i => $revision) {
      if (!is_numeric($revision)) {
        continue;
      }

      $revisions[$i]++;
      $found = true;
      break;
    }

    if (!$found) {
      return $current . '.1';
    }

    $revisions = array_reverse($revisions);
    return implode('.', $revisions);
  }
}
