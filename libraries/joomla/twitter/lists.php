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
}
