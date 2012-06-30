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
 * Twitter API Users class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterUsers extends JTwitterObject
{
	/**
	 * Method to get up to 100 users worth of extended information, specified by either ID, screen name, or combination of the two.
	 *
	 * @param   string   $screen_name  A comma separated list of screen names, up to 100 are allowed in a single request.
	 * @param   string   $id           A comma separated list of user IDs, up to 100 are allowed in a single request.
	 * @param   boolean  $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a variety of
	 * 								   metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getUsersLookup($screen_name = null, $id = null, $entities = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set user IDs and screen names.
		if ($id)
		{
			$parameters['user_id'] = $id;
		}
		if ($screen_name)
		{
			$parameters['screen_name'] = $screen_name;
		}
		if ($id == null && $screen_name == null)
		{
			// We don't have a valid entry
			throw new RuntimeException('You must specify either a comma separated list of screen names, user IDs, or a combination of the two');
		}

		// Set the API base
		$base = '/1/users/lookup.json';

		// Check if string_ids is true
		if ($entities)
		{
			$parameters['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($base, 'post', $parameters);
	}
}
