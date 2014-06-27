<?php

#header('Content-type: text/plain');
#print_r($_SERVER);
#print_r(explode('/', ltrim($_SERVER['PATH_INFO'], '/')));
#exit();

ini_set('display_errors', 'off');

header('Content-type: text/xml');

require_once 'helpers/applications.php';

$user	= null;
$xml	= simplexml_load_string('<tablet></tablet>');

$whitelist = array('localhost', '127.0.0.1', '::1');
if(!in_array($_SERVER['HTTP_HOST'], $whitelist))
{
	if(!isset($_SERVER['HTTPS']) || strcasecmp($_SERVER['HTTPS'], 'on') != 0)
	{
		$xml->addChild('error', 'You must use SSL encryption to access this site');
		exit($xml->asXML());
	}

	if(!isset($_SERVER['PHP_AUTH_USER']))
	{
		$xml->addChild('error', 'You must be authenticated to access this site');
		exit($xml->asXML());
	}
}

$path = explode('/', ltrim($_SERVER['PATH_INFO'], '/'));

switch($path[0])
{
	case 'applist':
		getAppList($xml, $_SERVER['PHP_AUTH_USER']);
		break;
	case 'fetchapp':
		getDefinition($xml, $path[1]);
		break;
	default:
		$xml->addChild('error', 'No recognised action set');
}

echo $xml->asXML();
