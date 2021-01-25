<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020 David Bensel  <david.bensel@gmail.com>
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

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

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
$id 		= GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$socid		= GETPOST('socid', 'int');
$action 	= GETPOST('action', 'aZ09');
$userid		= GETPOST('userid', 'int');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'samplescard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$lineid		 = GETPOST('lineid', 'int');
$origin 	 = GETPOST('origin', 'alpha');
$originid	 = GETPOST('originid', 'int');

// Initialize technical objects
$object = new Samples($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->lims->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('samplescard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key, 'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.
// Hide 'revision' and 'last_modifications' if not relevant
if ($object->revision <= 0) {
	$object->fields['revision']['visible'] = 0;
	$object->fields['last_modifications']['visible'] = 0;
}

$usercancreate = $user->rights->lims->samples->write;

$permissiontoread = $user->rights->lims->samples->read;
$permissiontoadd = $user->rights->lims->samples->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontovalidate = $user->rights->lims->samples->delete;
$permissiontodelete = $user->rights->lims->samples->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->lims->samples->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->lims->samples->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->lims->multidir_output[isset($object->entity) ? $object->entity : 1];

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'lims', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

//if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

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

	// Do this before the report gets printed
	// In addition also validate all the lines -> Results
	if ($action == 'confirm_validate' && $confirm == 'yes' && $permissiontoadd)
	{
		dol_syslog('Samples object with ref='.$object->ref.' ... validate lines', LOG_DEBUG);
		$result = new Results($object->db);
			
		foreach ($object->lines as $line){
			$result->fetch($line->fk_result);
			
			if ($line->status == $result::STATUS_DRAFT){
				$line->validate($user);
				dol_syslog('Result with ref='.$result->ref.' validated', LOG_DEBUG);
			}
		}
		// save person who validated
		$object->fk_user_approval = $user->id;
		// save date when validated
		$object->date_approval = dol_now();
		$object->update($user);
	}
	if ($action == 'update') {
		dol_syslog('samplingbyclient='.GETPOST('samplingbyclient', 'int').' fk_user='.GETPOST('fk_user', 'int'), LOG_DEBUG);
		if (GETPOST('samplingbyclient', 'int')==1 && GETPOST('fk_user', 'int')!=-1) {
			$langs->load("errors");
			$object->error = $langs->trans('ErrorSamplingPersonSetTwice');
			setEventMessages($langs->trans("ErrorSamplingPersonSetTwice"), null, 'errors');
			$action = 'edit';
		}
	}

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
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd)
	{
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// actions_addupdatedelete.inc.php does not print with confirm_setdraft
	if ($action == 'confirm_setdraft' && $confirm == 'yes' && $permissiontoadd)
	{
		// reset person who validated to null
		$object->fk_user_approval = NULL;
		$object->update($user);
		
		//force report to be printed again
		// Generate Document
		$object->PrintReport();
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
		$fk_user = GETPOST('userid', 'int');
		$fk_method = GETPOST('MethodID', 'int');
		$testresult = GETPOST('result', 'int');
		//$abnormalities = GETPOST('abnormalities');
		$status = GETPOST('status');
		
		// In case fk_user is not set, set it to current user->id
		$fk_user = is_numeric($fk_user) ? $fk_user : $user->id;
		/*
		dol_syslog('Samples_card action=addline: ---------', LOG_DEBUG);
		dol_syslog('idprod= '.$idprod, LOG_DEBUG);
		dol_syslog('rang= '.$rang, LOG_DEBUG);
		dol_syslog('fkuser= '.$fk_user, LOG_DEBUG);
		dol_syslog('fkmethod= '.$fk_method, LOG_DEBUG);
		dol_syslog('result= '.$testresult, LOG_DEBUG);
		dol_syslog('abnormalities= '.$abnormalities, LOG_DEBUG);
		dol_syslog('status= '.$status, LOG_DEBUG);
		*/
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
		// check for required fields
		if ($testresult == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('result')), null, 'errors');
			$error++;
		}
		// check if result conformity: -1...error, 0...result within method's range, 1 result outside method's range
		$abnormalities = $object->checkConformity($testresult, $fk_method);
		if ($abnormalities < 0) {
			$error++;
		}
		// No Errors -> Add line
		if (!$error && !empty($idprod)) {
			$ret = $object->fetch($id);
			if ($ret < 0) {
				dol_print_error($db, $object->error);
				exit();
			}

			// Clean parameters $date_start and $date_end
			$date_start = dol_mktime(GETPOST('date_start'.$predef.'hour'), GETPOST('date_start'.$predef.'min'), GETPOST('date_start'.$predef.'sec'), GETPOST('date_start'.$predef.'month'), GETPOST('date_start'.$predef.'day'), GETPOST('date_start'.$predef.'year'));
			$date_end = dol_mktime(GETPOST('date_end'.$predef.'hour'), GETPOST('date_end'.$predef.'min'), GETPOST('date_end'.$predef.'sec'), GETPOST('date_end'.$predef.'month'), GETPOST('date_end'.$predef.'day'), GETPOST('date_end'.$predef.'year'));

			// Insert line
			$result = $object->addline($idprod, $fk_method, $abnormalities, $testresult, $fk_user,$date_start, $date_end, -1, '', 0, GETPOST('fk_parent_line'));
			dol_syslog(__METHOD__." addline idprod=".$idprod." idmethod=".$fk_method."  result=".$result, LOG_DEBUG);

			
			if ($result > 0){
				// Generate Document
				$object->PrintReport();

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

	if ($action == 'setlabel') {
		dol_syslog(__METHOD__.' action=setlabel', LOG_DEBUG);
		$object->id = $id;
		$object->label = GETPOST('label', 'alpha');
		$object->update($user); // save value
		$action = 'view'; // ToDo: Set &id='.$id;'
	}

	// Actions to send emails
	$triggersendname = 'LIMS_SAMPLES_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_SAMPLES_TO';
	$trackid = 'samples'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
if (!empty($conf->projet->enabled)) { $formproject = new FormProjets($db); }

$title = $langs->trans("Samples");
$help_url = '';
llxHeader('', $title, $help_url);

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
	if (!empty($origin) && !empty($originid) && !empty($socid)){
		
		// COPIED FROM htdocs/commande/card.php
		$element = $subelement = $origin;
		$regs = array();
		if (preg_match('/^([^_]+)_([^_]+)/i', $origin, $regs)) {
			$element = $regs[1];
			$subelement = $regs[2];
		}
		dol_syslog('Sample create from '.$element.' id='.$originid.' socid='.$socid, LOG_DEBUG);

		if ($element == 'project') {
			$projectid = $originid;
		} 
		else {
			// For compatibility
			if ($element == 'order' || $element == 'commande') {
				$element = $subelement = 'commande';
			} elseif ($element == 'propal') {
				$element = 'comm/propal';
				$subelement = 'propal';
			} elseif ($element == 'contract') {
				$element = $subelement = 'contrat';
			}

			dol_include_once('/'.$element.'/class/'.$subelement.'.class.php');
			
			$classname = ucfirst($subelement);
			$objectsrc = new $classname($db);
			$objectsrc->fetch($originid);
			if (empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines'))
				$objectsrc->fetch_lines();
			
			$object->fk_soc = $socid;
			$object->fk_project = (!empty($objectsrc->fk_project) ? $objectsrc->fk_project : '');
			
			$object->fk_facture = $objectsrc->id;
			
			$object->note_public = (!empty($objectsrc->ref_client) ? ('Ref client: '.$objectsrc->ref_client) : '');

			$object->note_private = $object->getDefaultCreateValueFor('note_private', (!empty($objectsrc->note_private) ? $objectsrc->note_private : null));
			$object->note_public .= $object->getDefaultCreateValueFor('note_public', (!empty($objectsrc->note_public) ? $objectsrc->note_public : null));
			
			// Processed at /core/tpl/commonfields_add.tpl.php
			$_POST['fk_facture'] = $object->fk_facture;
			$_POST['fk_soc'] = $object->fk_soc;
			$_POST['fk_project'] = $object->fk_project;
			$_POST['note_public'] = $object->note_public;
			$_POST['note_private'] = $object->note_private;
			// Processed after object is created
			$_POST['socid'] = $object->fk_soc;
			$_POST['origin'] = $element;
			$_POST['originid'] = $originid;
	
			session_start();
			$_SESSION['importsample_post'] = $_POST;
		}
	}

	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("Sample")), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage ? "submit" : "button").'" class="button button-cancel" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>'; // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';

	//dol_set_focus('input[name="ref"]');
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("Samples"), '', 'object_'.$object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print '<div class="center"><input type="submit" class="button button-save" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button button-cancel" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
	dol_syslog('Part to show record', LOG_DEBUG);
	
	session_start();
	if (empty($_POST) && isset($_SESSION['importsample_post'])) {
		$post = $_SESSION['importsample_post'];
		unset($_SESSION['importsample_post']);
		dol_syslog('Restore session importsample_post', LOG_DEBUG);
	}
	else $post = $_POST;
	
	if (isset($post['origin']) && isset($post['originid'])) {
		$origin = $post['origin'];
		$originid = $post['originid'];
		$classname = ucfirst($post['origin']);
		$objectsrc = new $classname($db);
		$objectsrc->fetch($post['originid']);
		
		dol_syslog('Import lines from '.$classname.' with id='.$originid, LOG_DEBUG);
		
		if (!empty($objectsrc->lines) && method_exists($objectsrc, 'fetch_lines')) {
			$objectsrc->fetch_lines();
			$i = 0;
			$products_source = array();
			foreach ($objectsrc->lines as $line) {
				$product_import = $line->fk_product;
				if (is_numeric($product_import))
					$products_source[$i] = $product_import;
				$i++;
			}
			dol_syslog('Found products_source ...'.var_export($products_source,true), LOG_DEBUG);
			
			$sql = 'SELECT m.rowid, m.ref, m.label, m.description, m.fk_equipment,';
			$sql .= ' e.fk_product';
			$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as m';
			$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'lims_equipment as e ON e.rowid=m.fk_equipment';
			$sql .= ' WHERE fk_product IN ('.implode(',',$products_source).')';

			// Insert line
			$resql = $object->db->query($sql);
			if (!$resql) {
				$object->error = $object->db->lasterror();
			} 
			else {
				$num = $object->db->num_rows($resql);
				//dol_syslog("query num=".$num, LOG_DEBUG);
				
				if ($num > 0) {
					while ($obj = $object->db->fetch_object($resql)) {
						//dol_syslog(" addline obj".var_export($obj, true), LOG_DEBUG);
						
						$idprod = $obj->fk_product;
						$fk_method = $obj->rowid;
						$abnormalities = false;
						$testresult = -1; 			// result NOT NULL
						$fk_user = $user->id;
						$date_start = ''; 
						$date_end = '';
						$rang = -1;
						$origin = $origin;			// not handeled by method/class
						$origin_id = $origin_id;	// not handeled by method/class
						$fk_parent_line = 0;		// not handeled by method/class
						$result = $object->addline($idprod, $fk_method, $abnormalities, $testresult, $fk_user, $date_start, $date_end, $rang, $origin, $origin_id, $fk_parent_line);
						dol_syslog(" addline idprod=".$idprod." idmethod=".$fk_method."  result=".$result, LOG_DEBUG);
					}
				}
			}
		}
		else {
			dol_syslog('No lines or method fetch_lines not existent', LOG_DEBUG);
		}
	}
	$res = $object->fetch_optionals();

	$head = samplesPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("Samples"), -1, $object->picto);

	$formconfirm = '';

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteSamples'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'ask_deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Update line
	if ($action == 'updateline' && $usercancreate && !GETPOST('cancel', 'alpha')) {
		dol_syslog('action=updateline',LOG_DEBUG);
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
		//$abnormalities = GETPOST('abnormalities');
		$fk_method = GETPOST('MethodID', 'int');
		
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
				$object->error = $langs->trans('ErrorStartDateGreaterEnd');
				$error++;
		}
		// check if result conformity: -1...error, 1...result within method's range, 0 result outside method's range
		$abnormalities = $object->checkConformity($testresult, $fk_method);
		if ($abnormalities < 0) {
			$error++;
		}
		
		// ERROR HANDLING
		if ($testresult == '') {
			setEventMessages($langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv('result')), null, 'errors');
			$error++;
		}
		$objResults = new Results($object->db);
		$objResults->fetch(GETPOST('lineid', 'int'));
		
		if (is_null($objResults->ref))
			$error++;
		
		// No Errors -> Update line
		if (!$error) {
			// Update line
			dol_syslog('action=updateline: ref='.$objResults->ref.' lineid='.GETPOST('lineid', 'int'), LOG_DEBUG);
			
			// Those are not changed:
			//$objResults->fk_samples = $this->id;
			//$objResults->fk_method = $fk_method;
			//$objResults->rang = $ranktouse;
			$objResults->status = Results::STATUS_DRAFT; // Line (Result) set to STATUS_DRAFT -> ID is unchanged => no use of it for now
			
			$objResults->fk_user = $fk_user;
			$objResults->result = $testresult;
			$objResults->start = $date_start;
			$objResults->end = $date_end;
			$objResults->abnormalities = $abnormalities;

			$result = $objResults->update($user);
			// method not defined:
			//$object->updateline($abnormalities, $testresult, $fk_user, $date_start, $date_end,);
		
			if ($result > 0) {
				// Generate Document
				$object->PrintReport();

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
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
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
	/*
	 // Ref customer
	 $morehtmlref.=$form->editfieldkey("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', 0, 1);
	 $morehtmlref.=$form->editfieldval("RefCustomer", 'ref_client', $object->ref_client, $object, 0, 'string', '', null, null, '', 1);
	 // Thirdparty
	 $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . (is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	 // Project
	 if (! empty($conf->projet->enabled))
	 {
	 $langs->load("projects");
	 $morehtmlref .= '<br>'.$langs->trans('Project') . ' ';
	 if ($permissiontoadd)
	 {
	 //if ($action != 'classify') $morehtmlref.='<a class="editfielda" href="' . $_SERVER['PHP_SELF'] . '?action=classify&amp;id=' . $object->id . '">' . img_edit($langs->transnoentitiesnoconv('SetProject')) . '</a> ';
	 $morehtmlref .= ' : ';
	 if ($action == 'classify') {
	 //$morehtmlref .= $form->form_project($_SERVER['PHP_SELF'] . '?id=' . $object->id, $object->socid, $object->fk_project, 'projectid', 0, 0, 1, 1);
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
	 $morehtmlref .= ': '.$proj->getNomUrl();
	 } else {
	 $morehtmlref .= '';
	 }
	 }
	 }*/
	
	// Label
	$morehtmlref.=$form->editfieldkey("SAlabelSampleName", 'label', $object->label, $object, $user->rights->lims->samples->write, 'string', '', 0, 1);
	$morehtmlref.=$form->editfieldval("SAlabelSampleName", 'label', $object->label, $object, $user->rights->lims->samples->write, 'string', '', null, null, '', 1);
	// Thirdparty
	$morehtmlref.='<br>'.$langs->trans('SAlabelCustomer').' : '.(is_object($object->thirdparty) ? $object->thirdparty->getNomUrl(1) : '');
	
	// Project
	if (!empty($conf->projet->enabled))
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

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	$keyforbreak='date';						// We change column with this field
	unset($object->fields['label']);			// Hide field already shown in banner
	unset($object->fields['fk_soc']);			// Hide field already shown in banner
	unset($object->fields['fk_project']);		// Hide field already shown in banner
	unset($object->fields['note_private']);		// Hide field 

	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';

	print dol_get_fiche_end();


	/*
	 * Lines
	 */

	if (!empty($object->table_element_line))
	{
		// Show object lines
		$result = $object->getLinesArray();

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
			//if (empty($user->socid)) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail').'</a>'."\n";
			//}

			// Back to draft
			if ($object->status == $object::STATUS_VALIDATED) {
				if ($permissiontovalidate) {
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes">'.$langs->trans("SetToDraft").'</a>';
				}
			}

			// Modify
			if ($permissiontoadd && ($object->status != $object::STATUS_VALIDATED)) {
				print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit">'.$langs->trans("Modify").'</a>'."\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Modify').'</a>'."\n";
			}

			// Validate
			if ($object->status == $object::STATUS_DRAFT) {
				if ($permissiontovalidate) {
					if (empty($object->table_element_line) || (is_array($object->lines) && count($object->lines) > 0))
					{
						print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes">'.$langs->trans("Validate").'</a>';
					} else {
						$langs->load("errors");
						print '<a class="butActionRefused" href="" title="'.$langs->trans("ErrorAddAtLeastOneLineFirst").'">'.$langs->trans("Validate").'</a>';
					}
				}
			}

			// Clone
			if ($permissiontoadd) {
				print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&socid='.$object->socid.'&action=clone&object=samples">'.$langs->trans("ToClone").'</a>'."\n";
			}

			/*
			if ($permissiontoadd)
			{
				if ($object->status == $object::STATUS_ENABLED) {
					print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=disable">'.$langs->trans("Disable").'</a>'."\n";
				} else {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=enable">'.$langs->trans("Enable").'</a>'."\n";
				}
			}
			if ($permissiontoadd)
			{
				if ($object->status == $object::STATUS_VALIDATED) {
					print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=close">'.$langs->trans("Cancel").'</a>'."\n";
				} else {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=reopen">'.$langs->trans("Re-Open").'</a>'."\n";
				}
			}
			*/

			// Delete (need delete permission, or if draft, just need create/modify permission)
			if ($permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd))
			{
				print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete&amp;token='.newToken().'">'.$langs->trans('Delete').'</a>'."\n";
			} else {
				print '<a class="butActionRefused classfortooltip" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('Delete').'</a>'."\n";
			}
		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}
		
	if ($action != 'presend') {
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 1;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->lims->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $user->rights->lims->samples->read; // If you can read, you can build the PDF to read content
			$delallowed = $user->rights->lims->samples->write; // If you can create/edit, you can remove a file on card
			if (is_null($object->model_pdf))
				$object->model_pdf = 'lims_testreport';
			
				dol_syslog('action='.$action.' filedir='.$filedir, LOG_DEBUG);
	
			print $formfile->showdocuments('lims:Samples', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang, '', $object, 0, 'remove_file_comfirm');
			//print $formfile->showdocuments('lims', $objref, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang, '', $object, 0, 'remove_file_comfirm');
		}

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
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail = 'samples';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->lims->dir_output;
	$trackid = 'samples'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
