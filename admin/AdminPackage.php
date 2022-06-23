<?php //-->
/**
 * This file is part of the Incept Project.
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Incept\Package\Admin;

use Incept\Framework\Framework;

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

use Incept\Package\PackageTrait;

use Throwable;
use SimpleXMLElement;

/**
 * Admin Package
 *
 * @vendor   Incept
 * @package  Package
 * @standard PSR-2
 */
class AdminPackage
{
  use PackageTrait;

  /**
   * @const string ROOT_PATH
   */
  const ROOT_PATH = '/admin';

  /**
   * @const string ROOT_SPA
   */
  const ROOT_SPA = '/admin/spa';

  /**
   * @const string XML_TEMPLATE
   */
  const XML_TEMPLATE = "<?xml version=\"1.0\"?>\n<%s></%s>";

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
   * Helper to build the admin menu
   *
   * @param *array $menu
   * @param *array $host
   *
   * @return array
   */
  protected function buildMenu(array $menu, array $host): array
  {
    foreach ($menu as $i => $item) {
      if (isset($item['submenu']) && is_array($item['submenu'])) {
        $active = false;
        foreach ($item['submenu'] as $j => $subitem) {
          if (isset($subitem['path']) && $subitem['path'] === $host['path']) {
            $menu[$i]['submenu'][$j]['active'] = true;
            $active = true;
          }
        }

        if ($active) {
          $menu[$i]['active'] = true;
        }

        continue;
      }

      if ($item['path'] === $host['path']) {
        $menu[$i]['active'] = true;
      }
    }

    return $menu;
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
    //prevent starting session in cli mode
    if (php_sapi_name() === 'cli') {
      return;
    }

    //if there's already content
    if ($response->hasContent()) {
      return;
    }

    //get the path
    $path = $request->getPath('string');
    //if not an admin path
    if ($path !== static::ROOT_PATH
      && strpos((string) $path, static::ROOT_PATH . '/') !== 0
    ) {
      return;
    }

    $debug = strpos((string) $path, static::ROOT_SPA . '/') === false ? 'page': 'spa';

    //if it was a call for an actual file
    if (preg_match('/\.[a-zA-Z0-9]{1,4}$/', $path)) {
      return;
    }

    //if this is not an html page
    $type = $response->getHeaders('Content-Type');
    if (strpos((string) $type, 'html') === false) {
      //don't make it pretty
      return $this->errorDebug($request, $response, $error, $debug);
    }

    //get the code
    $code = $response->getCode();

    if ($code === 404) {
      return $this->error404($request, $response, $error);
    }

    //get config settings
    $settings = $this->handler->package('config')->get('settings');

    //if no environment
    if (!isset($settings['environment'])
      //or the environment is not live
      || $settings['environment'] !== 'live'
      //or it's not a 500 error
      || $code !== 500
    ) {
      //don't make it pretty
      return $this->errorDebug($request, $response, $error, $debug);
    }

    //okay make it pretty...
    $this->error500($request, $response, $error);

    if (!isset($settings['email'])) {
      return true;
    }

    return $this->errorEmail($request, $response, $error);
  }

  /**
   * 404 Error Processor
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param *Throwable          $error
   *
   * @return bool
   */
  protected function error404(
    RequestInterface $request,
    ResponseInterface $response,
    Throwable $error
  ): bool {
    //load some packages
    $language = $this->handler->package('lang');
    $handlebars = $this->handler->package('handlebars');

    //set the template root
    $template = __DIR__ . '/template';

    $body = $handlebars
      ->setTemplateFolder($template)
      ->renderFromFolder('404');

    //set content
    $response
      ->set('page', 'title', $language->translate('Oops...'))
      ->set('page', 'class', 'page-404 page-error')
      ->setContent($body);

    //render page
    $this->render($request, $response);

    return true;
  }

  /**
   * 500 Error Processor
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param *Throwable          $error
   *
   * @return bool
   */
  protected function error500(
    RequestInterface $request,
    ResponseInterface $response,
    Throwable $error
  ): bool {
    //load some packages
    $language = $this->handler->package('lang');
    $handlebars = $this->handler->package('handlebars');

    //set the template root
    $template = __DIR__ . '/template';

    $body = $handlebars
      ->setTemplateFolder($template)
      ->renderFromFolder('500');

    //set content
    $response
      ->set('page', 'title', $language->translate('Oops...'))
      ->set('page', 'class', 'page-500 page-error')
      ->setContent($body);

    //render page
    $this->render($request, $response);
    return true;
  }

  /**
   * Email Error
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param *Throwable          $error
   *
   * @return bool
   */
  protected function errorEmail(
    RequestInterface $request,
    ResponseInterface $response,
    Throwable $error
  ) {
    //load some packages
    $host = $this->handler->package('host');
    $language = $this->handler->package('lang');

    //set the template root
    $template = __DIR__ . '/template';
    //get config settings
    $settings = $config->get('settings');

    //build the email elements
    $to = $settings['email'];

    $subject = sprintf('%s - Error', $settings['name']);

    $body = sprintf(
      "%s thrown: %s\n%s(%s)\n\n%s",
      get_class($error),
      $error->getMessage(),
      $error->getFile(),
      $error->getLine(),
      $error->getTraceAsString()
    );

    [
      'request' => $request,
      'response' => $response
    ] = $this->handler->makePayload();

    //set to
    $request->setStage('to', $to);
    //set event
    $request->setStage(0, 'email-send');
    //set subject
    $request->setStage('subject', $subject);
    //set the text
    $request->setStage('text', $body);
    //set the html
    $request->setStage('html', $body);

    $this->handler->package('event')->emit('queue', $request, $response);

    //if we werent able to queue
    if ($response->isError()) {
      $data = $request->getStage();
      //send manually after the connection
      $this->postprocess(function ($request, $response) use ($data) {
        $this('event')->call('email-send', $data);
      });
    }

    return true;
  }

  /**
   * Debug Error Processor
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param *Throwable         $error
   * @param *string            $mode
   *
   * @return bool
   */
  protected function errorDebug(
    RequestInterface $request,
    ResponseInterface $response,
    Throwable $error,
    string $mode = 'page'
  ) {
    //load some packages
    $host = $this->handler->package('host');
    $language = $this->handler->package('lang');
    $handlebars = $this->handler->package('handlebars');

    //set a snippet range
    $range = 10;

    $data = [
      'message' => $error->getMessage(),
      'class' => get_class($error),
      'file' => $error->getFile(),
      'line' => $error->getLine(),
      'stack' => []
    ];

    //shorten file
    $data['short_file'] = basename($data['file']);
    if (strpos((string) $data['file'], INCEPT_CWD) === 0) {
      $data['short_file'] = substr($data['file'], strlen(INCEPT_CWD));
    }

    //determine the source code snippet
    $file = file($data['file']) ?? [];
    $data['start'] = max($data['line'] - $range, 1);
    $data['end'] = min($data['line'] + $range, count($file));
    //build the snippet (preserve the keys) instead of:
    //$data['snippet'] = array_slice($file, $data['start'], $data['end'] - $data['start']);
    $data['snippet'] = [];
    for ($i = $data['start']; $i <= $data['end']; $i++) {
      if (isset($file[$i - 1])) {
        $data['snippet'][$i] = $file[$i - 1];
      }
    }

    $stack = $error->getTrace();
    $count = count($stack);

    foreach ($stack as $i => $trace) {
      if (!isset($trace['file']) || !file_exists($trace['file'])) {
        continue;
      }

      //shorten file
      $trace['short_file'] = basename($trace['file']);
      if (strpos((string) $data['file'], INCEPT_CWD) === 0) {
        $trace['short_file'] = substr($trace['file'], strlen(INCEPT_CWD) + 1);
      }

      //determine the source code snippet
      $file = file($trace['file']);
      $trace['step'] = $count - $i;
      $trace['start'] = max($trace['line'] - $range, 1);
      $trace['end'] = min($trace['line'] + $range, count($file));
      //build the snippet (preserve the keys) instead of:
      //$trace['snippet'] = array_slice($file, $trace['start'], $trace['end'] - $trace['start']);
      $trace['snippet'] = [];
      for ($j = $trace['start']; $j <= $trace['end']; $j++) {
        if (isset($file[$j - 1])) {
          $trace['snippet'][$j] = $file[$j - 1];
        }
      }

      //unset($trace['args']);
      //determine arguments, prevent big objects and arrays
      if (isset($trace['args'])) {
        foreach ($trace['args'] as $i => $arg) {
          //if it's a string
          if (is_string($arg)) {
            //get the original length
            $length = strlen($arg);
            //get the snippet
            $arg = substr($arg, 0, 100);
            //if the original length does not equal the new length
            if ($length !== strlen($arg)) {
              //add an etc...
              $arg .= '...';
            }
            //change new lines to spaces
            $arg = str_replace(PHP_EOL, ' ', $arg);
            //save it back
            $trace['args'][$i] = '"' . $arg . '"';
            continue;
          }

          if (is_null($arg)) {
            $trace['args'][$i] = 'NULL';
            continue;
          }

          if (is_bool($arg)) {
            $trace['args'][$i] = $arg ? 'TRUE': 'FALSE';
            continue;
          }

          //nothing wrong with scalar nulls
          if (is_scalar($arg) || is_null($arg)) {
            continue;
          }

          if (is_resource($arg)) {
            $trace['args'][$i] = (string) $arg;
            continue;
          }

          if (is_object($arg)) {
            $trace['args'][$i] = get_class($arg);
            if (method_exists($arg, '__traceName')) {
              $trace['args'][$i] = $arg->__traceName();
            }
            continue;
          }

          //if it's an array
          if (is_array($arg)) {
            $arg = print_r($this->printArray($arg), true);
            $arg = str_replace('Array ( ', 'Array(', $arg);
            //get the original length
            $length = strlen($arg);
            //get the snippet
            $arg = substr($arg, 0, 100);
            //if the original length does not equal the new length
            if ($length !== strlen($arg)) {
              //add an etc... then close the array
              $arg .= '...)';
            }

            $trace['args'][$i] = $arg;
            continue;
          }
        }
      }

      $data['stack'][] = $trace;
    }

    $handlebars->registerHelper('plus', function($x, $y) {
      return $x + $y;
    });

    $handlebars->registerHelper('nolines', function($string) {
      $string = preg_replace('/\s+/is', ' ', $string);
      $string = str_replace('Array ( ', 'Array(', $string);
      return str_replace('  ', ' ', $string);
    });

    //set the template root
    $template = __DIR__ . '/template';

    $body = $handlebars
      ->setTemplateFolder($template)
      ->renderFromFolder('debug/' . $mode, $data);

    //set content
    $response
      ->set('page', 'title', $language->translate('Oops...'))
      ->set('page', 'class', 'page-error')
      ->setContent($body);

    if ($mode === 'spa') {
      return;
    }

    //render page
    $this->render($request, $response, 'blank');
    return true;
  }

  /**
   * Flattens a multidimensional row into a singular one
   *
   * @param *array  $row
   * @param *string $path
   *
   * @return array
   */
  protected function flattenRow(array $row, string $path = ''): array
  {
    $flat = [];
    foreach($row as $key => $value) {
      if (is_array($value)) {
        $flat = array_merge($flat, $this->flattenRow($value, $path . $key . '/'));
        continue;
      }

      $flat[$path . $key] = $row[$key];
    }

    return $flat;
  }

  /**
   * Render admin invalid screen and store it in the response
   *
   * @param *ResponseInterface $response
   *
   * @return string
   */
  public function invalid(ResponseInterface $response): string
  {
    $handlebars = $this->handler->package('handlebars');

    $template = __DIR__ . '/template';
    $body = $handlebars->renderFromFile(
      sprintf('%s/invalid.html', $template),
      $response->get('json')
    );

    $response->setContent($body);
    return $body;
  }

  /**
   * Print R for arrays
   *
   * @param *array $array
   *
   * @return bool
   */
  protected function printArray(
    array $array,
    int $level = 0,
    int $range = 10
  ): array {
    $count = 0;
    $printed = [];
    foreach ($array as $key => $value) {
      //if we are at the range, stop
      if (($count++) >= $range) {
        break;
      }

      //if it's a string
      if (is_string($value)) {
        //get the original length
        $length = strlen($value);
        //get the snippet
        $value = substr($value, 0, 100);
        //if the original length does not equal the new length
        if ($length !== strlen($value)) {
          //add an etc...
          $value .= '...';
        }
        //change new lines to spaces
        $value = str_replace(PHP_EOL, ' ', $value);
        //save it back
        $printed[$key] = '"' . $value . '"';
        continue;
      }

      if (is_null($value)) {
        $printed[$key] = 'NULL';
        continue;
      }

      if (is_bool($value)) {
        $printed[$key] = $value ? 'TRUE': 'FALSE';
        continue;
      }

      if (is_numeric($value)) {
        $printed[$key] = (string) $value;
      }

      if (is_resource($value)) {
        $printed[$key] = (string) $value;
        continue;
      }

      if (is_object($value)) {
        $printed[$key] = get_class($value);
        if (method_exists($value, '__traceName')) {
          $printed[$key] = $value->__traceName();
        }
        continue;
      }

      //if it's an array
      if (is_array($value)) {
        if ($level === 3) {
          $printed[$key] = 'Array(...)';
        } else {
          $printed[$key] = $this->printArray($value, $level + 1, $range);
        }

        continue;
      }
    }

    return $printed;
  }

  /**
   * Render admin page and store it in the response
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param string             $layout
   *
   * @return string
   */
  public function render(
    RequestInterface $request,
    ResponseInterface $response,
    string $layout = 'app'
  ): string {
    //load some packages
    $host = $this->handler->package('host');
    $config = $this->handler->package('config');
    $handlebars = $this->handler->package('handlebars');

    //determine the menu
    $menu = $response->get('admin_menu') ?? [];
    $menu = $this->buildMenu($menu, $host->all());

    //deal with flash messages
    if ($request->hasSession('flash')) {
      $flash = $request->getSession('flash');
      $response->set('page', 'flash', $flash);
      $response->removeSession('flash');
    }

    //load history
    $history = $this->handler->package('event')->call('system-collection-history-search', [
      'range' => 5,
      'total' => 0,
      'sort' => [
        'history_created' => 'DESC'
      ]
    ])['rows'];

    $data = [
      'page' => $response->get('page'),
      'results' => $response->getResults(),
      'content' => $response->getContent(),
      'i18n' => $request->getSession('i18n'),
      'host' => $host->all(),
      'history' => $history,
      'menu' => $menu
    ];

    $template = __DIR__ . '/template';

    $page = $handlebars
      ->registerPartialFromFile('head', "$template/_head.html", true)
      ->registerPartialFromFile('left', "$template/_left.html", true)
      ->registerPartialFromFile('right', "$template/_right.html", true)
      ->registerPartialFromFile('flash', "$template/_flash.html", true)
      ->renderFromFile(sprintf('%s/_%s.html', $template, $layout), $data);

    $response->setContent($page);
    return $page;
  }

  /**
   * Helper to convert rows to CSV format
   *
   * @param *array $rows
   *
   * @return array
   */
  public function rowsToCsv(array $rows): array
  {
    //loop through rows
    foreach($rows as $i => $row) {
      //flatten each row
      $rows[$i] = $this->flattenRow($row);
    }

    //build the csv header
    $head = [];
    //loop through rows (again)
    foreach($rows as $row) {
      //loop through the row keys
      $keys = array_keys($row);
      foreach ($keys as $i => $key) {
        //if the key is already in the head
        if (in_array($key, $head)) {
          //no need to add
          continue;
        }

        //if we are here it's not in the head

        //if we are in the first element
        if ($i === 0) {
          //add it to the very start of head
          array_unshift($head, $key);
          continue;
        }

        //we can assume the one before this has been added. So let's find it
        $after = array_search($keys[$i - 1], $head);
        array_splice($head, $after + 1, 0, [$key]);
      }
    }

    //build the CSV now, add the head
    $csv = [ $head ];
    //loop through rows (again)
    foreach ($rows as $row) {
      //build the line
      $line = [];
      //map the line by the order of the head
      foreach ($head as $column) {
        //make sure there is a value
        $line[$column] = isset($row[$column]) ? $row[$column]: null;
      }
      //now add it to the master CSV
      $csv[] = array_values($line);
    }

    return $csv;
  }

  /**
   * Helper to convert rows to XML format
   *
   * @param *array  $rows
   * @param *string $root The root element
   *
   * @return SimpleXMLElement
   */
  public function rowsToXml(array $rows, string $root): SimpleXMLElement
  {
    //set up the xml template
    $root = sprintf(static::XML_TEMPLATE, $root, $root);

    //get the contents
    return $this->toXml($rows, new SimpleXMLElement($root))->asXML();
  }

  /**
   * Helper to convert array to XML format
   *
   * @param *array            $array
   * @param *SimpleXMLElement $xml
   *
   * @return SimpleXMLElement
   */
  protected function toXml(array $array, SimpleXMLElement $xml): SimpleXMLElement
  {
    //for each array
    foreach ($array as $key => $value) {
      //if the value is an array
      if (is_array($value)) {
        //if the key is not a number
        if (!is_numeric($key)) {
          //send it out for further processing (recursive)
          $this->toXml($value, $xml->addChild($key));
          continue;
        }

        //send it out for further processing (recursive)
        $this->toXml($value, $xml->addChild('item'));
        continue;
      }

      //add the value
      if (!is_numeric($key)) {
        $xml->addChild($key, htmlspecialchars($value));
        continue;
      }

      $xml->addChild('item', htmlspecialchars($value));
    }

    return $xml;
  }
}
