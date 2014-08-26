<?php
namespace CHAOS\Harvester\Bonanza\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;

class AssetObjectProcessor extends BasicAssetObjectProcessor {
	
	public function process(&$externalObject, &$shadow = null) {
		/* @var $externalObject \SimpleXMLElement */

		$shadow->extras["AssetId"] = strval($externalObject->AssetId);
		$shadow = $this->initializeShadow($externalObject, $shadow);

		$this->_harvester->process('unpublished-by-curator-processor', $externalObject, $shadow);
		
		// If the unpublished by curator filter was failing ..
		if($shadow->skipped) {
			return $shadow;
		}
		
		//$this->_harvester->process('asset_metadata_dka', $externalObject, $shadow);
		$this->_harvester->process('asset_metadata_dka2', $externalObject, $shadow);
		$this->_harvester->process('asset_metadata_dka_dr', $externalObject, $shadow);
		$this->_harvester->process('asset_file_thumb', $externalObject, $shadow);
		$this->_harvester->process('asset_file_video_high', $externalObject, $shadow);
		$this->_harvester->process('asset_file_video_mid', $externalObject, $shadow);
		$this->_harvester->process('asset_file_video_low', $externalObject, $shadow);
		$this->_harvester->process('asset_file_audio', $externalObject, $shadow);
		
		return $shadow;
	}
	
}