<?php
namespace CHAOS\Harvester\Bonanza\Modes;
class BasicAllMode extends \CHAOS\Harvester\Modes\AllMode implements \CHAOS\Harvester\Loadable {
	
	public function execute() {
		$this->_harvester->debug(__CLASS__." is executing.");
		
		$chaos = $this->_harvester->getChaosClient();
		/* @var $bonanza \bonanza\BonanzaClient */
		$bonanza = $this->_harvester->getExternalClient('bonanza');
		
		$m = 1;
		
		$this->_harvester->info("Fetching references to all movieclips.");
		$assets = $bonanza->GetEverything();
		$this->_harvester->info("Found %u movieclips.", count($assets));
		
		foreach($assets as $asset) {
			printf("[#%u/%u] ", $m++, count($assets));
			$assetShadow = null;
			try {
				$assetShadow = $this->_harvester->process('asset', $asset);
			} catch(\Exception $e) {
				$this->_harvester->registerProcessingException($e, $asset, $assetShadow);
			}
			print("\n");
		}
	}
}