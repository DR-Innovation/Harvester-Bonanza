<?php
namespace CHAOS\Harvester\Bonanza\Processors;

class ProductionIDPreProcessor extends \CHAOS\Harvester\Processors\PreProcessor {
	
	protected $_translationsByBrokenProductionID = array();
	protected $_translationsByAssetID = array();
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		// Load in the corrections from the datafile.
		if(array_key_exists('datafile', $parameters)) {
			$datafile = $parameters['datafile'];
			$datafile = $harvester->resolvePath($datafile);
			if($datafile) {
				$datafile = file_get_contents($datafile);
				$datafile_rows = str_getcsv($datafile, "\n");
				foreach($datafile_rows as $row) {
					$row = str_getcsv($row, ";");
					if(count($row) != 3) {
						throw new \RuntimeException("Malformed datafile, all rows have to have exact 3 collumns, seperated by semicolons.");
					}
					$brokenProductionID = strval($row[0]);
					$correctProductionID = strval($row[1]);
					$assetID = strval($row[2]);
					if(is_numeric($correctProductionID) && is_numeric($assetID)) {
						$this->_translationsByBrokenProductionID[$brokenProductionID] = $correctProductionID;
						$this->_translationsByAssetID[$assetID] = $correctProductionID;
					} else {
						$harvester->debug("A line in the $name ".__CLASS__." datafile had non-nummeric values: It was skipped.");
					}
				}
			} else {
				throw new \Exception("The ".__CLASS__." has to have a datafile parameter that points to a datafile.");
			}
		} else {
			throw new \Exception("The ".__CLASS__." has to have a datafile parameter.");
		}
	}
	
	public function process(&$externalObject, &$shadow = null) {
		$newProductionID = null;
		
		if(array_key_exists(strval($externalObject->ProductionId), $this->_translationsByBrokenProductionID)) {
			$newProductionID = $this->_translationsByBrokenProductionID[strval($externalObject->ProductionId)];
		} else if(array_key_exists(strval($externalObject->AssetId), $this->_translationsByAssetID)) {
			$newProductionID = $this->_translationsByAssetID[strval($externalObject->AssetId)];
		} else {
			// Abort as this production id is apparently correct already.
			return;
		}
		
		// TODO Check if this even works.
		if($newProductionID != null) {
			$this->_harvester->debug("Correcting production ID '%s' to '%s'", $externalObject->ProductionId, $newProductionID);
			$externalObject->ProductionId = $newProductionID;
		}
	}
}
