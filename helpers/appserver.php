<?php

class Appserver
{
	private $user;
	private $path;
	private $xml;
	
	public function getFormList()
	{
		global $appsrv;
		$appsrv = true;
		$limit	= '';

		if(isset($this->path['appid']))
		{
			$app = intval($this->path['appid']);

			if($app == $this->path['appid'])
			{
				$limit = 'AND Doc.app_id = ' . $app;
			}
		}
		
		$dbFactory = new DBFactory();
		$db = $dbFactory->createDB();
		
		$query = 'SELECT Doc.id, Doc.name, Doc.status, Doc.app_id, MAX(Changeset.created_date) as modified_date, Doc.base_address
					FROM Doc
						LEFT JOIN Changeset ON Doc.id = Changeset.doc_id
							WHERE submitter_email = "' . $this->user . '"
								' . (($limit) ? $limit : '') . '
								AND Doc.base_address = "0.0.0.1"
									GROUP BY Doc.id
									ORDER BY Changeset.created_date DESC';
		$db->query($query);

		while($row = $db->fetchRow()) 
		{
			$doc = $this->xml->addChild('doc');
			$doc->addChild('id', $row['id']);
			$doc->addChild('appid', $row['app_id']);
			$doc->addChild('name', $row['name']);
			$doc->addChild('address', $row['base_address']);
			$doc->addChild('last_modified', gmdate('Y-m-d H:i:s', strtotime($row['modified_date'])));
		}
	}
	
	public function getDefinition()
	{
		global $appsrv;
		$appsrv = true;

		if(!isset($this->path['appid']))
		{
			$this->xml->addChild('error', 'App ID not given');
			return;
		}

		$app = null;
		$app = intval($this->path['appid']);

		if(!is_int($app))
		{
			$this->xml->addChild('error', 'App ID value is not a number');
			return;
		}

		$baseconfig = '/var/opt/dpp-appserver/apps/' . $app . '/config.xml';
		$basepdf	= '/var/opt/dpp-appserver/apps/' . $app . '/background.pdf';

		if(!is_file($baseconfig))
		{
			try
			{
				$ai = new ApplicationInstaller();
				$ai->downloadApp($app, 0);
			}
			catch(Exception $e)
			{
				$this->xml->addChild('error', $e->getMessage());
				return;
			}
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

		$a = $this->xml->addChild('application');
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

	public function __construct($user, $path, $xml)
	{
		$this->user = $user;
		$this->path = $path;
		$this->xml	= $xml;
	}
}
