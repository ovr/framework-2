# Brainwave Config

This library handles config files. It's responsible for loading, saving and accessing config settings.

There are multiple formats in which config files can be handled:

- php
- json
- yaml
- ini
- toml
- xml

Symfony\Yaml is needed in order to parse and format `.yml` files.

Yosymfony\Toml is needed in order to parse and format `.toml` files.

## Loading

Get a new container

```
use Brainwave\Config;

$config = new Configuration(new ConfigurationHandler, new FileLoader);
```

We'll need to add a path to load the files from:

```
$config->addPath(__DIR__.'app/config');

## Accessing data

Therefor it's possible to retrieve the data in two ways: through ->get and the ArrayAccess way.

```
$setting = $config->get('setting', 'fallback');

// is the same as

$setting = $config['setting'];
```

The first way does allow you to supply a default

//TODO finish docu