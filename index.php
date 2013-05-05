<?php

/**
 * @file
 * LogonScreener for Windows 7.
 *
 * Script for random changing win7 logonscreen images.
 *
 * @author Mike Shiyan <logonscreener@mike.pp.ua>
 * @link https://bitbucket.org/mikeshiyan/logonscreener
 */

/**
 * Path where logonscreen image resides.
 */
define('LOGONSCREENER_DESTINATION_PATH', 'C:/Windows/System32/oobe/info/backgrounds/backgroundDefault.jpg');

/**
 * Maximum filesize of logonscreen image in bytes.
 *
 * Images bigger than this size will fail to be shown.
 * P.S.: Actual limit is yet unknown. Max known size at the moment: 255 975 B.
 */
define('LOGONSCREENER_MAX_FILESIZE', 256702);

/**
 * Command line arguments.
 *
 * @global string $source
 *   Path to directory with images to be chosen from.
 * @global int $screen_width
 *   Width of the destination image.
 * @global int $screen_height
 *   Height of the destination image.
 * @global bool $debug_mode
 *   (Optional) Flag to run script in debug mode. Log messages will be
 *   printed out.
 */
list(, $source, $screen_width, $screen_height) = $argv;
$debug_mode = !empty($argv[4]);

logonscreener_log('Debug mode ON.');
logonscreener_requirements();
logonscreener_destination_prepare();
logonscreener_source_scan();

/**
 * Checks minimal requirements to run the script.
 */
function logonscreener_requirements() {
  if (version_compare(PHP_VERSION, '5.2.1') < 0) {
    logonscreener_log('Your PHP is too old. Go get the latest.');
    exit;
  }
  if (!function_exists('imagegd2') && (!function_exists('dl') || !@dl('php_gd2.dll'))) {
    logonscreener_log('GD is disabled.');
  }
}

/**
 * Builds the destination folder tree if it does not already exist.
 */
function logonscreener_destination_prepare() {
  if (is_file(LOGONSCREENER_DESTINATION_PATH)) {
    chmod(LOGONSCREENER_DESTINATION_PATH, 0777);
  }
  else {
    $directory = dirname(LOGONSCREENER_DESTINATION_PATH);

    if (!is_dir($directory)) {
      mkdir($directory, 0, TRUE);
    }
  }
}

/**
 * Scans source directory attempting to change logonscreen image.
 *
 * @return bool
 *   FALSE if source directory does not exist, otherwise TRUE.
 */
function logonscreener_source_scan() {
  global $source;
  $source = str_replace('\\', '/', rtrim($source, '/\\'));

  if (!$files = @scandir($source)) {
    if ($GLOBALS['debug_mode'] && is_file($source)) {
      // Debug mode let us set one specific image file as a source.
      $files = array(basename($source));
      $source = dirname($source);
    }
    else {
      logonscreener_log("$source not found.");
      return FALSE;
    }
  }

  while (!empty($files)) {
    $key = array_rand($files);
    if (logonscreener_file_change($source . '/' . $files[$key])) {
      break;
    }
    unset($files[$key]);
  }

  return TRUE;
}

/**
 * Creates a new image for use in logonscreen.
 *
 * Generates an image derivative by checking file path to be a valid image,
 * applying scale and crop effects, and saving a cached version of the
 * resulting image.
 *
 * @param string $file
 *   Path of the source file.
 *
 * @return bool
 *   TRUE if an image is valid for logonscreen or if an image derivative was
 *   generated, or FALSE if the image derivative could not be generated.
 */
function logonscreener_file_change($file) {
  if (!$info = logonscreener_image_info($file)) {
    return FALSE;
  }

  // If source image is valid to be a logonscreen or if transformed image exists
  // in cache, then just copy it.
  if (!logonscreener_image_is_valid($info) && ($tmp_file = logonscreener_file_cache($file))) {
    $created = function_exists('imagegd2')
            && ($image = logonscreener_image_load($file, $info['extension']))
            && ($image = logonscreener_image_scale_and_crop($image, $info['width'], $info['height']))
            && ($file  = logonscreener_image_save($image, $tmp_file));
    if (!$created) {
      return FALSE;
    }
  }

  if (!@copy($file, LOGONSCREENER_DESTINATION_PATH)) {
    logonscreener_log('FILE WAS NOT COPIED!!! Dunno Y :(');
    // Do not return FALSE because other images may get the same fate.
  }

  return TRUE;
}

/**
 * Gets details about an image.
 *
 * Supported file formats are GIF, JPG and PNG.
 *
 * @param string $file
 *   String specifying the path of the image file.
 *
 * @return bool|array
 *   FALSE, if the file could not be found or is not an image. Otherwise, a
 *   keyed array containing information about the image:
 *   - "width": Width, in pixels.
 *   - "height": Height, in pixels.
 *   - "extension": Commonly used file extension for the image.
 *   - "file_size": File size in bytes.
 */
function logonscreener_image_info($file) {
  if (!is_file($file)) {
    logonscreener_log("$file is not a file.");
    return FALSE;
  }

  $data = getimagesize($file);

  if (!is_array($data)) {
    logonscreener_log("No details for $file");
    return FALSE;
  }

  $extensions = array('1' => 'gif', '2' => 'jpeg', '3' => 'png');
  $extension = isset($extensions[$data[2]]) ?  $extensions[$data[2]] : '';
  $info = array(
    'width'     => $data[0],
    'height'    => $data[1],
    'extension' => $extension,
    'file_size' => filesize($file),
  );

  logonscreener_log("$file details:", $info);
  return $info;
}

/**
 * Checks if an image is valid to be a logonscreen without any modification.
 *
 * To be a logonscreen image must meet the following conditions:
 * - Image dimensions must equal to the screen ones.
 * - Image must be in JPEG format.
 * - Size of an image file must not exceed a certain value
 *   (specified by LOGONSCREENER_MAX_FILESIZE constant).
 *
 * @param array $info
 *   Image information return by logonscreener_image_info().
 *
 * @return bool
 *   TRUE, if an image meets the required conditions, FALSE otherwise.
 *
 * @see LOGONSCREENER_MAX_FILESIZE
 * @see logonscreener_image_info()
 */
function logonscreener_image_is_valid($info) {
  $valid = $info['width']     == $GLOBALS['screen_width']
        && $info['height']    == $GLOBALS['screen_height']
        && $info['extension'] == 'jpeg'
        && $info['file_size'] <= LOGONSCREENER_MAX_FILESIZE;

  if ($valid) {
    logonscreener_log('Image is valid with no further transformation.');
  }

  return $valid;
}

/**
 * Checks if file needs to be cached.
 *
 * @param string &$file
 *   Source file path. If cached version of file exists, then this parameter
 *   will point to it instead of source file.
 *
 * @return bool|string
 *   The path to save the cached file to on success, or FALSE if file is cached
 *   already.
 */
function logonscreener_file_cache(&$file) {
  $directory = str_replace('\\', '/', rtrim(sys_get_temp_dir(), '/\\'));
  $filename  = pathinfo($file, PATHINFO_FILENAME);
  $tmp_file  = "$directory/logonscreener $filename.jpg";
  logonscreener_log("Temporary file: $tmp_file");

  if (is_file($tmp_file)) {
    $file = $tmp_file;
    logonscreener_log('Cached file exists.');
    return FALSE;
  }

  return $tmp_file;
}

/**
 * Loads an image file and returns an image resource.
 *
 * @param string $file
 *   Path to an image file.
 * @param string $extension
 *   Image file extension.
 *
 * @return bool|resource
 *   An image resource identifier (GD image handle) on success, or FALSE
 *   if there was a problem loading the file.
 */
function logonscreener_image_load($file, $extension) {
  $function = 'imagecreatefrom' . $extension;

  if (!function_exists($function) || !($image = $function($file))) {
    logonscreener_log("$function() does not exist or it did not return what was expected.");
    return FALSE;
  }

  return $image;
}

/**
 * Scales an image to the exact width and height given.
 *
 * This function achieves the target aspect ratio by cropping the original image
 * equally on both sides, or equally on the top and bottom.
 *
 * The resulting image always has the exact target dimensions.
 *
 * @param resource $image
 *   An image identifier returned by logonscreener_image_load().
 * @param int $source_width
 *   Width of loaded image.
 * @param int $source_height
 *   Height of loaded image.
 *
 * @return bool|resource
 *   An image resource scaled and cropped to the target dimensions on success,
 *   or FALSE on errors.
 *
 * @see logonscreener_image_load()
 */
function logonscreener_image_scale_and_crop($image, $source_width, $source_height) {
  global $screen_width, $screen_height;

  // Phase 1: Scale.
  $scale = max($screen_width / $source_width, $screen_height / $source_height);
  $scaled_width = (int) round($source_width * $scale);
  $scaled_height = (int) round($source_height * $scale);

  if ($scale != 1) {
    logonscreener_log("Scale = $scale. Scaled width = $scaled_width. Scaled height = $scaled_height.");

    $res2 = imagecreatetruecolor($scaled_width, $scaled_height);
    imagefill($res2, 0, 0, imagecolorallocate($res2, 255, 255, 255));
    if (!imagecopyresampled($res2, $image, 0, 0, 0, 0, $scaled_width, $scaled_height, $source_width, $source_height)) {
      logonscreener_log('Scale did not worked out. HZ.');
      return FALSE;
    }

    // Destroy the original image and update image object.
    imagedestroy($image);
    $image = $res2;
  }

  // Phase 2: Crop.
  if ($scaled_width != $screen_width || $scaled_height != $screen_height) {
    $screen_width = (int) $screen_width;
    $screen_height = (int) $screen_height;

    $x = ($scaled_width - $screen_width) / 2;
    $y = ($scaled_height - $screen_height) / 2;
    logonscreener_log("Crop source X = $x. Crop source Y = $y.");

    $res = imagecreatetruecolor($screen_width, $screen_height);
    imagefill($res, 0, 0, imagecolorallocate($res, 255, 255, 255));
    if (!imagecopyresampled($res, $image, 0, 0, $x, $y, $screen_width, $screen_height, $screen_width, $screen_height)) {
      logonscreener_log('Crop did not worked out. HZ.');
      return FALSE;
    }

    // Destroy the original image and update image object.
    imagedestroy($image);
    $image = $res;
  }

  return $image;
}

/**
 * Closes the image and saves the changes to a temporary file.
 *
 * The function is called recursively with lower quality value on each iteration
 * while the size of image file exceeds the LOGONSCREENER_MAX_FILESIZE value.
 *
 * @param resource $image
 *   An image resource returned by logonscreener_image_load() and transformed.
 * @param string $filename
 *   The path to save the file to.
 * @param int $quality
 *   Ranges from 0 (worst quality, smaller file) to 100 (best quality,
 *   biggest file).
 *
 * @return bool|string
 *   Path to temporary saved image file on success, FALSE on failure.
 *
 * @see LOGONSCREENER_MAX_FILESIZE
 * @see logonscreener_image_load()
 */
function logonscreener_image_save($image, $filename, $quality = 100) {
  if (!imagejpeg($image, $filename, $quality)) {
    logonscreener_log("Could not write to tmp file with $quality% quality.");
    return FALSE;
  }

  // Clear the cached file size and get the image information.
  clearstatcache();
  $filesize = filesize($filename);

  if ($filesize > LOGONSCREENER_MAX_FILESIZE) {
    logonscreener_log("Quality = $quality%. Filesize = $filesize B.");
    $filename = logonscreener_image_save($image, $filename, --$quality);
  }
  else {
    logonscreener_log("Resulted image quality: $quality%.", "Resulted file size: $filesize.");
  }

  return $filename;
}

/**
 * Logs debug messages.
 *
 * Whole process of logonscreen changing is printing out if script is called
 * with the 'debug_mode' argument.
 *
 * @param mixed
 *   Any string or variable to be printed. Function takes any number
 *   of arguments, each will be printed on a new line.
 */
function logonscreener_log() {
  if ($GLOBALS['debug_mode']) {
    foreach (func_get_args() as $var) {
      if (is_string($var)) {
        print $var;
        print "\n";
      }
      else {
        print_r($var);
      }
    }
  }
}
