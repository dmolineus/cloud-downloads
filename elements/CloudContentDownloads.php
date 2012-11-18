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
 * Class ContentDownloads
 *
 * Front end content element "downloads".
 * @copyright  Leo Feyer 2005-2012
 * @author     Leo Feyer <http://contao.org>
 * @package    Core
 */
class CloudContentDownloads extends ContentElement
{
	/**
	 * reference to cloud api
	 * 
	 * @var Netzmacht\Cloud\Api
	 */
	protected $objCloudApi;
	
	
	/**
	 * Files object
	 * @var \FilesModel
	 */
	protected $objFiles;
	
	
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_downloads';	
	
	
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
	 * Return if there are no files
	 * @return string
	 */
	public function generate()
	{
		
		$this->cloudMultiSRC = deserialize($this->cloudMultiSRC);				

		// Return if there are no files
		if (!is_array($this->cloudMultiSRC) || empty($this->cloudMultiSRC))
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

		// Get the file entries from the database
		$this->objFiles = array();
		
		foreach ($this->cloudMultiSRC as $strPath) 
		{
			try {
				$objNode = $this->objCloudApi->getNode($strPath);
			}
			catch(\Exception $e) {			
				continue;
			}
			
			$this->objFiles[$objNode->path] = $objNode;			 		
		} 			

		if ($this->objFiles === null)
		{
			return '';
		}

		$file = \Input::get('cloudFile', true);

		// Send the file to the browser and do not send a 404 header (see #4632)
		if ($file != '' && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file)))
		{
			if(isset($this->objFiles[$file]) || isset($this->objFiles[dirname($file)])) {
				$objNode = $this->objCloudApi->getNode($file);
				$this->objCloudApi->sendFileToBrowser($objNode);
			}			
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		global $objPage;

		$files = array();
		$auxDate = array();
		$auxId = array();

		$objFiles = $this->objFiles;
		$allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

		// Get all files
		foreach ($objFiles as $strPath => $objNode)
		{
			// Continue if the files has been processed
			if (isset($files[$objFiles->path]))
			{
				continue;
			}

			// Single files
			if ($objNode->type == 'file')
			{

				if (!in_array($objNode->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objNode->basename))
				{
					continue;
				}

				// cloudApi: no meta data handling so far
				//$arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

				// Use the file name as title if none is given
				$strTitle = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objNode->basename)));

				// Add the image
				$files[$objNode->path] = array
				(
					'id'        => md5($objNode->path),
					'name'      => $objNode->basename,
					'title'     => $strTitle,
					'link'      => $strTitle,
					'caption'   => '',
					'href'      => \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&amp;' : '?') . 'cloudFile=' . $this->urlEncode($objNode->path),
					'filesize'  => $this->getReadableSize($objNode->filesize, 1),
					'icon'      => TL_FILES_URL . 'system/themes/' . $this->getTheme() . '/images/' . $objNode->icon,
					'mime'      => $objNode->mime,
					'meta'      => array(),
					'extension' => $objNode->extension,
					'path'      => $objNode->dirname
				);

				// cloudApi no mtime handling so far
				//$auxDate[] = $objFile->mtime;
				//$auxId[] = $objFiles->id;
			}

			// Folders
			else
			{
				$arrChildren = $objNode->getChildren();				

				if ($objSubfiles === null)
				{
					continue;
				}

				foreach ($arrChildren as $strPath => $objChild)
				{
					// Skip subfolders
					if ($objChild->type == 'folder')
					{
						continue;
					}

					if (!in_array($objChild->extension, $allowedDownload) || preg_match('/^meta(_[a-z]{2})?\.txt$/', $objChild->basename))
					{
						continue;
					}

					//$arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->language);

					// Use the file name as title if none is given
					$strTitle = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)));

					// Add the image
					$files[$objChild->path] = array
					(
						'id'        => md5($objChild->id),
						'name'      => $objChild->basename,
						'title'     => $strTitle,
						'link'      => $strTitle,
						'caption'   => '',
						'href'      => \Environment::get('request') . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos(\Environment::get('request'), '?') !== false) ? '&amp;' : '?') . 'file=' . $this->urlEncode($objChild->path),
						'filesize'  => $this->getReadableSize($objChild->filesize, 1),
						'icon'      => 'system/themes/' . $this->getTheme() . '/images/' . $objChild->icon,
						'mime'      => $objChild->mime,
						'meta'      => $arrMeta,
						'extension' => $objChild->extension,
						'path'      => $objChild->dirname
					);

					//$auxDate[] = $objFile->mtime;
					//$auxId[] = $objSubfiles->id;
				}
			}
		}

		// Sort array
		switch ($this->sortBy)
		{
			default:
			case 'name_asc':
				uksort($files, 'basename_natcasecmp');
				break;

			case 'name_desc':
				uksort($files, 'basename_natcasercmp');
				break;

			case 'date_asc':
				//array_multisort($files, SORT_NUMERIC, $auxDate, SORT_ASC);
				break;

			case 'date_desc':
				//array_multisort($files, SORT_NUMERIC, $auxDate, SORT_DESC);
				break;

			case 'meta': // Backwards compatibility
			case 'custom':
				if ($this->orderSRC != '')
				{
					// Turn the order string into an array
					$arrOrder = array_flip(explode(',', $this->orderSRC));

					// Move the matching elements to their position in $arrOrder
					foreach ($files as $k=>$v)
					{
						if (isset($arrOrder[$v['id']]))
						{
							$arrOrder[$v['id']] = $v;
							unset($files[$k]);
						}
					}

					// Append the left-over images at the end
					if (!empty($files))
					{
						$arrOrder = array_merge($arrOrder, $files);
					}

					// Remove empty or numeric (not replaced) entries
					foreach ($arrOrder as $k=>$v)
					{
						if ($v == '' || is_numeric($v))
						{
							unset($arrOrder[$k]);
						}
					}

					$files = $arrOrder;
					unset($arrOrder);
				}
				break;

			case 'random':
				shuffle($files);
				break;
		}

		$this->Template->files = array_values($files);
	}
}
