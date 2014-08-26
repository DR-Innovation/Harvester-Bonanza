<?php
namespace CHAOS\Harvester\Bonanza\Modes;
class ByStartdateMode extends \CHAOS\Harvester\Modes\SetByReferenceMode implements \CHAOS\Harvester\Loadable {
	
	/**
	 * The $reference is here a string in the ISO 8601 date format.
	 * (non-PHPdoc)
	 * @see CHAOS\Harvester\Modes.SetByReferenceMode::execute()
	 */
	public function execute($reference) {
		$this->_harvester->debug(__CLASS__." is executing.");
		
		$chaos = $this->_harvester->getChaosClient();
		/* @var $bonanza \bonanza\BonanzaClient */
		$bonanza = $this->_harvester->getExternalClient('bonanza');
		
		$reference = strtotime($reference);
		
		$m = 1;
		
		$this->_harvester->info("Fetching references to all assets changed since %s.", date("r", $reference));
		$assets = $bonanza->doGetDataByStartdate(date("c", $reference));
		$this->_harvester->info("Found %u assets.", count($assets));
		
		foreach($assets as $asset) {
			printf("[#%u/%u] ", $m++, count($assets));
			$assetShadow = null;
			try {
				$assetShadow = $this->_harvester->process('asset', $asset);
				$assetShadow->commit($this->_harvester);
			} catch(\Exception $e) {
				$this->_harvester->registerProcessingException($e, $asset, $assetShadow);
			}
			print("\n");
		}
	}
}