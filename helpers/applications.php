<?php

function getAppList($xml, $username)
{
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
			}
		}
	}
}
