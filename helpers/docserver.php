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

		$app 	= null;
		$appid	= null;
		$appid	= intval($this->path['appid']);

		if($appid != $this->path['appid'])
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
		global $appsrv;
		$appsrv	= true;
		$doc	= null;
		$docid	= intval($this->path['docid']);
		
		if(!isset($this->path['docid']))
		{
			$this->xml->addChild('error', 'ID not given');
			return;
		}
		
		if($docid != $this->path['docid'])
		{
			$this->xml->addChild('error', 'ID value is not a number');
			return;
		}
		
		try
		{
			$doc = Document::load(Document::BY_ID, $docid);
		}
		catch(Exception $e)
		{
			$this->xml->addChild('error', $e->getMessage());
			return;
		}
		
		if(is_null($doc))
		{
			$this->xml->addChild('error', 'A document with the ID ' . $docid . ' was not found or could not be loaded');
			return;
		}
		
		foreach($doc->pages as $pagenum => $page)
		{
			$p = $this->xml->addChild('page');
			$p->addAttribute('number', ++$pagenum);
			
			foreach($page->fields as $field)
			{
				$f = $p->addChild('field', htmlspecialchars($field->value));
				$f->addChild('name', $field->key);
			}
		}
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
