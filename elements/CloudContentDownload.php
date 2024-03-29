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
	 * @var \CloudNodeModel
	 */
	protected $objNode;
	

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
			//$this->objCloudApi->authenticate();
		}
		catch(\Exception $e)
		{
			return '<p class="errror">No Cloud Api found</p>';
			
		}
		
		// get node
		$this->objNode = \CloudNodeModel::findOneById($this->cloudSingleSRC);
		
		if($this->objNode === null)
		{		
			return '';			
		}
		

		$allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

		// Return if the file type is not allowed
		if (!in_array($this->objNode->extension, $allowedDownload))
		{			
			return '';
			
		}

		$file = \Input::get('cloudFile', true);

		// Send the file to the browser and do not send a 404 header (see #4632)
		if ($file != '' && $file == $this->objNode->path)
		{	
			try {
				$this->objCloudApi->sendFileToBrowser($this->objNode);	
			}
			catch(\Exception $e)
			{
				
			}
		}
		
		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		if ($this->linkTitle == '')
		{
			$this->linkTitle = $this->objNode->name;
		}

		$this->Template->link = $this->linkTitle;
		$this->Template->title = specialchars($this->titleText ?: $this->linkTitle);
		$this->Template->href = \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&amp;' : '?') . 'cloudFile=' . $this->urlEncode($this->objNode->path);
		$this->Template->filesize = $this->getReadableSize($this->objNode->filesize, 1);
		$this->Template->icon = TL_FILES_URL . 'system/themes/' . $this->getTheme() . '/images/' . $this->objNode->icon;
		$this->Template->mime = $this->objNode->mime;
		$this->Template->extension = $this->objNode->extension;
		$this->Template->path = $this->objNode->dirname;
	}
}
