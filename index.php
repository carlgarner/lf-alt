<?php

ini_set('display_errors', 'off');

header('Content-type: text/xml');

require_once 'helpers/appserver.php';
require_once 'helpers/registry.php';

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

function __autoload($class)
{
	define('APP', '/opt/dpp-appserver/src/');
	define('REG', '/opt/dpp-registry/src/');
	
	$dirs = array(	APP,
					APP . 'actions',
					APP . 'rules',
					APP . 'api',
					APP . 'api/renderers',
					APP . 'api/delivery',
					APP . 'events',
					APP . 'exceptions',
					REG,
					REG . 'entities',
				);
				
	foreach($dirs as $dir)
	{
		$file = $dir . '/' . $class . '.php';
		
		if(file_exists($file))
		{
			require_once $file;
		}
	}
}