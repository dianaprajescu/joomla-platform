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
require_once JPATH_PLATFORM . '/joomla/twitter/lists.php';

/**
 * Test class for JTwitterFriends.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.1
 */
class JTwitterListsTest extends TestCase
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
	 * @var    JTwitterLists  Object under test.
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
	 * @var    string  Sample JSON Twitter error message.
	 * @since  12.1
	 */
	protected $twitterErrorString = '{"errors":[{"message":"Sorry, that page does not exist","code":34}]}';

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

		$this->object = new JTwitterLists($this->options, $this->client);
		$this->oauth = new JTwitterOAuth($key, $secret, $my_url, $this->client);
		$this->oauth->setToken($key, $secret);
	}

	protected function getMethod($name)
	{
		$class = new ReflectionClass('JTwitterFriends');
		$method = $class->getMethod($name);
		$method->setAccessible(true);
		return $method;
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedUser()
	{
		// User ID or screen name
		return array(
			array(234654235457),
			array('testUser'),
			array(null)
			);
	}

	/**
	 * Tests the getLists method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedUser
	 */
	public function testGetLists($user)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getLists($user);
		}

		$path = $this->object->fetchUrl('/1/lists/all.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getLists($user),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getLists method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetListsFailure($user)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getLists($user);
		}

		$path = $this->object->fetchUrl('/1/lists/all.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getLists($user);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedListStatuses()
	{
		// List ID or slug and owner
		return array(
			array(234654235457, null),
			array('test-list', 'testUser'),
			array('test-list', 12345),
			array('test-list', null),
			array(null, null)
			);
	}

	/**
	 * Tests the getListStatuses method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 */
	public function testGetListStatuses($list, $owner)
	{
		$since_id = 12345;
		$max_id = 54321;
		$per_page = 10;
		$page = 1;
		$entities = true;
		$include_rts = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getListStatuses($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListStatuses($list, $owner);
		}

		$data['since_id'] = $since_id;
		$data['max_id'] = $max_id;
		$data['per_page'] = $per_page;
		$data['page'] = $page;
		$data['include_entities'] = $entities;
		$data['include_rts'] = $include_rts;

		$path = $this->object->fetchUrl('/1/lists/statuses.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListStatuses($list, $owner, $since_id, $max_id, $per_page, $page, $entities, $include_rts),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListStatuses method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testGetListStatusesFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getListStatuses($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListStatuses($list, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/statuses.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getListStatuses($list, $owner);
	}

	/**
	 * Tests the getListMemberships method
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedUser
	 */
	public function testGetListMemberships($user)
	{
		$filter = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListMemberships($user);
		}
		$data['filter_to_owned_lists'] = $filter;

		$path = $this->object->fetchUrl('/1/lists/memberships.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListMemberships($user, $filter),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListMemberships method - failure
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedUser
	 * @expectedException DomainException
	 */
	public function testGetListMembershipsFailure($user)
	{
		$filter = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListMemberships($user);
		}
		$data['filter_to_owned_lists'] = $filter;

		$path = $this->object->fetchUrl('/1/lists/memberships.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getListMemberships($user, $filter);
	}

	/**
	 * Tests the getListSubscribers method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 */
	public function testGetListSubscribers($list, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getListSubscribers($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListSubscribers($list, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/lists/subscribers.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getListSubscribers($list, $owner, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getListSubscribers method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testGetListSubscribersFailure($list, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->getListSubscribers($list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->getListSubscribers($list, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/lists/subscribers.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getListSubscribers($list, $owner, $entities, $skip_status);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedDeleteListMember()
	{
		// List ID or slug, user and owner
		return array(
			array(234654235457, 12345, null),
			array('test-list', 'userTest', 'testUser'),
			array('test-list', 'userTest', 12345),
			array('test-list', 12345, null),
			array('test-list', null, 'testUser'),
			array(null, null, null)
			);
	}

	/**
	 * Tests the deleteListMember method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedDeleteListMember
	 */
	public function testDeleteListMember($list, $user, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->deleteListMember($this->oauth, $list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->deleteListMember($this->oauth, $list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->deleteListMember($this->oauth, $list, $user, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/members/destroy.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->deleteListMember($this->oauth, $list, $user, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the deleteListMember method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedDeleteListMember
	 * @expectedException DomainException
	 */
	public function testDeleteListMemberFailure($list, $user, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->deleteListMember($this->oauth, $list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->deleteListMember($this->oauth, $list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->deleteListMember($this->oauth, $list, $user, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/members/destroy.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->deleteListMember($this->oauth, $list, $user, $owner);
	}

	/**
	 * Tests the subscribe method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 */
	public function testSubscribe($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->subscribe($this->oauth, $list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->subscribe($this->oauth, $list, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/subscribers/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->subscribe($this->oauth, $list, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the subscribe method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testSubscribeFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->subscribe($this->oauth, $list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->subscribe($this->oauth, $list, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/subscribers/create.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->subscribe($this->oauth, $list, $owner);
	}

	/**
	 * Tests the isListMember method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedDeleteListMember
	 */
	public function testIsListMember($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isListMember($this->oauth, $list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isListMember($this->oauth, $list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isListMember($this->oauth, $list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/lists/members/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isListMember($this->oauth, $list, $user, $owner, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isListMember method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedDeleteListMember
	 * @expectedException DomainException
	 */
	public function testIsListMemberFailure($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isListMember($this->oauth, $list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isListMember($this->oauth, $list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isListMember($this->oauth, $list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/lists/members/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->isListMember($this->oauth, $list, $user, $owner, $entities, $skip_status);
	}

	/**
	 * Tests the isListSubscriber method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedDeleteListMember
	 */
	public function testIsListSubscriber($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isListSubscriber($this->oauth, $list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isListSubscriber($this->oauth, $list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isListSubscriber($this->oauth, $list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/lists/subscribers/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->isListSubscriber($this->oauth, $list, $user, $owner, $entities, $skip_status),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the isListSubscriber method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedDeleteListMember
	 * @expectedException DomainException
	 */
	public function testIsListSubscriberFailure($list, $user, $owner)
	{
		$entities = true;
		$skip_status = true;

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->isListSubscriber($this->oauth, $list, $user, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->isListSubscriber($this->oauth, $list, $user, $owner);
		}

		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			$this->setExpectedException('RuntimeException');
			$this->object->isListSubscriber($this->oauth, $list, $user, $owner);
		}

		$data['include_entities'] = $entities;
		$data['skip_status'] = $skip_status;

		$path = $this->object->fetchUrl('/1/lists/subscribers/show.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->isListSubscriber($this->oauth, $list, $user, $owner, $entities, $skip_status);
	}

	/**
	 * Tests the unsubscribe method
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 */
	public function testUnsubscribe($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->unsubscribe($this->oauth, $list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->unsubscribe($this->oauth, $list, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/subscribers/destroy.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->unsubscribe($this->oauth, $list, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the unsubscribe method - failure
	 *
	 * @param   mixed  $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed  $owner  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedListStatuses
	 * @expectedException DomainException
	 */
	public function testUnsubscribeFailure($list, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->unsubscribe($this->oauth, $list, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->unsubscribe($this->oauth, $list, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/subscribers/destroy.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->unsubscribe($this->oauth, $list, $owner);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.1
	*/
	public function seedAddListMembers()
	{
		// List, User ID, screen name and owner.
		return array(
			array(234654235457, null, '234654235457', null),
			array('test-list', null, '234654235457,245864573437', 'testUser'),
			array('test-list', 'testUser', null, null),
			array('test-list', 'testUser', '234654235457', 'userTest'),
			array(null, null, null, null)
			);
	}

	/**
	 * Tests the addListMembers method
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedAddListMembers
	 */
	public function testAddListMembers($list, $user_id, $screen_name, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if($user_id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/members/create_all.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the addListMembers method - failure
	 *
	 * @param   mixed   $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   string  $user_id      A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   string  $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   mixed   $owner        Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  void
	 *
	 * @since 12.1
	 * @dataProvider seedAddListMembers
	 * @expectedException DomainException
	 */
	public function testAddListMembersFailure($list, $user_id, $screen_name, $owner)
	{
		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			if (is_numeric($owner))
			{
				$data['owner_id'] = $owner;
			}
			elseif (is_string($owner))
			{
				$data['owner_screen_name'] = $owner;
			}
			else
			{
				// We don't have a valid entry
				$this->setExpectedException('RuntimeException');
				$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
			}
		}
		else
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
		}

		if ($user_id)
		{
			$data['user_id'] = $user_id;
		}
		if ($screen_name)
		{
			$data['screen_name'] = $screen_name;
		}
		if($user_id == null && $screen_name == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
		}

		$path = $this->object->fetchUrl('/1/lists/members/create_all.json');

		$this->client->expects($this->once())
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->addListMembers($this->oauth, $list, $user_id, $screen_name, $owner);
	}
}
