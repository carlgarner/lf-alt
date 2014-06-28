<?php

define('APPs', '/opt/dpp-appserver/src');
define('REGs', '/opt/dpp-registry/src');

$regsrv = false;
$appsrv = false;

ini_set('display_errors', 'off');

header('Content-type: text/xml');

require_once 'helpers/appserver.php';
require_once 'helpers/regserver.php';

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
		$reg = new RegServer();
		$reg->getAppList($xml, $_SERVER['PHP_AUTH_USER']);
		break;
	case 'fetchapp':
		$app = new AppServer();
		$app->getDefinition($xml, $path[1]);
		break;
	default:
		$xml->addChild('error', 'No recognised action set');
}

echo $xml->asXML();

function __autoload($class)
{
	global $regsrv, $appsrv;

	$appdirs = array(	APPs,
						APPs . '/actions',
						APPs . '/rules',
						APPs . '/api',
						APPs . '/api/renderers',
						APPs . '/api/delivery',
						APPs . '/events',
						APPs . '/exceptions',
					);
	$regdirs = array(	REGs,
						REGs . '/entities',
					);

	if($regsrv)
	{
		foreach($regdirs as $reg)
		{
			$file = $reg . '/' . $class . '.php';

			if(file_exists($file))
			{
				require_once $file;
			}
		}
	}

	if($appsrv)
	{
		foreach($appdirs as $app)
		{
			$file = $app . '/' . $class . '.php';

			if(file_exists($file))
			{
				require_once $file;
			}
		}
	}
}
