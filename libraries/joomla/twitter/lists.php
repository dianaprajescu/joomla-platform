<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Twitter API Lists class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterLists extends JTwitterObject
{
	/**
	 * Method to get all lists the authenticating or specified user subscribes to, including their own.
	 *
	 * @param   mixed  $user  Either an integer containing the user ID or a string containing the screen name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getLists($user)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$parameters['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$parameters['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/all.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}

	/**
	 * Method to get tweet timeline for members of the specified list
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   integer  $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer  $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   integer  $per_page     Specifies the number of results to retrieve per page.
	 * @param   integer  $page         Specifies the page of results to retrieve.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a variety
	 * 								   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $include_rts  When set to either true, t or 1, the list timeline will contain native retweets (if they exist) in addition
	 * 								   to the standard stream of tweets.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getListStatuses($list, $owner = null, $since_id = 0, $max_id = 0, $per_page = 0, $page = 0, $entities = false, $include_rts = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
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
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/statuses.json';

		// Check if since_id is specified
		if ($since_id > 0)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified
		if ($max_id > 0)
		{
			$data['max_id'] = $max_id;
		}

		// Check if per_page is specified
		if ($per_page > 0)
		{
			$data['per_page'] = $per_page;
		}

		// Check if page is specified
		if ($page > 0)
		{
			$data['page'] = $page;
		}

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if include_rts is true
		if ($include_rts > 0)
		{
			$data['include_rts'] = $include_rts;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $data);

	}

	/**
	 * Method to get the lists the specified user has been added to.
	 *
	 * @param   mixed    $user    Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $filter  When set to true, t or 1, will return just lists the authenticating user owns, and the user represented
	 * 							  by user_id or screen_name is a member of.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getListMemberships($user, $filter = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		$data = array();

		// Determine which type of data was passed for $user
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
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Check if filter is true.
		if ($filter)
		{
			$data['filter_to_owned_lists'] = $filter;
		}

		// Set the API base
		$base = '/1/lists/memberships.json';

		// Send the request.
		return $this->sendRequest($base, 'get', $data);
	}

	/**
	 * Method to get the subscribers of the specified list.
	 *
	 * @param   mixed    $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed    $owner        Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a variety
	 * 								   of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean  $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getListSubscribers($list, $owner = null, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
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
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/subscribers.json';

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status > 0)
		{
			$data['skip_status'] = $skip_status;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $data);

	}

	/**
	 * Method to remove the specified member from the list. The authenticated user must be the list's owner to remove members from the list..
	 *
	 * @param   JTwitterOAuth  $oauth  The JTwitterOAuth object.
	 * @param   mixed          $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $user   Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed          $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function deleteListMember($oauth, $list, $user, $owner = null)
	{
		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
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
				throw new RuntimeException('The specified username for owner is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
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
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/members/destroy.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to subscribe the authenticated user to the specified list.
	 *
	 * @param   JTwitterOAuth  $oauth  The JTwitterOAuth object.
	 * @param   mixed          $list   Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $owner  Either an integer containing the user ID or a string containing the screen name of the owner.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function subscribe($oauth, $list, $owner = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
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
				throw new RuntimeException('The specified username for owner is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/subscribers/create.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to check if the specified user is a member of the specified list.
	 *
	 * @param   JTwitterOAuth  $oauth        The JTwitterOAuth object.
	 * @param   mixed          $list         Either an integer containing the list ID or a string containing the list slug.
	 * @param   mixed          $user         Either an integer containing the user ID or a string containing the screen name of the user to remove.
	 * @param   mixed          $owner        Either an integer containing the user ID or a string containing the screen name of the owner.
	 * @param   boolean        $entities     When set to either true, t or 1, each tweet will include a node called "entities". This node offers a
	 * 										 variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function isListMember($oauth, $list, $user, $owner = null, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Determine which type of data was passed for $list
		if (is_numeric($list))
		{
			$data['list_id'] = $list;
		}
		elseif (is_string($list))
		{
			$data['slug'] = $list;

			// In this case the owner is required.
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
				throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
			}
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified list is not in the correct format; must use integer or string');
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
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API base
		$base = '/1/lists/members/show.json';

		// Check if entities is true
		if ($entities > 0)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true
		if ($skip_status > 0)
		{
			$data['skip_status'] = $skip_status;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		if (property_exists($response, 'body'))
		{
			return json_decode($response->body);
		}
		return $response;
	}
}
