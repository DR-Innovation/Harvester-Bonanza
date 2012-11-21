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
		parent::__construct($baseURL.'?WSDL');
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
	 * GetDataByCategory wraps a call to BonanzaGetDataByCategory with username and password.
	 * @param integer $categoryId The category to get.
	 */
	public function GetDataByCategory($categoryId = null) {
		$data = array(
			'categoryId' => $categoryId,
			'username' => $this->_username,
			'password' => $this->_password);
		$response = $this->BonanzaGetDataByCategory($data);
		$result = $response->BonanzaGetDataByCategoryResult;
		$xml = simplexml_load_string($result);
		return $xml;
	}
	
	/**
	 * GetDataByCategory wraps a call to BonanzaGetDataByCategory with username and password.
	 * @param integer $categoryId The category to get.
	 * @return \SimpleXMLElement Representing the data of the result.
	 */
	public function GetDataByStartdate($limitDateBegin = null) {
		$data = array(
			'limitDateBegin' => $limitDateBegin,
			'username' => $this->_username,
			'password' => $this->_password);
		$response = $this->BonanzaGetDataByStartdate($data);
		$result = $response->BonanzaGetDataByStartdateResult;
		$xml = simplexml_load_string($result);
		return $xml;
	}
	
	/**
	 * GetDataByCategory wraps a call to BonanzaGetDataByCategory with username and password.
	 * @return \SimpleXMLElement Representing the data of the result.
	 */
	public function GetEverything() {
		return $this->GetDataByStartdate('1753-01-01T00:00:00'); // Earliest valid dataTime.
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
	 * 
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