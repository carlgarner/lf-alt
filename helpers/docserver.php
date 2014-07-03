<?php

class DocServer
{
	private $user;
	private $path;
	private $xml;
	
	public function startDoc()
	{
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