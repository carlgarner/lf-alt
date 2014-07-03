<?php

ini_set('display_errors', 'off');

define('APPs', '/opt/dpp-appserver/src');
define('REGs', '/opt/dpp-registry/src');

require_once 'helpers/autoload.php';
require_once 'helpers/appserver.php';
require_once 'helpers/regserver.php';
require_once 'helpers/docserver.php';
require_once 'helpers/URL.php';

Autoload::register();
Autoload::directories(array(APPs,
							APPs . '/actions',
							APPs . '/rules',
							APPs . '/api',
							APPs . '/api/renderers',
							APPs . '/api/delivery',
							APPs . '/events',
							APPs . '/exceptions',
							REGs,
							REGs . '/entities',
					));

$user	= null;
$URL	= new URL();
$xml	= simplexml_load_string('<tablet></tablet>');

$whitelist = array('localhost', '127.0.0.1', '::1');
if(!in_array($_SERVER['HTTP_HOST'], $whitelist))
{
	if(!isset($_SERVER['HTTPS']) || strcasecmp($_SERVER['HTTPS'], 'on') != 0)
	{
		$xml->addChild('error', 'You must use SSL encryption to access this site');
		
		header('Content-type: text/xml');
		exit($xml->asXML());
	}

	if(!isset($_SERVER['PHP_AUTH_USER']))
	{
		$xml->addChild('error', 'You must be authenticated to access this site');
		
		header('Content-type: text/xml');
		exit($xml->asXML());
	}
}

//all URLs should begin at /route
$path = $URL->uri_to_assoc(1);

$reg = new RegServer($_SERVER['PHP_AUTH_USER'], $path, $xml);
$app = new Appserver($_SERVER['PHP_AUTH_USER'], $path, $xml);
$doc = new DocServer($_SERVER['PHP_AUTH_USER'], $path, $xml);

switch($path['route'])
{
	case 'applist':
		$reg->getAppList();
		break;
	case 'fetchapp':
		$app->getDefinition();
		break;
	case 'formlist':
		$app->getFormList();
		break;
	case 'startdoc':
		$doc->startDoc();
		break;
	case 'getdoc':
		$doc->getDoc();
		break;
	case 'updatedoc':
		$doc->updateDoc();
		break;
	default:
		$xml->addChild('error', 'No recognised action set');
}

header('Content-type: text/xml');
echo $xml->asXML();
