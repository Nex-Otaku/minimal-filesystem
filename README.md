<p align="center">
    <img src="https://github.com/Nex-Otaku/minimal-filesystem/blob/master/img/logo.png?raw=true" height="200px" />
    <h1 align="center">Minimal Filesystem</h1>
    <h3 align="center">Zero dependency. Small and powerful.</h3>
</p>

Installation
------------

The preferred way to install is through [composer](http://getcomposer.org/download/).

```
composer require nex-otaku/minimal-filesystem
```

**Packagist** allows installation of new packages, including this one, to only Composer version 2. ([see how to upgrade](https://blog.packagist.com/composer-2-0-is-now-available/))

So if you are tied to Composer version 1, you can't install package with the command above.

If it is your case, you can just copy-paste source file to your project. It has no dependencies and should work just fine.

Examples
-----

```php
$fs = new \NexOtaku\MinimalFilesystem\Filesystem();

// List all files in directory
var_dump($fs->listFiles('/var/www'));

// Read file
echo $fs->readFile('/var/log/my-app.log');

// Write file
$fs->writeFile('/var/etc/my-app.conf', json_encode(['favoriteCoffee' => 'Double Espresso']));

// Append to file
$fs->appendToFile('/var/log/my-app.log', 'Logging is easy!');
```

Filesystem commands
-----
### Directories
 - createDirectory
 - createDirectoryForFile
 - getCurrentDirectory
 - existsDirectory
 - isDirectory
### Files
 - writeFile
 - appendToFile
 - isReadableFile
 - readFile
 - renameFile
 - deleteFile
 - existsFile
 - isFile

### Paths
 - exists

### Listing, searching:
 - listFiles
 - searchFiles
 - searchFilesRecursively

Questions? Ask!
-----

Telegram: **[@nex_otaku](https://t.me/nex_otaku)**

Email: **[nex-otaku@yandex.ru](mailto:nex-otaku@yandex.ru)**

## License

[![License](http://img.shields.io/:license-mit-blue.svg?style=flat-square)](http://badges.mit-license.org)

- **[MIT license](http://opensource.org/licenses/mit-license.php)**
- Copyright 2021 © Nex Otaku.
