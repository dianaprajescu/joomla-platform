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
 * Twitter API Trends class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 * @since       12.1
 */
class JTwitterTrends extends JTwitterObject
{
	/**
	 * Method to get the top 10 trending topics for a specific WOEID, if trending information is available for it.
	 *
	 * @param   integer  $woeid    The Yahoo! Where On Earth ID of the location to return trending information for. Global information is available by using 1 as the WOEID.
	 * @param   string   $exclude  Setting this equal to hashtags will remove all hashtags from the trends list.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getTrends($woeid, $exclude = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/trends/' . $woeid . '.json';

		// Check if exclude is specified
		if ($exclude)
		{
			$parameters['exclude'] = $exclude;
		}

		// Send the request.
		return $this->sendRequest($base);
	}

	/**
	 * Method to get the locations that Twitter has trending topic information for.
	 *
	 * @param   float    $lat          The latitude to search around.
	 * @param   float    $long         The longitude to search around.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getLocations($lat = null, $long = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/trends/available.json';

		// Check if lat is specified
		if ($lat)
		{
			$parameters['lat'] = $lat;
		}

		// Check if long is specified
		if ($long)
		{
			$parameters['long'] = $long;
		}

		// Send the request.
		return $this->sendRequest($base);
	}
}