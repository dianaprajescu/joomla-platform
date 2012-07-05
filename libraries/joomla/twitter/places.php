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
	 * @param   string  $id  A place in the world. These IDs can be retrieved using getGeocode.
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

		// Send the request.
		return $this->sendRequest($base);
	}

	/**
	 * Method to get up to 20 places that can be used as a place_id when updating a status.
	 *
	 * @param   float    $lat          The latitude to search around.
	 * @param   float    $long         The longitude to search around.
	 * @param   string   $accuracy     A hint on the "region" in which to search. If a number, then this is a radius in meters, but it can also take a string that is suffixed with ft to specify feet.
	 * @param   string   $granularity  This is the minimal granularity of place types to return and must be one of: poi, neighborhood, city, admin or country.
	 * @param   integer  $max_results  A hint as to the number of results to return.
	 * @param   string   $callback     If supplied, the response will use the JSONP format with a callback of the given name.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.1
	 */
	public function getGeocode($lat, $long, $accuracy = null, $granularity = null, $max_results = 0, $callback = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit();

		// Set the API base
		$base = '/1/geo/reverse_geocode.json';

		// Set the request parameters
		$parameters['lat'] = $lat;
		$parameters['long'] = $long;

		// Check if accuracy is specified
		if ($accuracy)
		{
			$parameters['accuracy'] = $accuracy;
		}

		// Check if granularity is specified
		if ($granularity)
		{
			$parameters['granularity'] = $granularity;
		}

		// Check if max_results is specified
		if ($max_results)
		{
			$parameters['max_results'] = $max_results;
		}

		// Check if callback is specified
		if ($callback)
		{
			$parameters['callback'] = $callback;
		}

		// Send the request.
		return $this->sendRequest($base, 'get', $parameters);
	}
}
