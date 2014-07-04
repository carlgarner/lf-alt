<?php

class DocServer
{
	private $user;
	private $path;
	private $xml;
	
	public function startDoc()
	{
		global $appsrv;
		$appsrv = true;

		if(!isset($this->path['appid']))
		{
			$this->xml->addChild('error', 'ID not given');
			return;
		}

		$app 	= null
		$appid	= null;
		$appid	= intval($this->path['appid']);

		if(!is_int($appid))
		{
			$this->xml->addChild('error', 'ID value is not a number');
			return;
		}
		
		try
		{
			$appFactory = new ApplicationFactory();
			$app		= $appFactory->createApplication($appid);
		}
		catch(Exception $e)
		{
			$this->xml->addChild('error', $e->getMessage());
			return;
		}
		
		$doc = Document::create('0.0.0.1', '0.0.0.' . $app->numberOfPages, $app->id, $this->user);
		$doc->currentChangeset->submitterEmail = $this->user;
        $doc->currentChangeset->submitterPhone = "-";
        $doc->name = $name;
        $doc->modifiedBy = $this->user;
        $doc->addFieldsFromConfig($app);
		
		$doc->save(Document::STATE_NEW);
		
		$this->xml->addChild('docid', $doc->id);
	}
	
	public function getDoc()
	{
	}
	
	public function updateDoc()
	{
	}
	
	public function __construct($user, $path, $xml)
	{
		$this->user = $user;
		$this->path = $path;
		$this->xml	= $xml;
	}
}