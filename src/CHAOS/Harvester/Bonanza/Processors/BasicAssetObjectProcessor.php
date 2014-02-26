<?php
namespace CHAOS\Harvester\Bonanza\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;

class BasicAssetObjectProcessor extends \CHAOS\Harvester\Processors\ObjectProcessor {
	
	protected function generateQuery($externalObject) {
		//$legacyQuery = sprintf('(DKA-Organization:"%s" AND ObjectTypeID:%u AND m00000000-0000-0000-0000-000063c30000_da_all:"%s")', 'DR', $this->_objectTypeId, strval($externalObject->AssetId));
		$newQuery = sprintf('(FolderID:%u AND ObjectTypeID:%u AND DKA-ExternalIdentifier:"%s")', $this->_folderId, $this->_objectTypeId, strval($externalObject->AssetId));
		//return sprintf("(%s OR %s)", $legacyQuery, $newQuery);
		return $newQuery;
	}
	
	public function process(&$externalObject, &$shadow = null) {
		/* @var $externalObject \SimpleXMLElement */
		
		$this->_harvester->info("Processing '%s' #%d", $externalObject->Title, $externalObject->AssetId);
		
		$shadow = new ObjectShadow();
		if($externalObject->AssetType == 'Video') {
			$shadow = $this->_harvester->process('asset_video', $externalObject, $shadow);
		} elseif ($externalObject->AssetType == 'Audio' || $externalObject->AssetType == 'Radio') {
			$shadow = $this->_harvester->process('asset_radio', $externalObject, $shadow);
		} else {
			throw new \RuntimeException(sprintf("Incountered an unknown AssetType '%s'.", strval($externalObject->AssetType)));
		}
		
		$shadow->commit($this->_harvester);
		
		return $shadow;
	}
	
	/*
	function skip($externalObject, &$shadow = null) {
		$shadow = new ObjectShadow();
		$shadow->skipped = true;
		$shadow = $this->initializeShadow($shadow);
		$shadow->query = $this->generateQuery($externalObject);
		
		$shadow->commit($this->_harvester);
		
		return $shadow;
	}
	*/
}