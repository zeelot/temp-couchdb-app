<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Model {

	protected $_db = 'temp-couch-app';

	protected $_document = array();

	public function find($id)
	{
		$http = new HTTPRequest('http://dev.vm:5984/'.$this->_db.'/'.$id, HTTPRequest::METH_GET);
		$response = $http->send();

		if ($response->responseCode === 200)
		{
			$this->_document = json_decode($response->body, TRUE);
		}
		else
		{
			// Clear the object
			$this->_document = array();
		}

		return $this;
	}

	public function find_by_email($email)
	{
		$uri = '/_design/users/_view/find_by_email?key="'.$email.'"';
		$http = new HTTPRequest('http://dev.vm:5984/'.$this->_db.$uri, HTTPRequest::METH_GET);
		$response = $http->send();

		$data = json_decode($response->body, TRUE);

		if ($data['total_rows'] > 0)
		{
			$this->find($data['rows'][0]['id']);
		}
		else
		{
			// Clear the object
			$this->_document = array();
		}

		return $this;
	}

	public function loaded()
	{
		return (Arr::get($this->_document, '_id') !== NULL);
	}

	public function has_oauth_group($group)
	{
		return Arr::get($this->_document, $group) !== NULL;
	}

	public function add(array $data)
	{
		// Merge Current Data
		$data = array_merge($this->_document, $data);

		$couch_req = new HTTPRequest('http://dev.vm:5984/'.$this->_db, HTTPRequest::METH_POST);
		$couch_req->setBody(json_encode($data));
		$couch_req->setContentType('application/json');

		$response = $couch_req->send();
		$meta = json_decode($response->body, TRUE);

		// Merge the meta data from couch
		$data += array(
			'_id'  => $meta['id'],
			'_rev' => $meta['rev'],
		);

		$this->_document = $data;

		return $this;
	}

	public function create_from_facebook_token($token)
	{
		$config = Kohana::config('facebook');

		$http = new HTTPRequest($config->graph_url.'me?'.$token, HTTPRequest::METH_GET);
		$response = $http->send();
		$user = json_decode($response->body, TRUE);

		// Try finding this user before creating a document for him
		$search = $this->find_by_email(Arr::get($user, 'email'));
		if ($search->loaded() AND $search->has_oauth_group('facebook'))
			return $search;

		// Builds the data to store into CouchDB
		$data = array(
			'facebook' => array(
				'api_token'  => $token,
				'id'         => Arr::get($user, 'id'),
				'username'   => Arr::get($user, 'username'),
				'first_name' => Arr::get($user, 'first_name'),
				'last_name'  => Arr::get($user, 'last_name'),
				'link'       => Arr::get($user, 'link'),
				'email'      => Arr::get($user, 'email'),
			),
		);

		return $this->add($data);
	}

	public function create_from_github_token($token)
	{
		$config = Kohana::config('github');

		$http = new HTTPRequest($config->api_url.'user/show?'.$token, HTTPRequest::METH_GET);
		$response = $http->send();
		$user = Arr::get(json_decode($response->body, TRUE), 'user');

		// Try finding this user before creating a document for him
		$search = $this->find_by_email(Arr::get($user, 'email'));
		if ($search->loaded() AND $search->has_oauth_group('github'))
			return $search;

		// Builds the data to store into CouchDB
		$data = array(
			'github' => array(
				'api_token' => $token,
				'id'        => Arr::get($user, 'id'),
				'login'     => Arr::get($user, 'login'),
				'name'      => Arr::get($user, 'name'),
				'email'     => Arr::get($user, 'email'),
			),
		);

		return $this->add($data);
	}

	public function __get($key)
	{
		return $this->_document[$key];
	}
}
