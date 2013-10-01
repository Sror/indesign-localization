<?php

class DocInfo{
	//DocInfo
	public $Pages = 0;
	public $Name = "";
	public $Width = 0;
	public $Height = 0;
	public function getPages()
	{
		return $this->Pages;
	}
	public function setPages($Pages)
	{
		$this->Pages =$Pages;
	}
	public function getName()
	{
		return $this->Name;
	}
	public function setName($Name)
	{
		return $this->Name =$Name;
	}
	public function getWidth()
	{
		return $this->Width;
	}
	public function setWidth($Width)
	{
		return $this->Width =$Width;
	}
	public function getHeight()
	{
		return $this->Height;
	}
	public function setHeight($Height)
	{
		return $this->Height =$Height;
	}
	
}

?>