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
 * Twitter API Profile class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterProfile extends JTwitterObject
{
	/**
	 * Method to et values that users are able to set under the "Account" tab of their settings page.
	 *
	 * @param   JTwitterOAuth  $oauth        The JTwitterOAuth object.
	 * @param   string         $name         Full name associated with the profile. Maximum of 20 characters.
	 * @param   string         $url          URL associated with the profile. Will be prepended with "http://" if not present. Maximum of 100 characters.
	 * @param   string         $location     The city or country describing where the user of the account is located. The contents are not normalized or geocoded in any way. Maximum of 30 characters.
	 * @param   string         $description  A description of the user owning the account. Maximum of 160 characters.
	 * @param   boolean        $entities     When set to either true, t or 1, each tweet will include a node called "entities,". This node offers a
	 * 								  		 variety of metadata about the tweet in a discreet structure, including: user_mentions, urls, and hashtags.
	 * @param   boolean        $skip_status  When set to either true, t or 1 statuses will not be included in the returned user objects.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function updateProfile($oauth, $name = null, $url = null, $location = null, $description = null, $entities = false, $skip_status = false)
	{
		// Set parameters.
		$parameters = array('oauth_token' => $oauth->getToken('key'));

		$data = array();

		// Check if name is specified.
		if ($name)
		{
			$data['name'] = $name;
		}

		// Check if url is specified.
		if ($url)
		{
			$data['url'] = $url;
		}

		// Check if location is specified.
		if ($location)
		{
			$data['location'] = $location;
		}

		// Check if description is specified.
		if ($description)
		{
			$data['description'] = $description;
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

		// Set the API base
		$base = '/1/account/update_profile.json';

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'POST', $parameters, $data);
		return json_decode($response->body);
	}
}