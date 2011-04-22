<?php defined('SYSPATH') or die('No direct script access.');

class View_Page_Main_Index extends View_Page {

	public $title = 'OAuth + CouchDB Stuff';

	public function logged_in()
	{
		return $this->loaded();
	}

	public function github()
	{
		return $this->auth->github;
	}

	public function facebook()
	{
		return $this->auth->facebook;
	}
}
