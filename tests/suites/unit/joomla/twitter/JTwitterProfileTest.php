<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/twitter/twitter.php';
require_once JPATH_PLATFORM . '/joomla/twitter/http.php';
require_once JPATH_PLATFORM . '/joomla/twitter/profile.php';

/**
 * Test class for JTwitterProfile.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.1
 */
class JTwitterProfileTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since  12.1
	 */
	protected $options;

	/**
	 * @var    JTwitterHttp  Mock client object.
	 * @since  12.1
	 */
	protected $client;

	/**
	 * @var    JTwitterProfile  Object under test.
	 * @since  12.1
	 */
	protected $object;

	/**
	 * @var    JTwitterOAuth  Authentication object for the Twitter object.
	 * @since  12.1
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.1
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.1
	 */
	protected $errorString = '{"error":"Generic error"}';

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.1
	 */
	protected $rateLimit = '{"remaining_hits":150, "reset_time":"Mon Jun 25 17:20:53 +0000 2012"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$key = "lIio7RcLe5IASG5jpnZrA";
		$secret = "dl3BrWij7LT04NUpy37BRJxGXpWgjNvMrneuQ11EveE";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/twitter_test.php";

		$this->options = new JRegistry;
		$this->client = $this->getMock('JTwitterHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JTwitterProfile($this->options, $this->client);
		$this->oauth = new JTwitterOAuth($key, $secret, $my_url, $this->client);
		$this->oauth->setToken($key, $secret);
	}

	/**
	 * Tests the updateProfile method
	 *
	 * @return  void
	 *
	 * @since 12.1
	 */
	public function testUpdateProfile()
	{
		$name = 'testUser';
		$url = 'www.example.com/url';
		$location = 'San Francisco, CA';
		$description = 'Flipped my wig at age 22 and it never grew back. Also: I work at Twitter.';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['name'] = $name;
		$data['url'] = $url;
		$data['location'] = $location;
		$data['description'] = $description;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/account/update_profile.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateProfile($this->oauth, $name, $url, $location, $description, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateProfile method - failure
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @expectedException DomainException
	 */
	public function testUpdateProfileFailure()
	{
		$name = 'testUser';
		$url = 'www.example.com/url';
		$location = 'San Francisco, CA';
		$description = 'Flipped my wig at age 22 and it never grew back. Also: I work at Twitter.';
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$data['name'] = $name;
		$data['url'] = $url;
		$data['location'] = $location;
		$data['description'] = $description;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/account/update_profile.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->updateProfile($this->oauth, $name, $url, $location, $description, $entities, $skip_status);
	}

	/**
	 * Tests the updateProfileBackgroundImage method
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function testUpdateProfileBackgroundImage()
	{
		$image = 'path/to/source';
		$tile = true;
		$entities = true;
		$skip_status = true;
		$use = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set POST request parameters.
		$data['image'] = "@{$image}";
		$data['tile'] = $tile;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;
		$data['use'] = $use;

		$this->client->expects($this->once())
			->method('post')
			->with('/1/account/update_profile_background_image.json', $data)
			->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->updateProfileBackgroundImage($this->oauth, $image, $tile, $entities, $skip_status, $use),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the updateProfileBackgroundImage method - failure
	 *
	 * @return  void
	 *
	 * @since   12.1
	 * @expectedException DomainException
	 */
	public function testUpdateProfileBackgroundImageFailure()
	{
		$image = 'path/to/source';
		$tile = true;
		$entities = true;
		$skip_status = true;
		$use = true;

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set POST request parameters.
		$data['image'] = "@{$image}";
		$data['tile'] = $tile;
		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;
		$data['use'] = $use;

		$this->client->expects($this->once())
			->method('post')
			->with('/1/account/update_profile_background_image.json', $data)
			->will($this->returnValue($returnData));

		$this->object->updateProfileBackgroundImage($this->oauth, $image, $tile, $entities, $skip_status, $use);
	}
}
