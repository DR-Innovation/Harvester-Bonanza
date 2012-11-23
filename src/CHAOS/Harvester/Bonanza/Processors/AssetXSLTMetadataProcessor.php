<?php
namespace CHAOS\Harvester\Bonanza\Processors;

use CHAOS\Harvester\Processors\XSLTMetadataProcessor;

class AssetXSLTMetadataProcessor extends XSLTMetadataProcessor {
	
	public static function xslt_contributors ($actors) {
		return XSLTMetadataProcessor::preg_explode_to_xml($actors, '/(?P<Role>.*?), ?(?P<Name>.*?); ?/', 'Contributor', 'Person', 'http://www.danskkulturarv.dk/DKA.xsd', true);
	}
	
	public static function xslt_creators ($colophon) {
		return XSLTMetadataProcessor::preg_explode_to_xml($colophon, '/(?P<Name>.*?): ?(?P<Role>.*?)\. ?/', 'Creator', 'Person', 'http://www.danskkulturarv.dk/DKA.xsd', true);
	}
	
	public static function xslt_contributors_2 ($actors) {
		return XSLTMetadataProcessor::preg_explode_to_xml($actors, '/(?P<Role>.*?), ?(?P<Name>.*?); ?/', 'Contributors', 'Contributor', 'http://www.danskkulturarv.dk/DKA2.xsd', true);
	}
	
	public static function xslt_creators_2 ($colophon) {
		return XSLTMetadataProcessor::preg_explode_to_xml($colophon, '/(?P<Name>.*?): ?(?P<Role>.*?)\. ?/', 'Creators', 'Creator', 'http://www.danskkulturarv.dk/DKA2.xsd', true);
	}
}