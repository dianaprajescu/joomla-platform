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
 * Twitter API Block class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterBlock extends JTwitterObject
{
	/**
	 * Method to get the top 10 trending topics for a specific WOEID, if trending information is available for it.
	 *
	 * @param   JTwitterOAuth  $oauth         The JTwitterOAuth object.
	 * @param   integer        $page          Specifies the page of results to retrieve.
	 * @param   integer        $per_page      Specifies the number of results to retrieve per page.
	 * @param   boolean        $entities      When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a variety of metadata
	 * 										  about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $skip_statuse  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getBlocking($oauth, $page = 0, $per_page = 0, $entities = false, $skip_status = false)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		$data = array();

		// Check if page is specified
		if ($page > 0)
		{
			$data['page'] = $page;
		}

		// Check if per_page is specified
		if ($per_page > 0)
		{
			$data['per_page'] = $per_page;
		}

		// Check if entities is specified
		if ($entities)
		{
			$data['include_entities'] = $entities;
		}

		// Check if skip_statuses is specified
		if ($skip_status)
		{
			$data['skip_status'] = $skip_status;
		}

		// Set the API base
		$base = '/1/blocks/blocking.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}
