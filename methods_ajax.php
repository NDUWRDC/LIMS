<?php
// COPIED FROM \product\ajax\products.php

/* Copyright (C) 2006      Andre Cianfarani     <acianfa@free.fr>
 * Copyright (C) 2005-2013 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2007-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020-2021 David Bensel			<david.bensel@gmail.com>
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
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

if (!defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL', 1); // Disables token renewal
if (!defined('NOREQUIREMENU'))  define('NOREQUIREMENU', '1');
if (!defined('NOREQUIREHTML'))  define('NOREQUIREHTML', '1');
if (!defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX', '1');
if (!defined('NOREQUIRESOC'))   define('NOREQUIRESOC', '1');
if (!defined('NOCSRFCHECK'))    define('NOCSRFCHECK', '1');
if (empty($_GET['keysearch']) && !defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');

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

	$sql = 'SELECT e.rowid as erowid, e.ref as eref, e.label as elabel, e.description as edescription, e.fk_product,';
	$sql .= ' m.rowid, m.ref, m.label, m.fk_equipment';
	$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as m';
	$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'lims_equipment as e ON e.rowid=m.fk_equipment'; 
	$sql .= ' WHERE m.fk_equipment='.$idprod;

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