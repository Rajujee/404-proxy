## Scenario
You have just started working on a already-deployed project to add new features.
You downloaded the latest version of db from production, latest version of code from source control. You couldn't download all resources from production because there are a lot of files and document root is very large.
You would like to download missing files only when they are required. 

## Overview
This scripts solves 404 errors caused by missing files in local version which are instead present on the application in production.

## Usage
* Copy e404_proxy.php into the document root of your website.
* Add required environment variables.
* Set this script for handling 404 errors.

For example, with .htaccess:
<pre>
 &lt;IfModule mod_env.c&gt;
   SetEnv E404_PROXY_DEST "http://PRODUCTION_HOST_AND_URI"
    # Optional, for filtering accepted urls
   SetEnv E404_PROXY_FILTER /\.jpg$/i
 &lt;/IfModule&gt;
 ErrorDocument 404 /e404_proxy.php
</pre>

By default, this scripts redirects to the real location in which file actually exists. Set E404_PROXY_DOWNLOAD to download remove resource and save a local copy of the file. Next time the file will be served natively from the webserver.

## TODO
Test and provide documentation for nginx based application/websites.