# Graphical Installer

### How to add steps

 1. Add this to a preprocessor and customize

```php
$res->setPage('install_steps', 'your_step', [
  'label' => 'Custom',
  'icon' => 'fa-user',
  'path' => '/install/custom'
]);
```

 2. Create a controller that looks like the following

```php
$this('http')->get('/install/database', function($req, $res) {
  //there could be no active step
  $activeStep = -1;
  //get all the install steps so we can configure the state for each
  $steps = array_values($res->getPage('install_steps'));
  //for each step
  foreach($steps as $i => $step) {
    //if this is the path
    if ($step['path'] === '/install/database') {
      //mark the step and break out
      $activeStep = $i;
      break;
    }
  }

  //for each step (again)
  foreach($steps as $i => $step) {
    //if the step is before the active step
    if ($i < $activeStep) {
      //the state is passed
      $steps[$i]['passed'] = true;
    //if the step is the current step
    } else if ($i === $activeStep) {
      //the state is active
      $steps[$i]['active'] = true;
    }
  }

  //save the updated install steps
  $res->setPage('install_steps', $steps);

  //... YOUR LOGIC ....
});
```
