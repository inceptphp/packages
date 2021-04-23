<?php //-->

use Incept\Framework\Schema;

incept(function() {
  //lets load some package
  $emitter = $this('event');
  $terminal = $this('terminal');

  //scan through each file
  foreach (scandir(__DIR__ . '/../schema') as $file) {
    //if it's not a php file
    if(substr($file, -4) !== '.php') {
      //skip
      continue;
    }

    //get the original schema data (in this package)
    $data = include sprintf('%s/schema/%s', dirname(__DIR__), $file);

    //get the schema in the project
    $file = sprintf('%s/%s.php', Schema::getFolder(), $data['name']);
    //if schema file exists
    if (file_exists($file)) {
      //dont create
      $terminal->error(sprintf(
        '%s schema could not be created because it exists',
        $data['name']
      ), false);
      continue;
    }

    //----------------------------//
    // 1. Prepare Data
    //setup a new RnR
    $payload = $this->makePayload();
    $payload['request']->setStage($data);

    //----------------------------//
    // 2. Process Request
    //now trigger
    $emitter->emit(
      'system-schema-create',
      $payload['request'],
      $payload['response']
    );

    //----------------------------//
    // 3. Interpret Results
    //if the event does hot have an error
    if (!$payload['response']->isError()) {
      $terminal->system(sprintf(' - Schema %s was created', $data['name']));
      continue;
    }

    //for each error
    foreach($payload['response']->getValidation() as $key => $message) {
      //report error
      $terminal->error(sprintf('%s - %s', $data['name'], $message), false);
    }
  }
});
