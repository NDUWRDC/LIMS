<?php
/* Copyright (C) 2010      Regis Houssin       <regis.houssin@inodbox.com>
 * Copyright (C) 2012-2015 Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2020      David Bensel        <david.bensel@gmail.com>
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
 *       original    htdocs/projet/contact.php
 **      \file       lims/samples_contact.php
 *       \ingroup    lims
 *       \brief      List of all contacts of a sample
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
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
if ($conf->categorie->enabled) { require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php'; }
dol_include_once('/lims/class/samples.class.php');
dol_include_once('/lims/lib/lims_samples.lib.php');

if (!empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array("lims@lims", "companies"));

$id     = (GETPOST('id') ? GETPOST('id', 'int') : GETPOST('facid', 'int')); // For backward compatibility
$ref    = GETPOST('ref', 'alpha');
$lineid = GETPOST('lineid', 'int');
$socid  = GETPOST('socid', 'int');
$action = GETPOST('action', 'aZ09');

// Initialize technical objects
$object = new Samples($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->lims->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('samplescontactcard', 'globalcard'));  // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once$permission = $user->rights->mymodule->myobject->write;

// Security check
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
//$result = restrictedArea($user, 'lims', $object->id);

$permission = $user->rights->lims->samples->write;
/*
 * Actions
 */

// Add new contact
if ($action == 'addcontact' && $permission)
{
	$contactid = (GETPOST('userid') ? GETPOST('userid', 'int') : GETPOST('contactid', 'int'));
	$typeid = (GETPOST('typecontact') ? GETPOST('typecontact') : GETPOST('type'));
	$result = $object->add_contact($contactid, $typeid, GETPOST("source", 'aZ09'));

	if ($result >= 0) {
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
		exit;
	} else {
		if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
} elseif ($action == 'swapstatut' && $permission) {
	// Toggle the status of a contact
	$result = $object->swapContactStatus(GETPOST('ligne'));
} elseif ($action == 'deletecontact' && $permission) {
	// Deletes a contact
	$result = $object->delete_contact($lineid);

	if ($result >= 0) {
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
		exit;
	} else {
		dol_print_error($db);
	}
}


/*
 * View
 */

$title = $langs->trans("SAlabelContact").' - '.$object->ref.' '.$object->name;
$help_url = '';//"EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos";
llxHeader('', $title, $help_url);

$form = new Form($db);
$formcompany = new FormCompany($db);
$contactstatic = new Contact($db);
$userstatic = new User($db);


/* *************************************************************************** */
/*                                                                             */
/* Edition and view mode                                                       */
/*                                                                             */
/* *************************************************************************** */

if ($object->id) {
	/*
	 * Show tabs
	 */
	$head = samplesPrepareHead($object);

	print dol_get_fiche_head($head, 'contact', $langs->trans("Samples"), -1, 'object_'.$object->picto);

	$linkback = '<a href="'.dol_buildpath('/lims/samples_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	
	// Title
	$morehtmlref .=$langs->trans('SAlabelSampleName').' : '.$object->thirdparty->label;
	// Thirdparty
	if ($object->thirdparty->id > 0) {
		$morehtmlref .= '<br>'.$langs->trans('ThirdParty').' : '.(is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	}
	// Project
	if (!empty($conf->projet->enabled) && !empty($object->fk_project)) {
		$proj = new Project($db);
		$proj->fetch($object->fk_project);
		$langs->load('project');
		$morehtmlref.='<br>'.$langs->trans('Project') . ' : ';
		$morehtmlref.= $proj->getNomUrl();
	}
	$morehtmlref .= '</div>';

	// Define a complementary filter for search of next/prev ref.
	/*
	if (!$user->rights->projet->all->lire)
	{
		$objectsListId = $object->getProjectsAuthorizedForUser($user, 0, 0);
		$object->next_prev_filter = " rowid in (".(count($objectsListId) ?join(',', array_keys($objectsListId)) : '0').")";
	}
	*/
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref); //, '', 0, '', '', 1);


	print '<div class="fichecenter">';
	
	//print '<div class="fichehalfleft">';
	//print '<div class="underbanner clearboth"></div>';
	//print '<table class="border tableforfield centpercent">';
	// Other attributes
	//$cols = 2;
	//include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';
	//print "</table>";
	//print '</div>';

	print '<div class="fichehalfleft">';
	print '<div class="ficheaddleft">';
	print '<div class="underbanner clearboth"></div>';

	print '<table class="border tableforfield" width="100%">';
	// Description
	print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
	print nl2br($object->description);
	print '</td></tr>';
	// Categories
	/* TODO: add categories for samples
	if ($conf->categorie->enabled) {
		print '<tr><td class="valignmiddle">'.$langs->trans("Categories").'</td><td>';
		print $form->showCategories($object->id, Categorie::TYPE_PROJECT, 1);
		print "</td></tr>";
	}*/
	print '</table>';
	print '</div>';
	print '</div>';

	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();

	print '<br>';

	// Contacts lines (modules that overwrite templates must declare this into descriptor)
	$preselectedtypeofcontact = 'CUSTOMERREPORT';
	$dirtpls = array_merge($conf->modules_parts['tpl'], array('/core/tpl'));
	foreach ($dirtpls as $reldir) {
		$res = @include dol_buildpath($reldir.'/contacts.tpl.php');
		if ($res) {
			break;
		}
	}
}

// End of page
llxFooter();
$db->close();
