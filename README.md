# Less Service Provider for Silex
Simple less php service provider for Silex that uses https://github.com/leafo/lessphp as parser.

You'll find the following topics in this README:
*   [Dependencies](#dependencies)
*   [Installation](#installation)
*   [Configuration](#configuration)
*   [Usage](#usage)

## Dependencies
*   [Silex](http://silex.sensiolabs.org/)
*   PHP 5.3.2 and above
*   [LESS compiler written in PHP](https://github.com/leafo/lessphp)

## Installation
You basically have three options to add this extension to your Silex project. We'd strongly recommend the first option!

### composer.json
http://packagist.org/packages/fredjuvaux/silex-less-provider

```bash
php composer.phar require fredjuvaux/doctrine-less-provider
```

Or add to the dependencies in your projects composer.json file and update your dependencies::

```bash
"fredjuvaux/doctrine-less-provider": "*"
```

```bash
php composer.phar update
```

This is by far the easiest way, since it automatically adds the Doctrine dependencies and adds everything to the autoloading mechanism supplied by Composer.

More information on Composer can be found on [getcomposer.org](http://getcomposer.org/).

### Git
Another option is to clone the project:

```bash
cd /path/to/your_project/vendor
git clone git@github.com:fredjuvaux/silex-less-provider.git
```

Or you can add it as a submodule if your project is in a git repository too:

```bash
cd /path/to/your_project
git submodule add git@github.com:fredjuvaux/silex-less-provider.git
```

This will require you to manually install all the dependencies. Also note that you'll need to add the provider to the Silex autoloader (or whatever autoloading mechanism) by hand. More on both subjects can be found below.

### Download an archive
GitHub also gives you the option to [download an ZIP-archive](https://github.com/fredjuvaux/silex-less-provider/zipball/master), which you can extract in your vendor folder. This method will also require you to manually install all the dependencies and add everything to your autoloader.


## Configuration

Registering the Less Service Provider is rather straight forward:

```php
<?php

/* ... */

use Less\Provider\LessServiceProvider;

$app->register(new LessServiceProvider(), array(
    'less.source_dir'   => array(__DIR__.'/../web/less/'), // specify one or serveral directories
    'less.cache_dir'    => __DIR__.'/../var/cache/', // specify one directory
    'less.target_dir'   => __DIR__.'/../web/css/', // specify one directory for compiled files
));