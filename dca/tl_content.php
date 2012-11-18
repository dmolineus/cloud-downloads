<?php

/**
 * Contao Open Source CMS
 * 
 * Copyright (C) 2005-2012 Leo Feyer
 * 
 * @package   cloud-api 
 * @author    David Molineus <http://www.netzmacht.de>
 * @license   GNU/LGPL 
 * @copyright Copyright 2012 David Molineus netzmacht creative 
 *  
 **/
 
$GLOBALS['TL_DCA']['tl_content']['palettes']['cloudDownload'] = '{type_legend},type,headline;{source_legend},cloudApi,cloudSingleSRC;{dwnconfig_legend},linkTitle,titleText;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['cloudDownloads'] = '{type_legend},type,headline;{source_legend},cloudApi,cloudMultiSRC,sortBy,useHomeDir;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['cloudApi'] = array
(
	'label'				=> &$GLOBALS['TL_LANG']['tl_content']['cloudapi_apiselect'],
	'inputType'			=> 'cloudApiSelect',
	'eval'				=> array('mandatory'=>true, 'tl_class'=>'clr', 'submitOnChange'=>true),
	'sql'				=> "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['cloudMultiSRC'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['multiSRC'],
	'exclude'                 => true,
	'inputType'               => 'cloudFileTree',
	'eval'                    => array('multiple'=>true, 'fieldType'=>'checkbox', 'orderField'=>'orderSRC', 'files'=>true, 'mandatory'=>true, 'cloudApiField' => 'cloudApi'),
	'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['cloudSingleSRC'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
	'exclude'                 => true,
	'inputType'               => 'cloudFileTree',
	'eval'                    => array('fieldType'=>'radio', 'mandatory'=>true, 'files'=>true, 'tl_class'=>'clr', 'cloudApiField' => 'cloudApi'),
	'sql'                     => "varchar(255) NOT NULL default ''"
);
