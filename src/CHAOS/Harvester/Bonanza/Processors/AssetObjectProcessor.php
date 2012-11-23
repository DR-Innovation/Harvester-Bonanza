<?php
namespace CHAOS\Harvester\Bonanza\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;
use CHAOS\Harvester\Shadows\SkippedObjectShadow;

class AssetObjectProcessor extends \CHAOS\Harvester\Processors\ObjectProcessor implements \CHAOS\Harvester\Loadable {
	
	public function __construct($harvester, $name, $parameter = null) {
		$this->_harvester = $harvester;
		$this->_harvester->debug("A ".__CLASS__." named '$name' was constructing.");
	}
	
	protected function generateQuery($externalObject) {
		$legacyQuery = sprintf('(DKA-Organization:"%s" AND ObjectTypeID:%u AND m00000000-0000-0000-0000-000063c30000_da_all:"%s")', 'DR', $this->_objectTypeId, strval($externalObject->AssetId));
		$newQuery = sprintf('(FolderTree:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, strval($externalObject->AssetId));
		return sprintf("(%s OR %s)", $legacyQuery, $newQuery);
	}
	
	public function process($externalObject, $shadow = null) {
		$this->_harvester->debug(__CLASS__." is processing.");
		
		/* @var $externalObject \SimpleXMLElement */
		
		$this->_harvester->info("Processing '%s' #%d", $externalObject->Title, $externalObject->AssetId);
		
		$shadow = new ObjectShadow();
		$shadow = $this->initializeShadow($shadow);
		$shadow->extras["AssetId"] = strval($externalObject->AssetId);
		
		$shadow->query = $this->generateQuery($externalObject);
		var_dump($externalObject);
		$shadow = $this->_harvester->process('asset_metadata_dka', $externalObject, $shadow);
		$shadow = $this->_harvester->process('asset_metadata_dka2', $externalObject, $shadow);
		$shadow = $this->_harvester->process('asset_metadata_dka_dr', $externalObject, $shadow);
		$shadow = $this->_harvester->process('asset_file_thumb', $externalObject, $shadow);
		$shadow = $this->_harvester->process('asset_file_video_high', $externalObject, $shadow);
		$shadow = $this->_harvester->process('asset_file_video_mid', $externalObject, $shadow);
		$shadow = $this->_harvester->process('asset_file_video_low', $externalObject, $shadow);
		
		var_dump($shadow);
		exit;
		
		$shadow->commit($this->_harvester);
		
		return $shadow;
	}
	
	function skip($externalObject, $shadow = null) {
		$shadow = new SkippedObjectShadow();
		$shadow = $this->initializeShadow($shadow);
		$shadow->query = $this->generateQuery($externalObject);
		
		$shadow->commit($this->_harvester);
		
		return $shadow;
	}
}