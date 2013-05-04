LogonScreener for Windows 7

A PHP script for random changing win7 logonscreen images.


REQUIREMENTS
------------

- Windows 7 (don't know if admin rights needed).
- PHP 5.2.1 (or higher).
- GD 2.0.28 (or higher) library is optional and only needed to resize images.
  Version 2.0.28 is bundled already (but not enabled by default) with PHP 5.2.1.


INSTALLATION
------------

1. Download this script and place it wherever you want on your machine.

2. Download the latest stable PHP in Zip from http://windows.php.net/download/
   (Thread Safe or Non - doesn't matter), and extract it wherever you want
   on your machine.

   Inside extracted PHP folder locate php.ini-development (or
   php.ini-recommended) file and open it for editing in any notepad-like
   application:
   - Find 'extension_dir' directive, uncomment it (remove leading semicolon)
     and set to 'ext', so if it was:

     ; extension_dir = "./"

     or something like that, now it should be:

     extension_dir = "ext"

   - Find 'extension = php_gd2.dll' and uncomment it too.

   Save this file as php.ini (note the file's extension) in the same folder.

   Now you're able to test this script (refer to Debugging section for
   information on how to do it).

3. Run Task Scheduler: press Win+R, type 'taskschd.msc' (without the quotes) and
   press OK.

   Choose "Create Task" in "Action" menu of Task Scheduler.

   On the "General" tab fill in such fields:
   - Name: LogonScreener (or whatever you wish).
   - Configure for: Windows 7.

   On the "Triggers" tab press "New", then fill in the information about when
   and how often you want the logonscreen to be changed. For example:
   - Begin the task: On a schedule.
   - Repeat task every: 30 minutes.
   - For a duration of: Indefinitely.

   On the "Actions" tab press "New", then fill in the next information:
   - Action: Start a program.
   - Program/script: here you need to insert a full path to php-win.exe in PHP
     folder. For example, C:/php5/php-win.exe
   - Add arguments: here you need to list following items separating them by
     a space:
     - full path to logonscreener script file;
     - full path to your images folder (without trailing slash);
     - your screen's width (in pixels);
     - your screen's height (in pixels).
     If any of paths contains spaces, wrap the full path in "double-quotes".
     For example, D:/logonscreener/index.php "E:/my pictures" 1366 768

   Press OK - task is saved.


DEBUGGING
---------

Debug process from cmd:
W:/usr/local/php5/php.exe W:/home/localhost/www/logonscreener/index.php C:/Users/Public/Pictures/wallpapers 1366 768 debug

@todo Describe each possible step from log and how to resolve it: preparing
destination folder, warning about Image functions are very memory intensive (Be
sure to set memory_limit high enough
http://php.net/manual/image.configuration.php), etc.


TODO
----

- Reread THAT post about logonscreen image requirements.
- Add license and/or copyright docs.
- Make use of tmp files instead of creating new ones (or save tmp files next
  to this script).
- Unlocal chars (like 'é') in filenames are not supported (Local [rus] are).


CREDITS
-------

Mike Shiyan <logonscreener@mike.pp.ua>
https://bitbucket.org/mikeshiyan/logonscreener
