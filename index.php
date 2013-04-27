<?php

$images_directory = 'C:/Users/Public/Pictures/1366x768/';
$logonscreen_path = 'C:/Windows/System32/oobe/info/backgrounds/backgroundDefault.jpg';
$screen_width = 1366;
$screen_height = 768;
change_logonscreen();

function change_logonscreen() {
  global $images_directory, $logonscreen_path;
  $files = scandir($images_directory);

  while (!empty($files)) {
    $key = array_rand($files);
    if (valid_image($files[$key])) {
      $filename = $files[$key];
      break;
    }
    unset($files[$key]);
  }

  // If we got a valid image.
  if (isset($filename)) {
    $filepath = $images_directory . $filename;

    if (!is_dir(dirname($logonscreen_path))) {
      mkdir(dirname($logonscreen_path), 0, TRUE);
    }
    copy($filepath, $logonscreen_path);
  }
}

function valid_image($filename) {
  global $images_directory, $logonscreen_path, $screen_width, $screen_height;

  if ($filename != '.' && $filename != '..' && pathinfo($filename, PATHINFO_EXTENSION) == 'jpg') {
    $filepath = $images_directory . $filename;
    $filesize = filesize($filepath);

    // Exact valid size is unknown.
    // Compare size to existing logonscreen image, so everytime it will be new one.
    if ($filesize < 260844 && (!is_file($logonscreen_path) || $filesize !== filesize($logonscreen_path))) {
      $info = getimagesize($filepath);

      if ($info && $info[2] == IMAGETYPE_JPEG && $info[0] == $screen_width && $info[1] == $screen_height) {
        return TRUE;
      }
    }
  }

  return FALSE;
}

?>