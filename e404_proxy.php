<?php

/**
 * A http 404 response.
 * */
function do_404($message) {
  header('HTTP/1.0 404 Not Found');
  header('X-E404:' . $message);
  print 'E404: ' . $message;
  die();
}

/**
 * Gets configuration parameter.
 * */
function cfg($key, $default = NULL) {
  return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
}

/**
 * Downloads remote file.
 * */
function http_request($location) {
  if (!extension_loaded('curl')) {
    do_404('CURL library must be installed to download files.');
  }

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_URL, $location);
  $data = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $result = array();
  $result['data'] = $data;
  $result['code'] = $code;

  return $result;
}

// Destination is mandatory.
if (!$dest = cfg('E404_PROXY_DEST')) {
  do_404('no destination set');
}

// Test for filter, if provided.
$uri = $_SERVER['REQUEST_URI'];
if (($filter = cfg('E404_PROXY_FILTER')) && !preg_match($filter, $uri)) {
  do_404('filter does not match, ' . $filter);
}

// Dev version is in sub-path? Remove this path when resolving the name of file in production.
// If production version is in sub-path you just need to append it to E404_PROXY_DEST.
if ($path = cfg('E404_PROXY_PATH')) {
  $uri = preg_replace('~^' . rtrim($path, '/') . '~', '', $uri);
}

$location = rtrim($dest, '/') . $uri;

// Download locally, if asked.
if ($download = cfg('E404_PROXY_DL')) {
  $http_result = http_request($location);

  if ($http_result['code'] != 200) {
    do_404('got ' . $http_result['code'] . ' trying to download ' . $location);
  }

  $pwd = dirname(__FILE__);
  $path = str_replace('/', DIRECTORY_SEPARATOR, $uri);
  $file = $pwd . $path;
  $dir = dirname($file);

  // Permissions to new directories and downloaded files.
  $perms = cfg('E404_PROXY_DL_PERMS', 0777);
  $perms = intval($perms, 8);

  if (!is_dir($dir)) {
    if (!mkdir($dir, $perms, TRUE)) {
      do_404('unable to create directory ' . $dir);
    }
  }

  if (!file_put_contents($file, $http_result['data'])) {
    do_404('unable to save file in ' . $file);
  }

  chmod($file, $perms);

  // Reload page.
  $location = $_SERVER['REQUEST_URI'];
}

// Redirect to real file.
header('Location: ' . $location);
