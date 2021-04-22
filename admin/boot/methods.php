<?php //-->

use UGComponents\IO\Request\RequestInterface;
use UGComponents\IO\Response\ResponseInterface;

//register a pseudo admin and load it
$this->register('admin')->package('admin')
  /**
   * Render Admin Page
   *
   * @param *RequestInterface  $request
   * @param *ResponseInterface $response
   * @param string             $layout
   *
   * @return string
   */
  ->addPackageMethod('render', function(
    RequestInterface $request,
    ResponseInterface $response,
    string $layout = 'app'
  ): string {
    //this is the same as incept()
    $handler = $this->getPackageHandler();

    //determine the menu
    $menu = $response->get('menu');
    if (!is_array($menu)) {
      $menu = $handler('config')->get('menu');
    }

    if (!is_array($menu)) {
      $menu = [];
    }

    $host = $handler('host')->all();
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

    $template = dirname(__DIR__) . '/template';

    $page = $handler('handlebars')
      ->registerPartialFromFile('head', $template . '/_head.html')
      ->registerPartialFromFile('left', $template . '/_left.html')
      ->registerPartialFromFile('right', $template . '/_right.html')
      ->registerPartialFromFile('flash', $template . '/_flash.html')
      ->renderFromFile(sprintf('%s/_%s.html', $template, $layout), $data);

    $response->setContent($page);
    return $page;
  })

  /**
   * Render Admin Page
   *
   * @param *ResponseInterface $response
   *
   * @return string
   */
  ->addPackageMethod('invalid', function(ResponseInterface $response): string {
    $handler = $this->getPackageHandler();

    $template = dirname(__DIR__) . '/template';
    $body = $handler('handlebars')->renderFromFile(
      sprintf('%s/invalid.html', $template),
      $response->get('json')
    );

    $response->setContent($body);
    return $body;
  })

  ->addPackageMethod('rowsToCsv', function($rows) {
    $flattenRow = function($row, $path = '') use (&$flattenRow) {
      $flat = [];
      foreach($row as $key => $value) {
        if (is_array($value)) {
          $flat = array_merge($flat, $flattenRow($value, $path . $key . '/'));
          continue;
        }

        $flat[$path . $key] = $row[$key];
      }
      return $flat;
    };

    foreach($rows as $i => $row) {
      $rows[$i] = $flattenRow($row);
    }

    $head = [];
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

    $csv = [$head];
    foreach ($rows as $row) {
      $line = [];
      foreach ($head as $column) {
        $line[$column] = isset($row[$column]) ? $row[$column]: null;
      }

      $csv[] = array_values($line);
    }

    return $csv;
  })

  ->addPackageMethod('rowsToXml', function($rows, $root) {
    //recursive xml parser
    $toXml = function ($array, $xml) use (&$toXml) {
      //for each array
      foreach ($array as $key => $value) {
        //if the value is an array
        if (is_array($value)) {
          //if the key is not a number
          if (!is_numeric($key)) {
            //send it out for further processing (recursive)
            $toXml($value, $xml->addChild($key));
            continue;
          }

          //send it out for further processing (recursive)
          $toXml($value, $xml->addChild('item'));
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
    };

    //set up the xml template
    $root = sprintf("<?xml version=\"1.0\"?>\n<%s></%s>", $root, $root);

    //get the contents
    return $toXml($rows, new SimpleXMLElement($root))->asXML();
  })
  ;
