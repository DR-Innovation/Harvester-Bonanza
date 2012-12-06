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
		$movieclips = $bonanza->GetEverything();
		foreach($assets as $asset) {
			printf("[#%u] ", $m++);
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