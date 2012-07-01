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
 * Twitter API Favorites class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterFavorites extends JTwitterObject
{
	public function getFavorites($oauth, $user = null, $count = 20, $since_id = 0, $max_id = 0, $page = 0, $entities = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base.
		$base = '/1/favorites.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}

		// Set the count string
		$data['count'] = $count;

		// Check if since_id is specified.
		if ($since_id > 0)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id > 0)
		{
			$data['max_id'] = $max_id;
		}

		// Check if page is specified.
		if ($page > 0)
		{
			$data['page'] = $page;
		}

		// Check if entities is true.
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}