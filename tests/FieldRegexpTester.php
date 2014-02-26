<?php
class FieldRegexpTester {
	public function __construct($histogram_filepath, $regexps) {
		if(!is_file($histogram_filepath)) {
			throw new \RuntimeException("Couldn't open the histogram.");
		}

		$content = file_get_contents($histogram_filepath);
		$histograms = json_decode($content);
		
		// Count up the number of values.
		$total_value_count = 0;
		foreach($histograms as $count) {
			$total_value_count += $count;
		}
		
		foreach($regexps as $regexp) {
			$matches = array();
			$mismatches = array();
			$match_count = 0;
			foreach($histograms as $value => $count) {
				$current_matches = array();
				if(preg_match($regexp, $value, $current_matches) > 0) {
					$match_count += $count;
					$matches[] = $current_matches;
				} else {
					$mismatches[] = $value;
				}
			}
			printf("Regular expression %s matched %.2f%%\n", $regexp, $match_count * 100.0 / $total_value_count);
			if(count($mismatches) > 0) {
				printf("A total of %u mismatches, here is a couple of examples:\n", count($mismatches));
				for($i = 0; $i < min(count($mismatches), 5); $i++) {
					printf("[%u]\t%s\n", $i, self::indent($mismatches[$i]));
				}
			}
			if(count($matches) > 0) {
				printf("A total of %u matches, here is a couple of examples:\n", count($matches));
				for($i = 0; $i < min(count($matches), 5); $i++) {
					printf("[%u]\t%s\n", $i, self::indent(print_r($matches[$i], true)));
				}
			}
			printf("\n=====\n");
		}
	}
	
	public static function indent($text) {
		return implode("\n\t", explode("\n", $text));
	}
}

$actors = new FieldRegexpTester('field_histograms/actors.json', array(
	//'/^.*$/',
	//'/^(([^;])*;)*$/',
	//'/^(?: ?([^;])*;)*$/',
	'/^(?: ?([^;])*;)* ?$/',
	'/^(?:;? ?([^;]*))+ ?$/',
));