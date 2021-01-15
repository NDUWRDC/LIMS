<?php
// COPIED FROM \product\ajax\products.php

/* Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
 * Copyright (C) 2005-2013 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2007-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file 	htdocs/product/ajax/products.php
 * \brief 	File to return Ajax response on product list request.
 */

if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');
if (empty($_GET['keysearch']) && !defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');

require '../../main.inc.php';

//$htmlname = GETPOST('htmlname', 'alpha');
//$type = GETPOST('type', 'int');
//$mode = GETPOST('mode', 'int');
//$status = ((GETPOST('status', 'int') >= 0) ? GETPOST('status', 'int') : - 1);
$outjson = (GETPOST('outjson', 'int') ? GETPOST('outjson', 'int') : 0);
$action = GETPOST('action', 'alpha');
$idprod = GETPOST('idprod', 'int');
$idmethod = GETPOST('idmethod', 'int');
$idsample = GETPOST('idsample', 'int');

/*
 * View
 */

// print '<!-- Ajax page called with url '.dol_escape_htmltag($_SERVER["PHP_SELF"]).'?'.dol_escape_htmltag($_SERVER["QUERY_STRING"]).' -->'."\n";

dol_syslog(join(',', $_GET));
// print_r($_GET);

if (!empty($action) && $action == 'fetch' && !empty($idprod))
{
	// action='fetch' is used to get list of methods related to one product -> id must be the product id.

	dol_include_once('/lims/class/methods.class.php', 'Methods');

	$sql = 'SELECT rowid, ref, label, fk_product';
	$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods';
	$sql .= ' WHERE fk_product='.$idprod;
	
	$result = $db->query($sql);
	$outjson = array();
	
	if( $result)
		while($row = $result->fetch_assoc()){
			$outjson[$row['rowid']] = $row['label'];
		}
	echo json_encode($outjson);
}

if (!empty($action) && $action == 'fetch' && !empty($idmethod) && !empty($idsample))
{
	//to get label, accuracy and unit of methods and min/max of limits
	
	dol_include_once('/lims/class/methods.class.php', 'Methods');
	dol_include_once('/lims/class/samples.class.php', 'Samples');
	
	dol_syslog('methods_ajax action=fetch idmethod='.$idmethod.' idsample='.$idsample, LOG_DEBUG);
	
	$method = new Methods($db);
	$method->fetch($idmethod);
	
	$sample = new Samples($db);
	$sample->fetch($idsample);
	
	$minmax = array();
	
	if ($method){
		$label = $method->standard;
		$accuracy = $method->accuracy;
		$rangelower = $method->range_lower;
		$rangeupper = $method->range_upper;
		$minmax = $method->getLimits($sample->fk_limits);
		$limitmin = $minmax['min'];
		$limitmax = $minmax['max'];
		$unit = $method->getUnit();
	}
	
	$outjson = array();
	$outjson = array('label'=>$label, 'accuracy'=>$accuracy, 'rangelower'=>$rangelower, 'rangeupper'=>$rangeupper, 'limitmin'=>$limitmin, 'limitmax'=>$limitmax, 'unit'=>$unit);
	
	echo json_encode($outjson);
}