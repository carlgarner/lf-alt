<?php

class RegServer
{
	private $user;
	private $path;
	private $xml;

	public function getAppList()
	{
		global $regsrv;
		$regsrv = true;

		$user = UserRepository::getUserHavingName($this->user);
		$orgs = $user->getOrganizationMemberships();
		$apps = ApplicationRepository::getApplicationsForOrganizations($orgs);

		$orga = array();

		foreach($orgs as $org)
		{
			$orga[$org->id] = $org->name;

			$apps = ApplicationRepository::getApplicationsForOrganizations(array($org->id));

			$o = $this->xml->addChild('organisation');
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

	public function __construct($user, $path, $xml)
	{
		$this->user = $user;
		$this->path = $path;
		$this->xml	= $xml;
	}
}
