<?php

class Appserver
{
	public function getDefinition($xml, $app = false)
	{
		if(!$app)
		{
			$xml->addChild('error', 'Application ID not given');
			return;
		}

		$baseconfig = '/var/opt/dpp-appserver/apps/' . $app . '/config.xml';
		$basepdf	= '/var/opt/dpp-appserver/apps/' . $app . '/background.pdf';

		if(!is_file($baseconfig))
		{
			$ai = new ApplicationInstaller();
			$ai->downloadApp($app, 0);	
		}

		if(!is_file($basepdf))
		{
			$appdir 	= dirname($basepdf);
			$xmlconf	= simplexml_load_file($basexml);

			$psfiles = array();
        	for($i = 1; $i <= $xmlconf->Pages; $i++) 
			{
				$psfiles[] = $appdir . DIRECTORY_SEPARATOR . 'background.' . $i . '.eps';
			}
	
			$width = (int)$xmlconf->BackgroundImageInfo->Width;
			$height = (int)$xmlconf->BackgroundImageInfo->Height;
			PDFRenderer::createPDF($psfiles, $appdir . DIRECTORY_SEPARATOR . 'background.pdf', $width, $height);
		}

		$appconfig = chunk_split(base64_encode(file_get_contents($baseconfig)), 80, "\n");

		$a = $xml->addChild('application');
		$a->addChild('config', $appconfig);

		$cache 	= str_replace('helpers', 'cache', dirname(__FILE__));
		$md5	= md5($basepdf);
		$test	= $cache . '/app_' . $app . '_' . $md5 . '_000.png';
	
		if(!file_exists($test))
		{
			exec("`which convert` -quality 00 -density 150x150 {$basepdf} {$cache}/app_{$app}_{$md5}_%03d.png");
		}

		$p = $a->addChild('pages');
		foreach(glob($cache . '/*_' . $md5 . '_*.png') as $id => $file)
		{
			$page = chunk_split(base64_encode(file_get_contents($file)), 80, "\n");

			$i = $p->addChild('page', $page);
			$i->addAttribute('id', ++$id);
		}
	}

	public function __construct()
	{
		global $appsrv;

		$appsrv = true;
	}
}