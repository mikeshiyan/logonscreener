LogonScreener for Windows 7

A PHP script for random changing win7 logonscreen images.

Command line example:
W:/usr/local/php5/php-win.exe W:/home/localhost/www/logonscreener/index.php C:/Users/Public/Pictures/wallpapers/ 1366 768
or for debugging:
W:/usr/local/php5/php.exe W:/home/localhost/www/logonscreener/index.php C:/Users/Public/Pictures/wallpapers/ 1366 768 debug

@todo:
  - Technical requirements in readme & checking in script:
    - php ver;
    - image ext.
  - Describe installation steps in readme.
  - Add license and/or copyright docs.
  - Make use of tmp files instead of creating new ones (or save tmp files next
    to this script).
  - Unlocal chars (like 'é') in filenames are not supported (Local [rus] are).
