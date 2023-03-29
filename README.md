# Simple config management class for le7 framework or any PHP project

## Requirements

- PHP 8.1 or higher.
- Composer 2.0 or higher.

## What it can?

- Read PHP (array), INI, and JSON files as config
- Load folder with config files to one (it understand config in all supported formats)
- Get config params as object properties
- Get config params as array values
- Get config params by path
- Strictly control param type and get default value if param not exists in config
- Use PSR SimpleCache interface for caching
- Get filtered params (with replacing parts of param values)
- You can write own adapter for your config types
- Register dynamic runtime parameter by path

## Installation

```shell
composer require rnr1721/le7-config
```

## Testing

```shell
composer test
```

## How it works?

```php

use Core\Config\ConfigFactoryGeneric;

    $data = [
        'myparam' => 2,
        'myparam2' => "string value",
        'myparam3' => [
            'myparam4' => false,
            'myparam5' => 44.33
        ]
    ];

    $factory = new ConfigFactoryGeneric();
    $config = $factory->fromArray($data);

    // Get params as object properties (null if empty)
    echo $config->myparam;
    echo $config->myparam3->myparam5;

    // Get params as array
    echo $config['myparam'];
    echo $config['myparam3']['myparam5'];

    // Get params by path (recommended way)
    echo $config->int('myparam',54); // 54 is default value if not exists in config
    echo $config->float('myparam3.myparam5',33.44); // 33.44 is default value if not exists in config
    echo $config->float('myparam3.myparam5'); // throw exception if value not exists in config
    var_dump($config->bool('myparam4/myparam4',true,'/'));
    echo $config->string('myparam2',"default value");

```

## How load PHP array, JSON or INI file?

```php

use Core\Config\ConfigFactoryGeneric;

    $filename = '/var/www/example.com/htdocs/config/config.php';

    $factory = new ConfigFactoryGeneric();
    $config = $factory->fromJsonFile($filename, 'My JSON config');

    // echo $config['myparam']...

```
## How load folder in different folders?

This config manager can load config from folder, in different formats.
For example You have folder where placed JSON files, INI and php Arrays in files

```php

use Core\Config\ConfigFactoryGeneric;

    $folders = [
        '/var/www/example.com/htdocs/config',
        '/var/www/example.com/htdocs/config2'
    ];

    $factory = new ConfigFactoryGeneric();
    // $folders can be string - one folder or array
    // seconf parameter - is suffix between filename and extension i.e. dbConfig.ini or dbConfig.php in this case
    $config = $factory->harvest($folders, 'Config');

    // $config->string('myparam')

```

## How use cache?

Simply inject PSR CacheInterface/

```php

use Core\Config\ConfigFactoryGeneric;

    $filename = '/var/www/example.com/htdocs/config/config.php';

    // $cache is PSR Cacheinterface
    $factory = new ConfigFactoryGeneric($cache);
    
    // myconfig is cache key to store in cache
    $config = $factory->fromArrayFile($filename, 'My PHP config', 'myconfig');

    // $config->string('myparam')

```

## Filtered params

```php

use Core\Config\ConfigFactoryGeneric;

    $data = [
        'myparam' => 2,
        'myparam2' => "My site is {myvariable1}",
        'myparam3' => [
            'myparam4' => false,
            'myparam5' => 44.33
        ]
    ];

    $factory = new ConfigFactoryGeneric();
    $config = $factory->fromArray($data);

    $config->applyFilter('vyvariable1','https://example.com');

    // stringf will return "My site is https://example.com"
    echo $config->stringf('myparam2');

```

## Dynamic parameters

You can add your own parameter (duplicates not allowed)

```php

use Core\Config\ConfigFactoryGeneric;

    $data = [
        'myparam' => 2,
        'myparam2' => "My site is {myvariable1}",
        'myparam3' => [
            'myparam4' => false,
            'myparam5' => 44.33
        ]
    ];

    $factory = new ConfigFactoryGeneric();
    $config = $factory->fromArray($data);

    // Add own parameter
    $config->registerParam('myparam3.testparam77',"test value");

    // Get this parameter
    $config->string("myparam3.testparam77");

```
Also you can combine dynamic parameters with filters:

```php

use Core\Config\ConfigFactoryGeneric;

    $data = [
        'myparam' => 2,
        'myparam2' => "My site is {myvariable1}",
        'myparam3' => [
            'myparam4' => false,
            'myparam5' => 44.33
        ]
    ];

    $factory = new ConfigFactoryGeneric();
    $config = $factory->fromArray($data);

    // Add own parameter 1
    $config->registerParam('myparam3.testdirhome',"/home/www",'homepath');

    // Add own parameter 2
    $config->registerParam('myparam3.testdirbase',"{homepath}/base");

    // Get this parameter (return /home/www/base)
    $config->stringf("myparam3.testdirbase");

```
