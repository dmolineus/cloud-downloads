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
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'Netzmacht',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// elements
	'Netzmacht\Cloud\Downloads\CloudContentDownload' => 'system/modules/cloud-downloads/elements/CloudContentDownload.php',
	'Netzmacht\Cloud\Downloads\CloudContentDownloads' => 'system/modules/cloud-downloads/elements/CloudContentDownloads.php',	
));
