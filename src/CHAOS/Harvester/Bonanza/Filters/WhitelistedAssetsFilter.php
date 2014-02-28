<?php
namespace CHAOS\Harvester\Bonanza\Filters;
class WhitelistedAssetsFilter extends \CHAOS\Harvester\Filters\Filter {
	
	protected $_whitelistedProductionIDs = array();

	public function __construct($harvester, $name, $parameters = array()) {
		parent::__construct($harvester, $name, $parameters);
		// Loading the list of production ids that have been banned.
		if(array_key_exists('datafile', $parameters)) {
			$datafile = $parameters['datafile'];
			$datafile = $harvester->resolvePath($datafile);
			if($datafile) {
				$datafile = file_get_contents($datafile);
				$datafile_rows = str_getcsv($datafile, "\n");
				var_dump($datafile_rows);
				exit;
				foreach($datafile_rows as $row) {
					if(strlen($row) != 11) {
						throw new \RuntimeException("Malformed datafile, all rows have to have exact 11 charecters (it was ".strlen($row).").");
					}
					$this->_whitelistedProductionIDs[] = $row;
				}
			} else {
				throw new \Exception("The ".__CLASS__." has to have a datafile parameter that points to a datafile.");
			}
		} else {
			throw new \Exception("The ".__CLASS__." has to have a datafile parameter.");
		}
	}
	
	public function passes($externalObject) {
		$productionID = strval($externalObject->ProductionId);
		return in_array($productionID, $this->_whitelistedProductionIDs) === true;
	}
}