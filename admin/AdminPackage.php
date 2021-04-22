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
  /**
   * Add handler for scope when routing
   *
   * @param *Framework $handler
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
    //load some packages
    $host = $this->handler->package('host');
    $config = $this->handler->package('config');
    $language = $this->handler->package('lang');
    $handlebars = $this->handler->package('handlebars');

    //get the path
    $path = $request->getPath('string');
    //if not an admin path
    if ($path !== '/admin' && strpos($path, '/admin/') !== 0) {
      return;
    }

    //if it was a call for an actual file
    if (preg_match('/\.[a-zA-Z0-9]{1,4}$/', $path)) {
      return;
    }

    //if this is not an html page
    $type = $response->getHeaders('Content-Type');
    if (strpos($type, 'html') === false) {
      //don't make it pretty
      return;
    }

    //get the code
    $code = $response->getCode();
    //set the template root
    $template = __DIR__ . '/template';

    if ($code === 404) {
      $body = $handlebars
        ->setTemplateFolder($template)
        ->renderFromFolder('404');

      //set content
      $response
        ->setPage('title', $language->translate('Oops...'))
        ->setPage('class', 'page-404 page-error')
        ->setContent($body);

      //render page
      $this->render($request, $response);

      return true;
    }

    //get config settings
    $config = $config->get('settings');

    //if no environment
    if (!isset($config['environment'])
      //or the environment is not production
      || $config['environment'] !== 'production'
      //or it's not a 500 error
      || $code !== 500
    ) {
      //don't make it pretty
      return;
    }

    //okay make it pretty...
    $body = $handlebars
      ->setTemplateFolder($template)
      ->renderFromFolder('500');

    //set content
    $response
      ->setPage('title', $language->translate('Oops...'))
      ->setPage('class', 'page-500 page-error')
      ->setContent($body);

    //render page
    $this->render($request, $response);

    if (!isset($config['email'])) {
      return true;
    }

    //build the email elements
    $to = $config['email'];
    $from = [
      'name' => $config['name'],
      'address' => sprintf('error@%s', $host->domain())
    ];
    $subject = sprintf('%s - Error', $config['name']);
    $body = sprintf(
      "%s thrown: %s\n%s(%s)\n\n%s",
      get_class($error),
      $error->getMessage(),
      $error->getFile(),
      $error->getLine(),
      $error->getTraceAsString()
    );
    //send mail eventually
    $this->sendMail($from, $to, $subject, $body);

    return true;
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

    $template = dirname(__DIR__) . '/template';
    $body = $handlebars->renderFromFile(
      sprintf('%s/invalid.html', $template),
      $response->get('json')
    );

    $response->setContent($body);
    return $body;
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
    $menu = $response->get('menu');
    if (!is_array($menu)) {
      $menu = $config->get('menu');
    }

    if (!is_array($menu)) {
      $menu = [];
    }

    $host = $host->all();
    foreach ($menu as $i => $item) {
      if (isset($item['submenu']) && is_array($item['submenu'])) {
        $active = false;
        foreach ($item['submenu'] as $j => $subitem) {
          if (isset($subitem['path']) && strpos($subitem['path'], $host['path']) === 0) {
            $menu[$i]['submenu'][$j]['active'] = true;
            $active = true;
          }
        }

        if ($active) {
          $menu[$i]['active'] = true;
        }

        continue;
      }

      if (strpos($item['path'], $host['path']) === 0) {
        $menu[$i]['active'] = true;
      }
    }

    //deal with flash messages
    if ($request->hasSession('flash')) {
      $flash = $request->getSession('flash');
      $response->set('page', 'flash', $flash);
      $response->removeSession('flash');
    }

    $data = [
      'page' => $response->get('page'),
      'results' => $response->getResults(),
      'content' => $response->getContent(),
      'i18n' => $request->getSession('i18n'),
      'host' => $host,
      'menu' => $menu
    ];

    $template = __DIR__ . '/template';

    $page = $handlebars
      ->registerPartialFromFile('head', $template . '/_head.html')
      ->registerPartialFromFile('left', $template . '/_left.html')
      ->registerPartialFromFile('right', $template . '/_right.html')
      ->registerPartialFromFile('flash', $template . '/_flash.html')
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
    $root = sprintf("<?xml version=\"1.0\"?>\n<%s></%s>", $root, $root);

    //get the contents
    return $this->toXml($rows, new SimpleXMLElement($root))->asXML();
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

  /**
   * Helper to send mail
   *
   * @param *array  $from
   * @param *string $to
   * @param *string $subject
   * @param *string $body
   */
  protected function sendMail(
    array $from,
    string $to,
    string $subject,
    string $body
  ) {
    //load some packages
    $http = $this->handler->package('http');
    //send it eventually
    $http->postprocess(function() use ($from, $to, $subject, $body) {
      $this->package('event')->call('email-send', [
        'to' => [
          [
            'address' => $to
          ]
        ],
        'from' => $from,
        'subject' => $subject,
        'plain' => $body
      ]);
    });
  }
}
