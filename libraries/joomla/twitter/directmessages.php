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
 * Twitter API Direct Messages class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterDirectMessages extends JTwitterObject
{
	/**
	 * Method to get the most recent direct messages sent to the authenticating user..
	 *
	 * @param   JTwitterOAuth  $oauth        The JTwitterOAuth object.
	 * @param   integer        $since_id     Returns results with an ID greater than (that is, more recent than) the specified ID.
	 * @param   integer        $max_id       Returns results with an ID less than (that is, older than) or equal to the specified ID.
	 * @param   integer        $count        Specifies the number of direct messages to try and retrieve, up to a maximum of 200.
	 * @param   integer        $page         Specifies the page of results to retrieve.
	 * @param   boolean        $entities     When set to true,  each tweet will include a node called "entities,". This node offers a variety of metadata
	 *                                       about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getDirectMessages($oauth, $since_id = 0, $max_id =  0, $count = 20, $page = 0, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/direct_messages.json';

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		// Check if since_id is specified.
		if ($since_id)
		{
			$data['since_id'] = $since_id;
		}

		// Check if max_id is specified.
		if ($max_id)
		{
			$data['max_id'] = $max_id;
		}

		// Check if count is specified.
		if ($count)
		{
			$data['count'] = $count;
		}

		// Check if page is specified.
		if ($page)
		{
			$data['page'] = $page;
		}

		// Check if entities is true.
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_status is true.
		if ($skip_status)
		{
			$data['skip_status'] = $skip_status;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}
