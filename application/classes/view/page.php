<?php defined('SYSPATH') or die('No direct script access.');

abstract class View_Page extends View_Layout {

	public $title = 'Default Page Title';

	public function i18n()
	{
		return function($string)
		{
			return __($string);
		};
	}

	public function _initialize()
	{
		Assets::add_group('default-template');
		parent::_initialize();
	}

	public function title()
	{
		return __($this->title);
	}

	public function notices()
	{
		$data = array();

		foreach (Notices::get() as $array)
		{
			$message_path = $array['type'].'.'.$array['key'];
			$data[] = array
			(
				'type'     => $array['type'],
				'key'      => $array['key'],
				'message'  => Kohana::message('notices', $message_path, $message_path),
			);
		}

		return $data;
	}

	public function log_out_url()
	{
		return Route::url('default', array(
			'controller' => 'main',
			'action'     => 'log_out',
		));
	}

	public function log_in_urls()
	{
		return array(
			'facebook' => Route::url('default', array(
				'controller' => 'main',
				'action'     => 'log_in_with_facebook',
			)),
		);
	}

	public function profiler()
	{
		return View::factory('profiler/stats');
	}

	public function assets_head()
	{
		$assets = '';
		foreach (Assets::get('head') as $asset)
		{
			$assets .= $asset."\n";
		}

		return $assets;
	}

	public function assets_body()
	{
		$assets = '';
		foreach (Assets::get('body') as $asset)
		{
			$assets .= $asset;
		}

		return $assets;
	}

	public function render($template = null, $view = null, $partials = null)
	{
		$content = parent::render($template, $view, $partials);

		return str_replace(array
		(
			'[[assets_head]]',
			'[[assets_body]]'
		), array
		(
			$this->assets_head(),
			$this->assets_body()
		), $content);
	}
}
