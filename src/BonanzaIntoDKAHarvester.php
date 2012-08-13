<?php
/**
 * This harvester connects to the API of the DR service Bonanza and
 * copies information on movies into a CHAOS service.
 * It was build to harvest the Bonanza metadata into the CHAOS deployment used for
 * DKA (danskkulturarv.dk).
 *
 * LICENSE: This program is free software: you can redistribute it and/or modify  
 * it under the terms of the GNU Lesser General Public License as published by  
 * the Free Software Foundation, either version 3 of the License, or  
 * (at your option) any later version.  
 *
 * @author     Kræn Hansen (Open Source Shift) for the danish broadcasting corporation, innovations.
 * @license    http://opensource.org/licenses/LGPL-3.0	GNU Lesser General Public License
 * @version    $Id:$
 * @link       https://github.com/CHAOS-Community/Harvester-Bonanza
 * @since      File available since Release 0.1
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
libxml_use_internal_errors();


// Bootstrapping CHAOS - begin 
if(!isset($_SERVER['INCLUDE_PATH'])) {
	die("The INCLUDE_PATH env parameter must be set.");
}
set_include_path(get_include_path() . PATH_SEPARATOR . $_SERVER['INCLUDE_PATH']);
require_once("CaseSensitiveAutoload.php"); // This will be reused by this script.
spl_autoload_extensions(".php");
spl_autoload_register("CaseSensitiveAutoload");
// Bootstrapping CHAOS - end

use CHAOS\Portal\Client\PortalClient;
use bonanza\BonanzaClient;
use bonanza\BonanzaImageExtractor;
use bonanza\BonanzaVideoExtractor;
use bonanza\dka\DKAXMLGenerator;
use bonanza\dka\DKA2XMLGenerator;
use bonanza\dka\DRXMLGenerator;

/**
 * Main class of the Bonanza Harvester.
 *
 * @author     Kræn Hansen (Open Source Shift) for the danish broadcasting corporation, innovations.
 * @license    http://opensource.org/licenses/LGPL-3.0	GNU Lesser General Public License
 * @version    Release: @package_version@
 * @link       https://github.com/CHAOS-Community/Harvester-Bonanza
 * @since      Class available since Release 0.1
 */
class BonanzaIntoDKAHarvester extends ADKACHAOSHarvester {
	
	const VERSION = "0.1";
	const BONANZA_ORGANIZATION_NAME = "Danmarks Radio";
	const RIGHTS_DESCIPTION = "Copyright © Danmarks Radio"; // TODO: Is this correct?
	
	/**
	 * The Bonanza client to be used for communication with the Bonanza Service. 
	 * @var BonanzaClient
	 */
	public $_bonanza;
	
	public function __construct($args) {
		// Adding configuration parameters
		$this->_CONFIGURATION_PARAMETERS["BONANZA_URL"] = "_bonanzaUrl";
		$this->_CONFIGURATION_PARAMETERS["BONANZA_LOGIN"] = "_bonanzaLogin";
		$this->_CONFIGURATION_PARAMETERS["BONANZA_PASSWORD"] = "_bonanzaPassword";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_BONANZA_VIDEO_DESTINATION_ID"] = "_videoDestinationID";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_BONANZA_VIDEO_HIGH_FORMAT_ID"] = "_videoHighFormatID";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_BONANZA_VIDEO_MID_FORMAT_ID"] = "_videoMidFormatID";
		$this->_CONFIGURATION_PARAMETERS["CHAOS_BONANZA_VIDEO_LOW_FORMAT_ID"] = "_videoLowFormatID";
		
		parent::__construct($args);
		
		$this->bonanza_initialize();
	}
	
	/**
	 * The URL of the Bonanza service.
	 * @var string
	 */
	protected $_bonanzaUrl;
	
	/**
	 * The username/login for the Bonanza service.
	 * @var string
	 */
	protected $_bonanzaLogin;
	
	/**
	 * The password for the Bonanza service.
	 * @var string
	 */
	protected $_bonanzaPassword;
	
	protected $_videoDestinationID;
	protected $_videoHighFormatID;
	protected $_videoMidFormatID;
	protected $_videoLowFormatID;
	
	
	protected function fetchRange($start, $count) {
		// Fetch all, limit locally.
		$response = $this->_bonanza->GetEverything();
		if(empty($response)) {
			throw new RuntimeException("Got an empty response from the bonanza service.");
		}
		$all = $response->Asset;
		$result = array();
		for($i = $start; $i < $count; $i++) {
			$result[] = $all[$i];
		}
		return $result;
	}
	
	protected function fetchSingle($reference) {
		$response = $this->_bonanza->GetEverything();
		if(empty($response)) {
			throw new RuntimeException("Got an empty response from the bonanza service.");
		}
		return $response->Asset[$reference];
	}
	
	protected function externalObjectToString($externalObject) {
		return sprintf("%s [%u]", $externalObject->Title, $externalObject->AssetId);
	}
	
	protected function getOrCreateObject($externalObject) {
		if($externalObject == null) {
			throw new RuntimeException("Cannot get or create a CHAOS object from a null external object.");
		}
		$BonanzaId = strval($externalObject->AssetId);
		if(!is_numeric($BonanzaId)) {
			throw new RuntimeException("Cannot get or create a CHAOS object from an external object with a non-nummeric ID.");
		} else {
			$BonanzaId = intval($BonanzaId);
		}
		$folderId = $this->_CHAOSFolderID;
		$objectTypeId = $this->_DKAObjectType->ID;
	}
	
	protected function initializeExtras(&$extras) {
		$extras = array();
	}
	
	public function getExternalClient() {
		return $this->_bonanza;
	}
	
	protected function shouldBeSkipped($externalObject) {
		return false;
	}
	
	/**
	 * Initialized the DFI client by making a simple test to see if the service is advailable.
	 * @throws RuntimeException If the service is unadvailable.
	 */
	protected function bonanza_initialize() {
		printf("Looking up the Bonanza service %s: ", $this->_bonanzaUrl);
		$this->_bonanza = new BonanzaClient($this->_bonanzaUrl, $this->_bonanzaLogin, $this->_bonanzaPassword);
		if(!$this->_bonanza->isServiceAdvailable()) {
			printf("Failed.\n");
			throw new RuntimeException("Couldn't connect to the Bonanza service.");
		} else {
			printf("Succeeded.\n");
		}
		
		BonanzaVideoExtractor::instance()->_videoDestinationID = $this->_videoDestinationID;
		BonanzaVideoExtractor::instance()->_videoHighFormatID = $this->_videoHighFormatID;
		BonanzaVideoExtractor::instance()->_videoMidFormatID = $this->_videoMidFormatID;
		BonanzaVideoExtractor::instance()->_videoLowFormatID = $this->_videoLowFormatID;
	}
}

// Call the main method of the class.
BonanzaIntoDKAHarvester::main($_SERVER['argv']);