<?php

header('Content-type: text/xml');

require_once '/opt/dpp-registry/src/entities/classes.php';

$user	= null;
$xml	= simplexml_load_string('<tablet></tablet>');

$whitelist = array('localhost', '127.0.0.1', '::1');
if(!in_array($_SERVER['HTTP_HOST'], $whitelist))
{
	if(!isset($_SERVER['HTTPS']) || strcasecmp($_SERVER['HTTPS'],"on") != 0)
	{
		$xml->addChild('error', 'You must use SSL encryption to access this site');
	}

	if(!isset($_SERVER['PHP_AUTH_USER']))
	{
		$xml->addChild('error', 'You must be authenticated to access this site');
	}

	echo $xml->asXML();
	exit;
}

$user = UserRepository::getUserHavingName($_SERVER['PHP_AUTH_USER']);

echo $xml->asXML();
