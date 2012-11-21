<?php
namespace CHAOS\Harvester\Bonanza\Modes;
class ByStartdateMode extends \CHAOS\Harvester\Modes\SetByReferenceMode implements \CHAOS\Harvester\Loadable {
	
	public function __construct($harvester, $name, $parameters = null) {
		$this->_harvester = $harvester;
		$this->_harvester->debug("A ".__CLASS__." named '$name' was constructing.");
	}
	
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
		
		$this->_harvester->info("Fetching references to all movieclips changed since %s.", date("r", $reference));
		$movieclips = $bonanza->GetDataByStartdate(date("c", $reference));
		$this->_harvester->info("Found %u movieclips.", count($movieclips));
		
		foreach($movieclips as $movieclip) {
			printf("[#%u] ", $m++);
			$movieclipShadow = null;
			try {
				$movieclipShadow = $this->_harvester->process('asset', $movieclip);
			} catch(\Exception $e) {
				$this->_harvester->registerProcessingException($e, $movieclip, $movieclipShadow);
			}
			print("\n");
		}
	}
}