<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Main extends Controller_Website {

	public function action_index() {}

	public function action_log_out()
	{
		Cookie::delete('auth');
		$this->request->redirect('');
	}

	public function action_log_in_with_facebook()
	{
		$config = Kohana::config('facebook');

		if ($this->request->query('code'))
		{
			// User sent back with a code
			$url = $config->oauth_token;
			$query = array(
				'client_id' => $config->app_id,
				'client_secret' => $config->app_secret,
				'redirect_uri' => URL::site($this->request->uri()),
				'code' => $this->request->query('code'),
			);

			$url = $url.'?'.http_build_query($query);

			$http = new HTTPRequest($url, HTTPRequest::METH_GET);
			$response = $http->send();

			$user = new Model_User;
			$user->create_from_facebook_token($response->body);

			// Log the user in
			Cookie::set('auth', $user->_id);
			$this->request->redirect('');
		}
		else
		{
			$url = $config->oauth_dialog;
			$query = array(
				'client_id' => $config->app_id,
				'redirect_uri' => URL::site($this->request->uri()),
				'scope' => 'offline_access,email',
			);

			$this->request->redirect($url.'?'.http_build_query($query));
		}
	}

	public function action_log_in_with_github()
	{
		$config = Kohana::config('github');

		if ($this->request->query('code'))
		{
			// User sent back with a code
			$url = $config->oauth_token;
			$query = array(
				'client_id' => $config->client_id,
				'client_secret' => $config->secret,
				'redirect_uri' => URL::site($this->request->uri()),
				'code' => $this->request->query('code'),
			);

			$url = $url.'?'.http_build_query($query);

			$http = new HTTPRequest($url, HTTPRequest::METH_GET);
			$response = $http->send();

			$user = new Model_User;
			$user->create_from_github_token($response->body);

			// Log the user in
			Cookie::set('auth', $user->_id);
			$this->request->redirect('');
		}
		else
		{
			$url = $config->oauth_dialog;
			$query = array(
				'client_id' => $config->client_id,
				'redirect_uri' => URL::site($this->request->uri()),
			);
//echo Debug::vars($query);die;
			$this->request->redirect($url.'?'.http_build_query($query));
		}
	}
}
