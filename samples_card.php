<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
 *   	\file       samples_card.php
 *		\ingroup    lims
 *		\brief      Page to create/edit/view samples
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP','none');					// Disable all Content Security Policies


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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/lims/class/samples.class.php');
dol_include_once('/lims/lib/lims_samples.lib.php');

if (!empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}

// Load translation files required by the page
$langs->loadLangs(array("lims@lims", "other"));

// Get parameters
$id			 = GETPOST('id', 'int');   // Sample ID
$ref		 = GETPOST('ref', 'alpha');
$socid		 = GETPOST('socid', 'int');
$action		 = GETPOST('action', 'aZ09');
$userid		 = GETPOST('userid', 'int');
$confirm	 = GETPOST('confirm', 'alpha');
$cancel		 = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'samplescard'; // To manage different context of search
$backtopage	 = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid		 = GETPOST('lineid', 'int');


// Initialize technical objects
$object = new Samples($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->lims->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('samplescard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
dol_syslog('Fetch optionals attributes and labels', LOG_DEBUG);
$extrafields->fetch_name_optionals_label($object->table_element);

dol_syslog('extrafields->getOptionalsFromPost', LOG_DEBUG);
$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = trim(GETPOST("search_all", 'alpha'));
dol_syslog('$search = array()', LOG_DEBUG);
$search = array();
foreach ($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key, 'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
dol_syslog('Load object', LOG_DEBUG);
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.
dol_syslog('After load object', LOG_DEBUG);
// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'lims', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

$usercancreate = $user->rights->lims->samples->write;

$permissiontoread = $user->rights->lims->samples->read;
$permissiontoadd = $user->rights->lims->samples->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->lims->samples->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->lims->samples->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->lims->samples->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->lims->multidir_output[isset($object->entity) ? $object->entity : 1];


/*
 * Actions
 */
dol_syslog('$parameters = array();', LOG_DEBUG);
$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error = 0;

	$backurlforlist = dol_buildpath('/lims/samples_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/lims/samples_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
		}
	}
	$triggermodname = 'LIMS_SAMPLES_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to move up and down lines of object
	include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd)
	{
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, 'SAMPLES_MODIFY');
	}
	if ($action == 'classin' && $permissiontoadd)
	{
		dol_syslog('setProject(GETPOST(projectid, int)', LOG_DEBUG);
		$object->setProject(GETPOST('projectid', 'int'));
	}
	
	// In addition also validate all the lines -> Results
	if ($action == 'confirm_validate' && $confirm == 'yes' && $permissiontoadd)
	{
		if ($object->error || $object->status == $object::STATUS_DRAFT){
			// object not validated
		}
		else {
			// object validated
			dol_syslog('Samples object with ref='.$object->ref.' ... validate lines', LOG_DEBUG);
			$result = new Results($object->db);
				
			foreach ($object->lines as $line){
				$result->fetch($line->fk_result);
				
				dol_syslog('--- $line='.var_export($line,true), LOG_DEBUG);
				
				//dol_syslog('--- $result='.var_export($result,true), LOG_DEBUG);
				If ($line->status == $result::STATUS_DRAFT){
					$line->validate($user);
					dol_syslog('Result with ref='.$result->ref.' validated', LOG_DEBUG);
				}
			
			}
		}
	}
	
	// Add a new line
	if ($action == 'addline' && $usercancreate)
	{
		$langs->load('errors');
		$error = 0;
		
		$predef=''; // Not used so far (invoice: free entry or predefined product)
		// result.class.php: Class SampleLine  
		// Store: rowid-ref-rang-fk_samples-fk_user-fk_method-result-start-end-abnormalities-?status

		//(0 = get then post(default), 1 = only get, 2 = only post, 3 = post then get)
		$idprod = GETPOST('ProdID', 'int'); 
		$rang = GETPOST('rang', 'int');
		$fk_user = GETPOST('userid', 'int');
		$fk_method = GETPOST('MethodID', 'int');
		$testresult = GETPOST('result', 'int');
		$abnormalities = GETPOST('abnormalities');
		$status = GETPOST('status');
		
		dol_syslog('Samples_card action=addline: ---------', LOG_DEBUG);
		dol_syslog('idprod= '.$idprod, LOG_DEBUG);
		dol_syslog('rang= '.$rang, LOG_DEBUG);
		dol_syslog('fkuser= '.$fk_user, LOG_DEBUG);
		dol_syslog('fkmethod= '.$fk_method, LOG_DEBUG);
		dol_syslog('result= '.$testresult, LOG_DEBUG);
		dol_syslog('abnormalities= '.$abnormalities, LOG_DEBUG);
		dol_syslog('status= '.$status, LOG_DEBUG);
		
		// Extrafields
		$extralabelsline = $extrafields->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafields->getOptionalsFromPost($object->table_element_line, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_".$key.$predef]);
			}
		}

		// ERROR HANDLING
		if ($result == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('result')), null, 'errors');
			$error++;
		}
		
		// No Errors -> Add line
		if (!$error && ($result >= 0) && !empty($idprod)) {
			$ret = $object->fetch($id);
			if ($ret < 0) {
				dol_print_error($db, $object->error);
				exit();
			}

			// Clean parameters $date_start and $date_end
			$date_start = dol_mktime(GETPOST('date_start'.$predef.'hour'), GETPOST('date_start'.$predef.'min'), GETPOST('date_start'.$predef.'sec'), GETPOST('date_start'.$predef.'month'), GETPOST('date_start'.$predef.'day'), GETPOST('date_start'.$predef.'year'));
			$date_end = dol_mktime(GETPOST('date_end'.$predef.'hour'), GETPOST('date_end'.$predef.'min'), GETPOST('date_end'.$predef.'sec'), GETPOST('date_end'.$predef.'month'), GETPOST('date_end'.$predef.'day'), GETPOST('date_end'.$predef.'year'));

			// Insert line
			$result = $object->addline($idprod, $fk_method, $abnormalities, $testresult, $fk_user,$date_start, $date_end, $rang, '', 0, GETPOST('fk_parent_line'));
			dol_syslog(__METHOD__." addline idprod=".$idprod." idmethod=".$fk_method."  result=".$result, LOG_DEBUG);

			
			if ($result > 0){
				// Define output language and generate document
				if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
				{
					$outputlangs = $langs;
					$newlang = '';
					if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
					if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
					if (!empty($newlang)) {
						$outputlangs = new Translate("", $conf);
						$outputlangs->setDefaultLang($newlang);
						$outputlangs->load('products');
					}
					$model = $object->modelpdf;
					$ret = $object->fetch($id); // Reload to get new records

					$result = $object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
					if ($result < 0) setEventMessages($object->error, $object->errors, 'errors');
				}

				unset($_POST['rang']);
				unset($_POST['userid']);
				unset($_POST['result']);

				unset($_POST['abnormalities']);
				unset($_POST['status']);
				unset($_POST['MethodID']);
				unset($_POST['ProdID']);

				unset($_POST['date_starthour']);
				unset($_POST['date_startmin']);
				unset($_POST['date_startsec']);
				unset($_POST['date_startday']);
				unset($_POST['date_startmonth']);
				unset($_POST['date_startyear']);
				unset($_POST['date_endhour']);
				unset($_POST['date_endmin']);
				unset($_POST['date_endsec']);
				unset($_POST['date_endday']);
				unset($_POST['date_endmonth']);
				unset($_POST['date_endyear']);

			} else {
				setEventMessages($object->error, $object->errors, 'errors');
			}

			$action = '';
		}
	}
	
	// Actions to send emails
	$triggersendname = 'SAMPLES_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_SAMPLES_TO';
	$trackid = 'samples'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */
dol_syslog('$form = new Form($db)',LOG_DEBUG);
$form = new Form($db);
$formfile = new FormFile($db);
if (!empty($conf->projet->enabled)) { $formproject = new FormProjets($db); }

llxHeader('', $langs->trans('Samples'), '');

// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';


// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Samples")));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	dol_fiche_head(array(), '');

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	dol_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage ? "submit" : "button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>'; // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("Samples"));

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	dol_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	dol_fiche_end();

	print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
	dol_syslog('Part to show record: object->fetch_optionals', LOG_DEBUG);
	
	$res = $object->fetch_optionals();

	$head = samplesPrepareHead($object);
	dol_syslog('dol_fiche_head', LOG_DEBUG);
	dol_fiche_head($head, 'card', $langs->trans("Samples"), -1, $object->picto);
	
	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteSamples'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'ask_deleteline')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		dol_syslog('action=clone', LOG_DEBUG);
	
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}
	// Update line
	if ($action == 'updateline' && $usercancreate && !GETPOST('cancel', 'alpha'))
	{
		$langs->load('errors');
		$error = 0;
		
		$predef=''; // Not used so far (invoice: free entry or predefined product)
		// result.class.php: Class SampleLine  
		// update: fk_user-result-start-end-abnormalities
		
		$date_start = '';
		$date_end = '';

		// Clean parameters $date_start and $date_end
		$date_start = dol_mktime(GETPOST('date_start'.$predef.'hour'), GETPOST('date_start'.$predef.'min'), GETPOST('date_start'.$predef.'sec'), GETPOST('date_start'.$predef.'month'), GETPOST('date_start'.$predef.'day'), GETPOST('date_start'.$predef.'year'));
		$date_end = dol_mktime(GETPOST('date_end'.$predef.'hour'), GETPOST('date_end'.$predef.'min'), GETPOST('date_end'.$predef.'sec'), GETPOST('date_end'.$predef.'month'), GETPOST('date_end'.$predef.'day'), GETPOST('date_end'.$predef.'year'));

		//(0 = get then post(default), 1 = only get, 2 = only post, 3 = post then get)
		$fk_user = GETPOST('userid', 'int');
		$testresult = GETPOST('result', 'int');
		$abnormalities = GETPOST('abnormalities');
		
		// Extrafields
		$extralabelsline = $extrafields->fetch_name_optionals_label($object->table_element_line);
		$array_options = $extrafields->getOptionalsFromPost($object->table_element_line, $predef);
		// Unset extrafield
		if (is_array($extralabelsline)) {
			// Get extra fields
			foreach ($extralabelsline as $key => $value) {
				unset($_POST["options_".$key.$predef]);
			}
		}

		// Check parameters
		if ($date_start > $date_end) {
				$langs->load("errors");
				$this->error = $langs->trans('ErrorStartDateGreaterEnd');
				return -1;
			}
			
		// ERROR HANDLING
		/*
		if ($result == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('result')), null, 'errors');
			$error++;
		}*/
		$obj = new Results($object->db);
		$obj->fetch(GETPOST('lineid', 'int'));
		
		if (is_null($obj->ref))
			$error++;
		
		// No Errors -> Update line
		if (!$error) 
		{
			// Update line
			dol_syslog('action=updateline: ref='.$obj->ref.' lineid='.GETPOST('lineid', 'int'), LOG_DEBUG);
			
			// Those are not changed:
			//$obj->fk_samples = $this->id;
			//$obj->fk_method = $fk_method;
			//$obj->rang = $ranktouse;
			//$obj->status = self::STATUS_DRAFT;
			
			$obj->fk_user		 = $fk_user;
			$obj->result		 = $testresult;
			$obj->start			 = $date_start;
			$obj->end			 = $date_end;
			$obj->abnormalities	 = $abnormalities;
			
			$result = $obj->updateCommon($user);
			
			// method not defined:
			//$object->updateline($abnormalities, $testresult, $fk_user, $date_start, $date_end,);
		
			if ($result > 0)
			{
				// Define output language and generate document
				if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
				{
					$outputlangs = $langs;
					$newlang = '';
					if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
					if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
					if (!empty($newlang)) {
						$outputlangs = new Translate("", $conf);
						$outputlangs->setDefaultLang($newlang);
						$outputlangs->load('products');
					}
					$model = $object->modelpdf;
					$ret = $object->fetch($id); // Reload to get new records

					$result = $object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
					if ($result < 0) setEventMessages($object->error, $object->errors, 'errors');
				}

				unset($_POST['rang']);
				unset($_POST['userid']);
				unset($_POST['result']);

				unset($_POST['abnormalities']);
				unset($_POST['status']);
				unset($_POST['MethodID']);
				unset($_POST['ProdID']);

				unset($_POST['date_starthour']);
				unset($_POST['date_startmin']);
				unset($_POST['date_startsec']);
				unset($_POST['date_startday']);
				unset($_POST['date_startmonth']);
				unset($_POST['date_startyear']);
				unset($_POST['date_endhour']);
				unset($_POST['date_endmin']);
				unset($_POST['date_endsec']);
				unset($_POST['date_endday']);
				unset($_POST['date_endmonth']);
				unset($_POST['date_endyear']);

			} else {
				setEventMessages($object->error, $object->errors, 'errors');
			}
			$action = '';
		}
	}
	
	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',	'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/lims/samples_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	
	// Description
	$morehtmlref.=$form->editfieldkey("Description", 'description', $object->description, $object, $user->rights->lims->samples->creer, 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("Description", 'description', $object->description, $object, $user->rights->lims->samples->creer, 'string', '', null, null, '', 1);
	// Thirdparty -> Needs to be changed to 'Customer'
	dol_syslog('object->thirdparty->getNomUrl', LOG_DEBUG);
	$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? 
	$object->thirdparty->getNomUrl(1) : '');
	
	// Project
	if (! empty($conf->projet->enabled))
	{
		$langs->load("projects");
		$morehtmlref.='<br>'.$langs->trans('Project') . ' ';
		if ($permissiontoadd)
		{
			if ($action != 'classify')
				$morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> : ';
			if ($action == 'classify') {
				//$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
				$morehtmlref .= '<form method="post" action="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'">';
				$morehtmlref .= '<input type="hidden" name="action" value="classin">';
				$morehtmlref .= '<input type="hidden" name="token" value="'.newToken().'">';
				$morehtmlref .= $formproject->select_projects($object->socid, $object->fk_project, 'projectid', $maxlength, 0, 1, 0, 1, 0, 0, '', 1);
				$morehtmlref .= '<input type="submit" class="button valignmiddle" value="'.$langs->trans("Modify").'">';
				$morehtmlref .= '</form>';
			} else {
				$morehtmlref.=$form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'none', 0, 0, 0, 1);
			}
		} else {
			if (! empty($object->fk_project)) {
				$proj = new Project($db);
				$proj->fetch($object->fk_project);
				$morehtmlref.=$proj->getNomUrl();
			} else {
				$morehtmlref.='';
			}
		}
	}
	
	$morehtmlref .= '</div>';

	dol_syslog(__METHOD__.' dol_banner_tab', LOG_DEBUG);
	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Common attributes
	$keyforbreak='date';						// We change column with this field
	unset($object->fields['fk_project']);		// Hide field already shown in banner
	unset($object->fields['fk_soc']);			// Hide field already shown in banner
	unset($object->fields['description']);		// Hide field already shown in banner
	unset($object->fields['note_private']);		// Hide field 
	
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';
	dol_fiche_end();

	/*
	 * Lines
	 */
	dol_syslog(__METHOD__.' $object->table_element_line='.var_export($object->table_element_line, true), LOG_DEBUG);
	if (!empty($object->table_element_line))
	{
		// Show object lines
		dol_syslog(__METHOD__.' before object->getLinesArray', LOG_DEBUG);
		$result = $object->getLinesArray();
		dol_syslog(__METHOD__.' object->getLinesArray='.var_export($result, true), LOG_DEBUG);

		print '	<form name="addproduct" id="addproduct" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.(($action != 'editline') ? '#addline' : '#line_'.GETPOST('lineid', 'int')).'" method="POST">
		<input type="hidden" name="token" value="' . newToken().'">
		<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline').'">
		<input type="hidden" name="mode" value="">
		<input type="hidden" name="id" value="' . $object->id.'">
		';

		if (!empty($conf->use_javascript_ajax) && $object->status == 0) {
			include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
		}

		print '<div class="div-table-responsive-no-min">';
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
		{
			print '<table id="tablelines" class="noborder noshadow" width="100%">';
		}

		if (!empty($object->lines))
		{
			$object->printObjectLines($action, $mysoc, null, GETPOST('lineid', 'int'), 1);
		}

		// Form to add new line
		
		if ($object->status == 0 && $permissiontoadd && $action != 'selectlines')
		{
			if ($action != 'editline')
			{
				// Add products/services form
				// Hook is used, formAddObjectLine would be displayed twice
				//$object->formAddObjectLine(1, $mysoc, $soc);
				$parameters = array();
				$reshook = $hookmanager->executeHooks('formAddObjectLine', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			}
		}
		
		if (!empty($object->lines) || ($object->status == $object::STATUS_DRAFT && $permissiontoadd && $action != 'selectlines' && $action != 'editline'))
		{
			print '</table>';
		}
		print '</div>';

		print "</form>\n";
	}


	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook))
		{
			// Send
			print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail').'</a>'."\n";

			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED)
			{
				if ($permissiontoadd)
				{
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes">'.$langs->trans("SetToDraft").'</a>';
				}
			}

			// Modify
			if ($permissiontoadd)
			{
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit">'.$langs->trans("Modify").'</a>'."\n";
			}
			else
			{
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
			}
			// Validate
			if ($object->status == $object::STATUS_DRAFT)
			{
				if ($permissiontoadd)
				{
					if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0))
					{
						print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes">'.$langs->trans("Validate").'</a>';
					}
					else
					{
						$langs->load("errors");
						print '<a class="butActionRefused" href="" title="'.$langs->trans("ErrorAddAtLeastOneLineFirst").'">'.$langs->trans("Validate").'</a>';
					}
				}
			}

			// Clone
			if ($permissiontoadd)
			{
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&socid='.$object->socid.'&action=clone&object=samples">'.$langs->trans("ToClone").'</a>'."\n";
			}

			/*
			if ($permissiontoadd)
			{
				if ($object->status == $object::STATUS_ENABLED)
			 	{
			 		print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=disable">'.$langs->trans("Disable").'</a>'."\n";
			 	}
			 	else
			 	{
			 		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=enable">'.$langs->trans("Enable").'</a>'."\n";
			 	}
			}
			if ($permissiontoadd)
			{
				if ($object->status == $object::STATUS_VALIDATED)
			 	{
			 		print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=close">'.$langs->trans("Cancel").'</a>'."\n";
			 	}
			 	else
			 	{
			 		print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=reopen">'.$langs->trans("Re-Open").'</a>'."\n";
			 	}
			}
			*/

			// Delete (need delete permission, or if draft, just need create/modify permission)
			if ($permissiontodelete)
			{
				print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>'."\n";
			}
			else
			{
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
			}
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend')
	{
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		// Documents
		$objref = dol_sanitizeFileName($object->ref);
		$relativepath = $objref . '/' . $objref . '.pdf';
		$filedir = $conf->lims->dir_output . '/' . $objref;
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$genallowed = $user->rights->lims->samples->read;	// If you can read, you can build the PDF to read content
		$delallowed = $user->rights->lims->samples->create;	// If you can create/edit, you can remove a file on card
		
		if (is_null ( $object->modelpdf ))
			$object->modelpdf = 'lims_testreport';
		
		// Does not work: bad file path generated
		// ?? Maybe use $hookmanager->initHooks(array('formfile')) ??
		//print $formfile->showdocuments('lims', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang, '', $object, 0, 'remove_file_comfirm');
		

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('samples'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright = '<a href="'.dol_buildpath('/lims/samples_agenda.php', 1).'?id='.$object->id.'">';
		$morehtmlright .= $langs->trans("SeeAll");
		$morehtmlright .= '</a>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail='samples';
	$defaulttopic='InformationMessage';
	$diroutput = $conf->lims->dir_output;
	$trackid = 'samples'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
