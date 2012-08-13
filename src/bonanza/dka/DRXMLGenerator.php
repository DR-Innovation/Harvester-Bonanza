<?php
namespace bonanza\dka;
class DRMetadataGenerator extends \ACHAOSMetadataGenerator {
	const SCHEMA_NAME = 'DKA.DR';
	const SCHEMA_GUID = '1221c2dd-3b23-4d27-97b3-ca7bf4720ecb';
	
	public static $singleton;
	
	/**
	 * Generate XML from some import-specific object.
	 * @param unknown_type $object
	 * @param boolean $validate Validate the generated XML agains a schema.
	 * @return DOMDocument Representing the imported item as XML in a specific schema.
	 */
	public function generateXML($input, $validate = false) {
		$asset = $input["asset"];
		$result = new \SimpleXMLElement("<?xml version='1.0' encoding='UTF-8' standalone='yes'?><DR xmlns:dr='http://www.example.org/DKA.DR'></DR>");
		
		$result->addChild("ProductionID", strval($asset->ProductionId));
		$result->addChild("StreamDuration", strval($asset->Duration));
		
		// Generate the DOMDocument.
		$dom = dom_import_simplexml($result)->ownerDocument;
		$dom->formatOutput = true;
		if($validate) {
			$this->validate($dom);
		}
		return $dom;
	}
	
	/**
	 * Sets the schema source fetching it from a chaos system.
	 * @param CHAOS\Portal\Client\PortalClient $chaosClient
	 */
	public function fetchSchema($chaosClient) {
		return $this->fetchSchemaFromGUID($chaosClient, self::SCHEMA_GUID);
	}
}