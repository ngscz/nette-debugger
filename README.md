# NetteDebugger #

Easy option, how to **manage Nette framework exceptions** from log dir.

Also add method to **clear cache and log** by clicking on button in browser.

## Instalation ##

Use composer

Via command line: `composer require ngscz/nette-debugger  "~1.0"`.

## Usage ##

Create PHP file `debugger.php` in your web root directory.

and add this code:

```php
<?php 

require dirname(__FILE__) . '/../vendor/autoload.php';

use ngscz\NetteDebugger;

$debugger = new NetteDebugger\Debugger();
$debugger->run();
```


Open `/debugger.php` in your favourite browser.