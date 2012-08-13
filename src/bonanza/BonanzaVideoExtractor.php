<?php
namespace bonanza;
class BonanzaVideoExtractor extends \ACHAOSFileExtractor {
	const BONANZA_VIDEO_BASE = 'rtmp://vod-bonanza.gss.dr.dk/bonanza/';
	
	public $_CHAOSVideoHighFormatID;
	public $_CHAOSVideoMidFormatID;
	public $_CHAOSVideoLowFormatID;
	public $_CHAOSVideoDestinationID;
	
	public static $singleton;
	/**
	 * Process the DFI movieitem.
	 * @param CHAOS\Portal\Client\PortalClient $chaosClient The CHAOS client to use for the importing.
	 * @param dfi\DFIClient $dfiClient The DFI client to use for importing.
	 * @param dfi\model\Item $movieItem The DFI movie item.
	 * @param stdClass $object Representing the DKA program in the CHAOS service, of which the images should be added to.
	 * @return array An array of processed files.
	 */
	function process($harvester, $object, $asset, &$extras) {
		$videosProcessed = array();
		
		$urlBase = self::BONANZA_VIDEO_BASE;
		
		$files = $asset->AssetFiles->AssetFile;
		
		printf("\tUpdating files for videos:\t");
		
		echo self::PROGRESS_END_CHAR;
		foreach($files as $f) {
			// The following line is needed as they forget to set their encoding.
			//$i->Caption = iconv( "UTF-8", "ISO-8859-1//TRANSLIT", $i->Caption );
			$formatId = null;
			if($f->AssetFileType == 'VideoHigh') {
				$formatId = $this->_CHAOSVideoHighFormatID;
			} elseif($f->AssetFileType == 'VideoLow') {
				$formatId = $this->_CHAOSVideoLowFormatID;
			} elseif($f->AssetFileType == 'VideoMid') {
				$formatId = $this->_CHAOSVideoMidFormatID;
			} else {
				// This is not a video asset file.
				continue;
			}
			
			$filenameMatches = array();
			if(preg_match("#$urlBase(.*)#", strval($f->Location), $filenameMatches) === 1) {
				$pathinfo = pathinfo($filenameMatches[1]);
				$response = $this->getOrCreateFile($chaosClient, $object, null, $formatId, $this->_CHAOSVideoDestinationID, $pathinfo['basename'], $pathinfo['basename'], $pathinfo['dirname']);
				if($response == null) {
					throw new RuntimeException("Failed to create a video file.");
				} else {
					$object->ProcessedFiles[] = $response;
					$videosProcessed[] = $response;
				}
			} else {
				printf("\tWarning: Found an images which was didn't have a scanpix/mini URL. This was not imported.\n");
			}
		}
		echo self::PROGRESS_END_CHAR;
		printf(" Done\n");
		
		return $videosProcessed;
	}
}