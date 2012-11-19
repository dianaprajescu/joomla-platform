<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector for the JOauth1aClient class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  OAuth
 *
 * @since       12.2
 */
class JOauth1aClientInspector extends JOauth1aClient
{
	/**
	 * Mimic verifing credentials.
	 *
	 * @return void
	 *
	 * @since 12.2
	 */
	public function verifyCredentials()
	{
		if (!strcmp($this->token['key'], 'valid'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to validate a response.
	 *
	 * @param   string         $url       The request URL.
	 * @param   JHttpResponse  $response  The response to validate.
	 *
	 * @return  void
	 *
	 * @since  12.2
	 * @throws DomainException
	 */
	public function validateResponse($url, $response)
	{
		if ($response->code < 200 || $response->code >399)
		{
				throw new DomainException($response->body);
		}
	}
}
