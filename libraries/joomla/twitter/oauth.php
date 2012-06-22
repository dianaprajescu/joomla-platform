<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * 
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();
jimport('joomla.environment.response');


/**
 * Joomla Platform class for generating Twitter API access token.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * 
 * @since       12.1
 */
class JTwitterOAuth
{
	/**
	* @var array  Contains consumer key and secret for the Twitter application.
	* @since 12.1
	*/
	protected $consumer = array();

	/**
	 * @var array  Contains user access token key and secret.
	 * @since 12.1
	 */
	protected $user_token = array();

	/**
	 * @var array  Contains access token key and secret.
	 * @since 12.1
	 */
	protected $token = array();

	/**
	* @var string  Callback URL for the Twitter application.
	* @since 12.1
	*/
	protected $callback_url;

	/**
	 * @var    JTwitterHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * @var array  Array containg request parameters.
	 */
	protected $parameters = array();

	/**
	 * @var string  The access token URL
	 * @since 12.1
	 */
	protected $accessTokenURL = 'https://api.twitter.com/oauth/access_token';

	/**
	 * @var string  The authenticate URL
	 * @since 12.1
	 */
	protected $authenticateURL = 'https://api.twitter.com/oauth/authenticate';

	/**
	 * @var string  The authorize URL
	 * @since 12.1
	 */
	protected $authorizeURL = 'https://api.twitter.com/oauth/authorize';

	/**
	 * @var string  The request token URL
	 * @since 12.1
	 */
	protected $requestTokenURL = 'https://api.twitter.com/oauth/request_token';

	/**
	* Constructor.
	*
	* @param   string        $consumer_key     Twitter consumer key.
	* @param   string        $consumer_secret  Twitter consumer secret.
	* @param   string        $user_key         Twitter user token key.
	* @param   string        $user_secret      Twitter user token secret used to sign requests.
	* @param   string        $callback_url     Twitter calback URL.
	* @param   JTwitterHttp  $client           The HTTP client object.
	*
	* @since 12.1
	*/
	public function __construct($consumer_key, $consumer_secret, $user_key, $user_secret, $callback_url, JTwitterHttp $client = null)
	{
		$this->consumer = array('key' => $consumer_key, 'secret' => $consumer_secret);
		$this->user_token = array('key' => $user_key, 'secret' => $user_secret);
		$this->callback_url = $callback_url;
		$this->client = isset($client) ? $client : new JTwitterHttp($this->options);
	}

	/**
	 * Method used to get a request token.
	 * 
	 * @return void
	 * 
	 * @since  12.1
	 */
	public function getRequestToken()
	{
		// Set the parameters.
		$this->parameters = array(
			'oauth_callback' => $this->callback_url,
			'oauth_consumer_key' => $this->consumer['key'],
			'oauth_nonce' => $this->generateNonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => time(),
			'oauth_token' => $this->user_token['key'],
			'oauth_version' => '1.0'
		);

		// Make an OAuth request for the Request Token.
		$response = $this->oauthRequest($this->requestTokenURL, 'POST');

		// Validate the response.
		if ($response->code != 200)
		{
			throw new DomainException($response->body);
		}

		parse_str($response->body, $params);
		if ($params['oauth_callback_confirmed'] == true)
		{
			// Save the request token.
			$this->token = array('key' => $params['oauth_token'], 'secret' => $params['oauth_token_secret']);
			$this->parameters['oauth_token'] = $this->token['key'];
		}
	}

	/**
	 * Method used to make an OAuth request.
	 * 
	 * @param   string  $url     The request URL.
	 * @param   string  $method  The request method.
	 * 
	 * @return  object  The JHttpResponse object.
	 * 
	 * @since 12.1
	 */
	public function oauthRequest($url, $method)
	{
		// Sign the request.
		$this->signRequest($url, $method);

		// Send the request.
		switch ($method)
		{
			case 'GET':
				return $this->client->get($this->to_url($url));
			case 'POST':
				return $this->client->post($url, null, array('Authorization' => $this->createHeader()));
		}
	}

	/**
	 * Method used to create the header for the POST request.
	 * 
	 * @return  string  The header.
	 * 
	 * @since 12.1
	 */
	public function createHeader()
	{
		$header = 'OAuth ';

		foreach ($this->parameters as $key => $value)
		{
			if (!strcmp($header, 'OAuth '))
			{
				$header .= $key . '="' . $value . '"';
			}
			else
			{
				$header .= ', ' . $key . '="' . $value . '"';
			}
		}
		return $header;
	}

	/**
	 * Method to create the URL formed string with the parameters.
	 * 
	 * @param   string  $url  The request URL.
	 * 
	 * @return  string  The formed URL.
	 * 
	 * @since  12.1
	 */
	public function to_url($url)
	{
		foreach ($this->parameters as $key => $value)
		{
			if (strpos($url, '?') === false)
			{
				$url .= '?' . $key . '=' . $value;
			}
			else
			{
				$url .= '&' . $key . '=' . $value;
			}
		}

		return $url;
	}

	/**
	 * Method used to sign requests.
	 * 
	 * @param   string  $url     The URL to sign.
	 * @param   string  $method  The request method.
	 * 
	 * @return  void
	 * 
	 * @since   12.1
	 */
	public function signRequest($url, $method)
	{
		// Create the signature base string.
		$base = $this->baseString($url, $method);

		$this->parameters['oauth_signature'] = $this->safeEncode(
			base64_encode(
				hash_hmac('sha1', $base, $this->prepare_signing_key(), true)
				)
			);
	}

	/**
	 * Prepare the signature base string.
	 * 
	 * @param   string  $url     The URL to sign.
	 * @param   string  $method  The request method.
	 *
	 * @return string  The base string.
	 * 
	 * @since 12.1
	 */
	private function baseString($url, $method)
	{
		// Encode parameters.
		foreach ($this->parameters as $key => $value)
		{
			$key = $this->safeEncode($key);
			$value = $this->safeEncode($value);
			$kv[] = "{$key}={$value}";
		}
		// Form the parameter string.
		$params = implode('&', $kv);

		// Signature base string elements.
		$base = array(
			$method,
			$url,
			$params
			);

		// Return the base string.
		return implode('&', $this->safeEncode($base));
	}

	/**
	 * Encodes the string or array passed in a way compatible with OAuth.
	 * If an array is passed each array value will will be encoded.
	 *
	 * @param   mixed  $data  The scalar or array to encode.
	 * 
	 * @return  string  $data encoded in a way compatible with OAuth.
	 * 
	 * @since 12.1
	 */
	private function safeEncode($data)
	{
		if (is_array($data))
		{
			return array_map(array($this, 'safeEncode'), $data);
		}
		elseif (is_scalar($data))
		{
			return str_ireplace(
				array('+', '%7E'),
				array(' ', '~'),
				rawurlencode($data)
				);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Method used to genereate the current nonce.
	 * 
	 * @return  string  The current nonce.
	 * 
	 * @since 12.1
	 */
	private static function generateNonce()
	{
		$mt = microtime();
		$rand = mt_rand();

		// The md5s look nicer than numbers.
		return md5($mt . $rand);
	}

	/**
	 * Prepares the OAuth signing key.
	 *
	 * @return string  The prepared signing key.
	 * 
	 * @since 12.1
	 */
	private function prepare_signing_key()
	{
		return $this->safeEncode($this->consumer['secret']) . '&' . $this->safeEncode(($this->token) ? $this->token['secret'] : '');
	}
}
