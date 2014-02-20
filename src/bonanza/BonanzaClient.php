<?php
/**
 * This is a very minimalistic client for the open DFI API.
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author     Kræn Hansen (Open Source Shift) for the danish broadcasting corporation, innovations.
 * @license    http://opensource.org/licenses/LGPL-3.0	GNU Lesser General Public License
 * @version    $Id:$
 * @link       https://github.com/CHAOS-Community/Harvester-DFI
 * @since      File available since Release 0.1
 */

namespace bonanza;

class BonanzaClient extends \SoapClient {
	
	const OPERATION_GET_DATA = "BonanzaGetData";
	
	/** @var string */
	protected $_baseURL;
	protected $_username;
	protected $_password;
	
	/**
	 * Constructs a new DFIClient for communication with the Danish Film Institute open API.
	 * @param string $baseURL 
	 */
	public function __construct($baseURL, $username, $password) {
		// Using HTTP 1.0 will fix the error of broken pipes.
		// See https://bugs.php.net/bug.php?id=60329
		$socket_context = stream_context_create( array('http' => array('protocol_version'  => 1.0) ) );
		//parent::__construct($baseURL.'?WSDL', array('keep_alive' => false)); // We cannot use keep_alive with the Bonanza service when processing takes time.
		parent::__construct($baseURL.'?WSDL', array('stream_context' => $socket_context)); // We cannot use keep_alive with the Bonanza service when processing takes time.
		$this->_baseURL = $baseURL;
		$this->_username = $username;
		$this->_password = $password;
	}
	
	/**
	 * Checks if the DFI service is advailable, by sending a single row request for the movie.service.
	 * @return boolean True if the service call goes through, false if not.
	 */
	public function sanityCheck() {
		$response = $this->__getFunctions();
		return (count($response) == 8);
	}
	
	/**
	 * doGetDataByCategory doGets every asset from a specific category.
	 * @param integer $categoryId The category to doGet.
	 */
	public function doGetDataByCategory($categoryId = null) {
		$data = array(
			'categoryId' => $categoryId,
			'username' => $this->_username,
			'password' => $this->_password);
		$response = $this->GetDataByCategory($data);
		$result = $response->GetDataByCategoryResult;
		$xml = simplexml_load_string($result);
		return $xml;
	}
	
	/**
	 * doGetDataByStartdate doGets every asset from a specific start date.
	 * @param integer $categoryId The category to doGet.
	 * @return \SimpleXMLElement Representing the data of the result.
	 */
	public function doGetDataByStartdate($limitDateBegin = null) {
		if($limitDateBegin instanceof \DateTime) {
			$limitDateBegin = $limitDateBegin->format(DateTime::W3C);
		}
		$data = array(
			'limitDateBegin' => $limitDateBegin,
			'username' => $this->_username,
			'password' => $this->_password);
		$response = $this->GetDataByStartdate($data);
		$result = $response->GetDataByStartdateResult;
		
		libxml_clear_errors();
		$xml = simplexml_load_string($result);
		if(libxml_get_last_error() !== false) {
			printf("Error parsing the response from the service: %s\n", $result);
		}
		return $xml;
	}
	
	public function doGetDataByDates($limitDateBegin, $limitDateEnd) {
		if($limitDateBegin instanceof \DateTime) {
			$limitDateBegin = $limitDateBegin->format(\DateTime::W3C);
		}
		if($limitDateEnd instanceof \DateTime) {
			$limitDateEnd = $limitDateEnd->format(\DateTime::W3C);
		}
		
		$data = array(
			'limitDateBegin' => $limitDateBegin,
			'limitDateEnd' => $limitDateEnd,
			'username' => $this->_username,
			'password' => $this->_password);
		
		$response = $this->GetDataByDates($data);
		$result = $response->GetDataByDatesResult;
		
		libxml_clear_errors();
		$xml = simplexml_load_string($result);
		if(libxml_get_last_error() !== false) {
			printf("Error parsing the response from the service: %s\n", $result);
		}
		return $xml;
	}
	
	/**
	 * doGetEverything wraps a call to BonanzaGetDataByCategory with username and password.
	 * @return \SimpleXMLElement Representing the data of the result.
	 */
	public function doGetEverything() {
		//return $this->doGetDataByStartdate('1753-01-01T00:00:00'); // Earliest valid dataTime - but this was too aggressive for the service.
		return $this->doGetEverythingSlowly();
	}
	
	/**
	 * doGetDataByCategory wraps a call to BonanzaGetDataByCategory with username and password.
	 * @return \SimpleXMLElement Representing the data of the result.
	 */
	public function doGetEverythingSlowly($start = '2010-01-01T00:00:00', $step = 'P1Y') {
		$step = new \DateInterval($step);
		
		$today = new \DateTime();
		$limitDateBegin = new \DateTime($start);
		$limitDateEnd = clone $limitDateBegin;
		$limitDateEnd->add($step);

		$result = array();
		
		do {
			$response = $this->doGetDataByDates($limitDateBegin, $limitDateEnd);
			$limitDateBegin->add($step);
			$limitDateEnd->add($step);
			if($response->count() > 0) {
				printf("Found %u Bonanza Assets in year %s.\n", count($response->Asset), $limitDateBegin->format('Y'));
				foreach($response->Asset as $asset) {
					$result[] = $asset;
				}
			}
		} while($limitDateEnd < $today);
		
		return $result;
	}
	
	/**
	 * Fetches movies from the service.
	 * @param int $startrow The offset in the query.
	 * @param unknown_type $rows The maximal number of movies to fetch.
	 * @throws RuntimeException If it fails to fetch the movies using the given parameters.
	 * @return multitype:SimpleXMLElement An array of movies.
	 */
	/*public function fetchMovies($startrow = 0, $rows = 1000) {
		//echo "fetchMovies called with \$startrow=$startrow and \$rows=$rows\n";
		$response = simplexml_load_file($this->_baseURL.self::LIST_MOVIES."?startrow=$startrow&rows=$rows");
		if($response === false || $response->MovieListItem == null) {
			throw new RuntimeException("Failed to fetch movies using \$startrow=$startrow and \$rows=$rows.");
		} else {
			$result = array();
			foreach($response->MovieListItem as $m) {
				$result[] = $m;
			}
			return $result;
		}
	}*/
	
	/**
	 * Fetches all movies using several calls to the fetchMovies method.
	 * @param int $batchSize How many movies are queried at the same time, maximal 1000.
	 * @param int $delay A non-negative integer specifing the amount of micro seconds to sleep between each call to the API, use this to do a slow fetch.
	 * @throws InvalidArgumentException If the $batchSize is below 1 or above 1000.
	 * @throws RuntimeException If it fails to fetch the movies using the given parameters.
	 * @return multitype:SimpleXMLElement An array of movies.
	 */
	/*public function fetchMultipleMovies($offset = 0, $count = null, $batchSize = 1000, $delay = 0) {
		if($batchSize > 1000) {
			throw new InvalidArgumentException("\$batchSize cannot exceed 1000, as this is not supported by the service anyway");
		} elseif($batchSize < 1) {
			throw new InvalidArgumentException("\$batchSize below 1 makes no sence.");
		}
		$result = array();
		while(true) {
			$partialMovies = $this->fetchMovies($offset, $batchSize);
			if($partialMovies === false) {
				throw new RuntimeException("Failed to fetch movies using \$offset=$offset and \$batchSize=$batchSize.");
			} else if(count($partialMovies) !== 0) {
				// This is not the first response.
				foreach($partialMovies as $m) {
					// @var $c SimpleXMLElement //
					$result[] = $m;
					if($count != null && count($result) >= $count) {
						return $result;
					}
				}
				
				// Increment the offset
				$offset += $batchSize;
			} else {
				return $result;
			}
			
			if($delay > 0) {
				usleep($delay);
			}
		}
	}*/
}