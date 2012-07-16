<?php
// A http 404 response
function do_404() {
  header('HTTP/1.0 404 Not Found');
  die();
}

// Dest is mandatory
if (!$dest = getenv('E404_PROXY_DEST')) {
  print 'E404: no destination set';
  do_404();
}

// Test for filter, if provided
$uri = $_SERVER['REQUEST_URI'];
if (($filter = getenv('E404_PROXY_FILTER')) && !preg_match($filter, $uri)) {
  print 'E404: filter does not match, ' . $filter;
  do_404();
}

// Adjust uri
$dir = dirname($_SERVER['SCRIPT_NAME']);
$uri = preg_replace('~^' . $dir . '~', '', $uri);

$location = rtrim($dest, '/') . $uri;

// Download to local, if asked
if ($download = getenv('E404_PROXY_DOWNLOAD')) {
  // TODO: add curl support
  $contents = file_get_contents($location);

  if (!$contents) {
    print 'E404: file is empty ' . $location;
    do_404();
  }

  $pwd = dirname(__FILE__);
  $path = str_replace('/', DIRECTORY_SEPARATOR, $uri);
  $file = $pwd . $path;
  $dir = dirname($file);

  if (!is_dir($dir)) {
    if (!mkdir($dir, 0777, TRUE)) {
      print 'E404: unable to create directory ' . $dir;
      do_404();
    }
  }

  if (!file_put_contents($file, $contents)) {
    print 'E404: unable to save file in ' . $file;
    do_404();
  }

  // Reload page
  $location = $_SERVER['REQUEST_URI'];
}

// Redirect to real file
header('Location: ' . $location);