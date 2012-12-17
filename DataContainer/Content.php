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
 
namespace Netzmacht\Cloud\Downloads\DataContainer;
use Backend;


/**
 * data container, we need it for dynamically load subpalettes
 * 
 */
class Content extends Backend
{
	
	/**
	 * choose the subpalette
	 */
	public function loadSubpalettes()
	{
		$this->import('Database');
		
		$objContent = $this->Database->prepare('SELECT c.type, a.name FROM tl_content c LEFT JOIN tl_cloud_api a ON a.id = c.cloudApi WHERE c.id=?')->execute(\Input::get('id'));
		$arrRow = $objContent->row();
		
		if($objContent !== null && $arrRow['name'] != null && isset($GLOBALS['TL_DCA']['tl_content']['subpalettes_cloudApi'][$arrRow['type']]))
		{
			$GLOBALS['TL_DCA']['tl_content']['metasubselectpalettes'] = $GLOBALS['TL_DCA']['tl_content']['subpalettes_cloudApi'][$arrRow['type']];
		}
	}
}
