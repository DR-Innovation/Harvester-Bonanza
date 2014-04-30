<?php
namespace CHAOS\Harvester\Bonanza\Filters;
class WhitelistedAssetsFilter extends \CHAOS\Harvester\Filters\Filter {
	
	protected $_whitelistedAssetIDs = array();

	public function __construct($harvester, $name, $parameters = array()) {
		parent::__construct($harvester, $name, $parameters);
		// Loading the list of production ids that have been banned.
		if(array_key_exists('datafile', $parameters)) {
			$datafile = $parameters['datafile'];
			$datafile = $harvester->resolvePath($datafile);
			if($datafile) {
				$datafile = file_get_contents($datafile);
				$datafile_rows = str_getcsv($datafile, "\n");
				array_shift($datafile_rows); // Remove the heading.
				foreach($datafile_rows as $row) {
					/* $row = explode("\t", $row);
					if(count($row) != 4) {
						throw new \RuntimeException("Malformed datafile.");
					}
					$this->_whitelistedAssetIDs[] = $row[1];
					*/
					$asset_id = intval($row);
					$this->_whitelistedAssetIDs[] = $asset_id;
				}
			} else {
				throw new \Exception("The ".__CLASS__." has to have a datafile parameter that points to a datafile.");
			}
		} else {
			throw new \Exception("The ".__CLASS__." has to have a datafile parameter.");
		}
	}
	
	public function passes($externalObject) {
		$assetID = strval($externalObject->AssetId);
		return in_array($assetID, $this->_whitelistedAssetIDs) === true;
	}
}
