LogonScreener for Windows 7

A PHP script for random changing win7 logonscreen images.

Command line example:
W:/usr/local/php5/php-win.exe W:/home/localhost/www/logonscreener/index.php C:/Users/Public/Pictures/wallpapers/ 1366 768
or for debugging:
W:/usr/local/php5/php.exe W:/home/localhost/www/logonscreener/index.php C:/Users/Public/Pictures/wallpapers/ 1366 768 debug

@todo:
  - Describe installation steps in readme.
  - Add license and/or copyright docs.
  - Make use of tmp files instead of creating new ones (or save tmp files next
    to this script).
  - Unlocal chars (like 'é') in filenames are not supported (Local [rus] are).

Requirements:
- PHP 5.2.1+ is required.
- GD 2.0.28+ library is optional and only needed to resize images.
  Version 2.0.28 is bundled already (but not enabled by default) with PHP 5.2.1.
Notes (TODO: Put this in install steps):
- Warning: Image functions are very memory intensive. Be sure to set
  memory_limit high enough.
  @link http://php.net/manual/image.configuration.php
