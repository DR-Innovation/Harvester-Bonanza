<?php
class BonanzaFieldHistogram {
	
	protected $_bonanza;
	protected $_fields;
	protected $_histograms = array();
	protected $_outputFolder;
	
	public static function main($arguments) {
		if(count($arguments) < 6) {
			throw new InvalidArgumentException("This script must be called with 6 runtime arguments.");
		}
		
		set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../src');

		require_once('bonanza/BonanzaClient.php');
		
		$bonanzaURL = $arguments[1];
		$bonanzaUsername = $arguments[2];
		$bonanzaPassword = $arguments[3];
		$outputFolder = strval($arguments[4]);
		$fields = strval($arguments[5]);
		
		if(is_dir($outputFolder)) {
			$outputFolder = realpath($outputFolder);
		} else {
			throw new RuntimeException("Invalid output folder $outputFolder relative to " . getcwd());
		}
		
		$singleton = new \BonanzaFieldHistogram($bonanzaURL, $bonanzaUsername, $bonanzaPassword, $outputFolder, $fields);
		$singleton->run();
		$singleton->save();
	}
	
	public function __construct($bonanzaURL, $bonanzaUsername, $bonanzaPassword, $outputFolder, $fields) {
		$this->_bonanza = new \bonanza\BonanzaClient($bonanzaURL, $bonanzaUsername, $bonanzaPassword);
		$this->_fields = explode(',', $fields);
		$this->_outputFolder = strval($outputFolder);
	}
	
	public function run() {
		$a = 1;
		$assets_count = 0;
		
		$step = new \DateInterval('P1M');
		
		$today = new \DateTime();
		$limitDateBegin = new \DateTime('2010-01-01T00:00:00');
		//$limitDateBegin = new \DateTime('2014-01-01T00:00:00');
		$limitDateEnd = clone $limitDateBegin;
		$limitDateEnd->add($step);
		
		$result = array();
		
		do {
			$response = $this->_bonanza->doGetDataByDates($limitDateBegin, $limitDateEnd);
			if($response->count() > 0) {
				$assets_count += count($response->Asset);
				printf("Found %u Bonanza Assets in %s.\n", count($response->Asset), $limitDateBegin->format('F Y'));
				foreach($response->Asset as $asset) {
					printf("[%u/%u] ", $a++, $assets_count);
					$assetShadow = $this->process($asset);
					print("\n");
				}
			}
			$limitDateBegin->add($step);
			$limitDateEnd->add($step);
		} while($limitDateEnd < $today);
	}
	
	public function save() {
		$unique_values = 0;
		foreach($this->_histograms as $field => $values) {
			$unique_values += count($values);
		}
		echo "Saving $unique_values unique values.\n";
		foreach($this->_histograms as $field => $values) {
			$field_output_filename = $this->_outputFolder . '/' . str_replace('/', '-', strtolower($field)) . '.json';
			$field_output_file = fopen($field_output_filename, 'w');
			fwrite($field_output_file, json_encode($values));
			/*foreach($values as $value => $count) {
				fwrite($field_output_file, "'" . str_replace("'", '"', $value) . "'");
				fwrite($field_output_file, "\t");
				fwrite($field_output_file, $count);
				fwrite($field_output_file, "\n");
			}*/
			fclose($field_output_file);
		}
	}
	
	/**
	 * Process a single XML asset element.
	 * @param \SimpleXMLElement $asset
	 */
	protected function process($asset) {
		foreach($this->_fields as $field) {
			$elements = $asset->xpath($field);
			if(count($elements) <= 0) {
				error_log('Looks like the asset has no '.$field, LOG_WARNING);
			} elseif (count($elements) == 1) {
				$value = strval($elements[0]);
				$this->registerValue($field, $value);
			} else {
				throw \Exception("Woups: Field XPath matches more than a single element.");
			}
		}
	}
	
	protected function registerValue($field, $value) {
		if(!array_key_exists($field, $this->_histograms)) {
			$this->_histograms[$field] = array();
		}
		if(!array_key_exists($value, $this->_histograms[$field])) {
			$this->_histograms[$field][$value] = 0;
		}
		$this->_histograms[$field][$value]++;
	}
	
}
BonanzaFieldHistogram::main($_SERVER['argv']);