<?php
namespace CHAOS\Harvester\Bonanza\Modes;
class BasicAllMode extends \CHAOS\Harvester\Modes\AllMode implements \CHAOS\Harvester\Loadable {
	
	public function execute() {
		$this->_harvester->debug(__CLASS__." is executing.");
		
		$chaos = $this->_harvester->getChaosClient();
		/* @var $bonanza \bonanza\BonanzaClient */
		$bonanza = $this->_harvester->getExternalClient('bonanza');
		
		$a = 1;
		$assets_count = 0;
		
		$this->_harvester->info("Fetching references to all assets.");

		// $assets = $bonanza->GetEverything();
		// This is too heavy on memory - We need to parse the results as they come in.
		// $this->_harvester->info("Found %u assets.", count($assets));

		$step = new \DateInterval('P1M');
	
		$today = new \DateTime();
		$limitDateBegin = new \DateTime('2010-01-01T00:00:00');
		$limitDateEnd = clone $limitDateBegin;
		$limitDateEnd->add($step);
	
		$result = array();
	
		do {
			$response = $bonanza->GetDataByDates($limitDateBegin, $limitDateEnd);
			if($response->count() > 0) {
				$assets_count += count($response->Asset);
				printf("Found %u Bonanza Assets in %s.\n", count($response->Asset), $limitDateBegin->format('F Y'));
				foreach($response->Asset as $asset) {
					printf("[%u/%u] ", $a++, $assets_count);
					$assetShadow = null;
					try {
						$assetShadow = $this->_harvester->process('asset', $asset);
					} catch(\Exception $e) {
						$this->_harvester->registerProcessingException($e, $asset, $assetShadow);
					}
					print("\n");
				}
			}
			$limitDateBegin->add($step);
			$limitDateEnd->add($step);
		} while($limitDateEnd < $today);
	}
}