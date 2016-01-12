<?php
// This file is intended to be used as a "routing" script for the PHP 5.4+ internal web server
// It does not need to be included or used outside of this context.
//
// This script checks to see if a static file exists in the current directory (/public), if not
// it bootstraps the index.php and lets Silex/Groowm take over the routing

if (file_exists(__DIR__ . '/' . $_SERVER['REQUEST_URI']))
	return false; //Tells the php -S server to take over routing and serve up the static file
else
	return include "index.php";
