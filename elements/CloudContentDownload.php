<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package   cloud-downloads 
 * @author    David Molineus <http://www.netzmacht.de>
 * @license   GNU/LGPL 
 * @copyright Copyright 2012 David Molineus netzmacht creative 
 *  
 **/


/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace Netzmacht\Cloud\Downloads;
use Netzmacht\Cloud\Api;
use ContentElement;


/**
 * Class ContentDownload
 *
 * Front end content element "download".
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://contao.org>
 * @package    Core
 */
class CloudContentDownload extends ContentElement
{	
  	
	/**
	 * reference to cloud api
	 * 
	 * @var Netzmacht\Cloud\Api
	 */
	protected $objCloudApi;
	

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_download';
	
	/**
	 * get cloudApi 
	 * 
	 * @return void
	 * @param array
	 */
	public function __construct($arrAttributes = null)
	{
		parent::__construct($arrAttributes);
		
		if ($this->cloudApi == null && $this->cloudApiField != '')	
		{
			$this->cloudApi = $this->activeRecord->{$this->cloudApiField};			
		}
	}


	/**
	 * Return if the file does not exist
	 * @return string
	 */
	public function generate()
	{		
		// Return if there is no file
		if ($this->cloudSingleSRC == '')
		{
			return '';
		}
		
		// load cloud api
		try 
		{
			$this->objCloudApi = Api\CloudApiManager::getApi($this->cloudApi);
			$this->objCloudApi->authenticate();
		}
		catch(\Exception $e)
		{
			return '<p class="errror">No Cloud Api found</p>';
			
		}
		
		// get node
		try 
		{
			$objNode = $this->objCloudApi->getNode($this->cloudSingleSRC);
		}
		catch(\Exception $e)
		{		
			return '';			
		}
		

		$allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

		// Return if the file type is not allowed
		if (!in_array($objNode->extension, $allowedDownload))
		{			
			return '';
			
		}

		$file = \Input::get('cloudFile', true);

		// Send the file to the browser and do not send a 404 header (see #4632)
		if ($file != '' && $file == $objNode->path)
		{
			$this->objCloudApi->sendFileToBrowser($objNode);
		}
				

		$this->cloudSingleSRC = $objNode->path;
		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		$objNode = $this->objCloudApi->getNode($this->cloudSingleSRC);

		if ($this->linkTitle == '')
		{
			$this->linkTitle = $objNode->basename;
		}

		$this->Template->link = $this->linkTitle;
		$this->Template->title = specialchars($this->titleText ?: $this->linkTitle);
		$this->Template->href = \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&amp;' : '?') . 'cloudFile=' . $this->urlEncode($objNode->path);
		$this->Template->filesize = $this->getReadableSize($objNode->filesize, 1);
		$this->Template->icon = TL_FILES_URL . 'system/themes/' . $this->getTheme() . '/images/' . $objNode->icon;
		$this->Template->mime = $objNode->mime;
		$this->Template->extension = $objNode->extension;
		$this->Template->path = $objNode->dirname;
	}
}
