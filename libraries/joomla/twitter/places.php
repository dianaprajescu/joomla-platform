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
 * Twitter API Places & Geo class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterPlaces extends JTwitterObject
{
	/**
	 * Method to get all the information about a known place.
	 *
	 * @param   string  $id  A place in the world. These IDs can be retrieved from geo/reverse_geocode.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getPlace($id)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/geo/id/' . $id . '.json';

		// Build the request path.
		$path = $base;

		// Send the request.
		return $this->sendRequest($path);
	}
}
