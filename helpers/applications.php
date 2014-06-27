<?php

function getAppList($xml, $username)
{
	require_once '/opt/dpp-registry/src/entities/classes.php';

	$user = UserRepository::getUserHavingName($username);
	$orgs = $user->getOrganizationMemberships();
	$apps = ApplicationRepository::getApplicationsForOrganizations($orgs);

	$orga = array();

	foreach($orgs as $org)
	{
		$orga[$org->id] = $org->name;

		$apps = ApplicationRepository::getApplicationsForOrganizations(array($org->id));

		$o = $xml->addChild('organisation');
		$o->addAttribute('id', $org->id);
		$o->addAttribute('name', $org->name);

		foreach($apps as $app)
		{
			if($app->enabled == 1)
			{
				$a = $o->addChild('app');
				$a->addChild('id', $app->id);
				$a->addChild('name', htmlentities($app->name));
				$a->addChild('pages', $app->pages);
				$a->addChild('description', htmlentities($app->description));
				$a->addChild('created', $app->created);
				$a->addChild('modified', $app->modified);
				$a->addChild('version', $app->version);
			}
		}
	}
}

function getDefinition($xml, $app = false)
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
		require_once '/opt/dpp-appserver/src/ApplicationInstaller.php';

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

	$temp = tempnam('/tmp', 'app_' . $app);
	@unlink($temp);
	@mkdir($temp);

	$a = $xml->addChild('application');
	$a->addChild('config', $appconfig);

	exec("`which convert` -quality 00 -density 150x150 {$basepdf} {$temp}/page_%d.png");

	$p = $a->addChild('pages');
	foreach(glob($temp . '/*.png') as $id => $file)
	{
		$page = chunk_split(base64_encode(file_get_contents($file)), 80, "\n");

		$i = $p->addChild('page', $page);
		$i->addAttribute('id', ++$id);
	}

	exec("rm -rf {$temp}");
}
