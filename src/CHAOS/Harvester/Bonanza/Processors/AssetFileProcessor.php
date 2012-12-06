<?php
namespace CHAOS\Harvester\Bonanza\Processors;
use CHAOS\Harvester\Shadows\ObjectShadow;
use CHAOS\Harvester\Shadows\FileShadow;

class AssetFileProcessor extends \CHAOS\Harvester\Processors\FileProcessor {
	
	protected $_AssetFileType;
	protected $_DerivedFromFormatId;
	
	const THUMB_AND_AUDIO_URL_BASE = "http://downol.dr.dk/download/";
	const VIDEO_URL_BASE = "rtmp://vod-bonanza.gss.dr.dk/bonanza/";
	
	public function __construct($harvester, $name, $parameters) {
		parent::__construct($harvester, $name, $parameters);
		$this->_AssetFileType = $parameters['AssetFileType'];
		if(array_key_exists('DerivedFromFormatId', $parameters)) {
			$this->_DerivedFromFormatId = intval($parameters['DerivedFromFormatId']);
		} else {
			$this->_DerivedFromFormatId = null;
		}
	}
	
	public function process($externalObject, $shadow = null) {
		if(!($shadow instanceof ObjectShadow)) {
			throw new \RuntimeException("The shadow has to be an initialized ObjectShadow.");
		}
		
		switch($this->_AssetFileType) {
			case 'Thumb':
			case 'Audio':
				$urlBase = self::THUMB_AND_AUDIO_URL_BASE;
				break;
			case 'VideoHigh':
			case 'VideoMid':
			case 'VideoLow':
				$urlBase = self::VIDEO_URL_BASE;
				break;
			default:
				throw new \RuntimeException("Unexpected base url of an asset file type: {$this->_AssetFileType}");
		}
		
		foreach($externalObject->AssetFiles->AssetFile as $file) {
			if($file->AssetFileType == $this->_AssetFileType) {
				if(preg_match("#$urlBase(.*)#", $file->Location, $filenameMatches) === 1) {
					$pathinfo = pathinfo($filenameMatches[1]);
					$fileShadow = $this->createFileShadow($pathinfo['dirname'], $pathinfo['basename']);
					
					// Fixing the derived file types.
					if($this->_DerivedFromFormatId !== null && $fileShadow->parentFileShadow == null) {
						foreach($shadow->fileShadows as $anotherFileShadow) {
							/* @var $anotherFileShadow FileShadow */
							if($anotherFileShadow->formatID === $this->_DerivedFromFormatId) {
								if($fileShadow->parentFileShadow == null) {
									$fileShadow->parentFileShadow = $anotherFileShadow;
								} else {
									$this->_harvester->info("[AssetFileProcessor:%s] It was possible to select more than one file as parent with DerivedFromFormatId = %u.", $this->_name, $this->_DerivedFromFormatId);
								}
							}
						}
					} elseif ($this->_DerivedFromFormatId !== null && $fileShadow->parentFileShadow != null) {
						throw new \RuntimeException("Couldn't set the parent file shadow using the DerivedFromFormatId, because the parent was already sat.");
					}
					
					$shadow->fileShadows[] = $fileShadow;
					/*if($file->AssetFileType == '' && !in_array('Image', $shadow->extras['fileTypes'])) {
						$shadow->extras['fileTypes'][] = 'Image';
					}*/
				} else {
					throw new \RuntimeException("Unexpected base url of an asset file: {$file->Location}");
				}
			} else {
				$this->_harvester->debug("Skipping file of type '%s'", $file->AssetFileType);
			}
		}
		
		return $shadow;
	}
}