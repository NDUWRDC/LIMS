<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020  Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2020       David Bensel            <david@bensel.cc>
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
 * 	\defgroup   lims     Module LIMS
 *  \brief      LIMS module descriptor.
 *
 *  \file       htdocs/lims/core/modules/modLIMS.class.php
 *  \ingroup    lims
 *  \brief      Description and activation file for module LIMS
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module LIMS
 */
class modLIMS extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
        $this->numero = 207150;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'lims';
		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "other";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleLIMSName' not found (LIMS is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleLIMSDesc' not found (LIMS is name of module).
		$this->description = "LIMSDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "LIMS description (Long)";
		$this->editor_name = 'David Bensel';
		$this->editor_url = 'https://www.nduwrdc.org';
		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '0.1.0';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where LIMS is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'generic';
		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 1,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/lims/css/lims.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/lims/js/lims.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
					//'printObjectLineTitle'),
					'data' => array(
						'samplescard','limitscard','invoicecard','equipmentlist'
					),
					'entity' => '0',
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
		);
		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/lims/temp","/lims/subdir");
		$this->dirs = array("/lims/temp");
		// Config pages. Put here list of php page, stored into lims/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@lims");
		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array();
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->langfiles = array("lims@lims");
		$this->phpmin = array(5, 5); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(11, -3); // Minimum version of Dolibarr required by module
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'LIMSWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('LIMS_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('LIMS_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array(
			1 => array('LIMS_PREFIX_SAMPLES', 'chaine', 'SA', 'Pre-fix for Sample objects', 1, 'allentities', 1),
			2 => array('LIMS_PREFIX_METHODS', 'chaine', 'ME', 'Pre-fix for Method objects', 1, 'allentities', 1),
			3 => array('LIMS_PREFIX_RESULTS', 'chaine', 'RE', 'Pre-fix for Result objects', 1, 'allentities', 1),
			4 => array('LIMS_PREFIX_LIMITS', 'chaine', 'LI', 'Pre-fix for Limit objects', 1, 'allentities', 1),
			5 => array('LIMS_PREFIX_EQUIPMENT', 'chaine', 'EQ', 'Pre-fix for Equipment objects', 1, 'allentities', 1),
			6 => array('SAMPLES_ADDON_PDF', 'chaine', 'lims_testreport', 'PDF template for Sample and Test Report', 1, 'allentities', 1), //copied from Crabe. Original used for invoices.
			7 => array('LIMS_SUBPERMCATEGORY_FOR_DOCUMENTS', 'chaine', 'report', 'Used to access reports.', 1, 'allentities', 1),
        );
		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->lims) || !isset($conf->lims->enabled)) {
			$conf->lims = new stdClass();
			$conf->lims->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@lims:$user->rights->lims->read:/lims/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@lims:$user->rights->othermodule->read:/lims/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries = array();
		/* Example:
		$this->dictionaries=array(
			'langs'=>'lims@lims',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."table1", MAIN_DB_PREFIX."table2", MAIN_DB_PREFIX."table3"),
			// Label of tables
			'tablib'=>array("Table1", "Table2", "Table3"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f', 'SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),
			// Sort order
			'tabsqlsort'=>array("label ASC", "label ASC", "label ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label", "code,label", "code,label"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label", "code,label", "code,label"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid", "rowid", "rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->lims->enabled, $conf->lims->enabled, $conf->lims->enabled)
		);
		*/

		// Boxes/Widgets
		// Add here list of php file(s) stored in lims/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'limswidget1.php@lims',
			//      'note' => 'Widget provided by LIMS',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/lims/class/equipment.class.php',
			//      'objectname' => 'Equipment',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->lims->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->lims->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->lims->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();

		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'View Samples of LIMS'; // Permission label
        $this->rights[$r][4] = 'samples'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Create/Update Samples of LIMS'; // Permission label
        $this->rights[$r][4] = 'samples'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Validate and Delete and Samples of LIMS'; // Permission label
        $this->rights[$r][4] = 'samples'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'View Results of LIMS'; // Permission label
        $this->rights[$r][4] = 'results'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Create/Update Results of LIMS'; // Permission label
        $this->rights[$r][4] = 'results'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Validate and Delete Results (Sample-Lines) of LIMS'; // Permission label
        $this->rights[$r][4] = 'results'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'View Methods of LIMS'; // Permission label
        $this->rights[$r][4] = 'methods'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Create/Update Methods of LIMS'; // Permission label
        $this->rights[$r][4] = 'methods'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Validate and Delete Methods of LIMS'; // Permission label
        $this->rights[$r][4] = 'methods'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'View Limits of LIMS'; // Permission label
        $this->rights[$r][4] = 'limits'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Create/Update Limits of LIMS'; // Permission label
        $this->rights[$r][4] = 'limits'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
        $this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
        $this->rights[$r][1] = 'Validate and Delete Limits of LIMS'; // Permission label
        $this->rights[$r][4] = 'limits'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
        $r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'View Equipment'; // Permission label
		$this->rights[$r][4] = 'equipment'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update Equipment'; // Permission label
		$this->rights[$r][4] = 'equipment'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Validate and Delete Equipment'; // Permission label
		$this->rights[$r][4] = 'equipment'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'View Reports of LIMS'; // Permission label
		$this->rights[$r][4] = 'report'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->lims->level1->level2)
		$r++;
		/* END MODULEBUILDER PERMISSIONS */
		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++] = array(
			'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top', // This is a Top menu entry
			'titre'=>'ModuleLIMSName',
			'mainmenu'=>'lims',
			'leftmenu'=>'',
			'url'=>'/lims/limsindex.php',
			'langs'=>'lims@lims', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->lims->enabled', // Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled.
			'perms'=>'1', // Use 'perms'=>'$user->rights->lims->equipment->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU SAMPLES */
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Samples',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_samples',
			'url'=>'/lims/samples_list.php',  // For now also display list - may be changed later
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_samples',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New Samples',
            'mainmenu'=>'lims',
            'leftmenu'=>'lims_samples_new',
			'url'=>'/lims/samples_card.php?action=create',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->samples->write',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_samples',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List Samples',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_samples_list',
			'url'=>'/lims/samples_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->samples->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		/* END MODULEBUILDER LEFTMENU SAMPLES */
		/* BEGIN MODULEBUILDER LEFTMENU RESULTS PART OF SAMPLES */
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_samples',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List Results',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_results_list',
			'url'=>'/lims/results_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->results->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		/*$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_results',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New Result',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_results_new',
			'url'=>'/lims/results_card.php?action=create',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);*/
		/* BEGIN MODULEBUILDER LEFTMENU RESULTS PART OF SAMPLES*/
		/* BEGIN LEFTMENU METHODS */
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Methods',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_methods',
			'url'=>'/lims/methods_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->methods->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_methods',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New Method',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_methods_new',
			'url'=>'/lims/methods_card.php?action=create',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->methods->write',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_methods',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List Methods',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_methods_list',
			'url'=>'/lims/methods_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->methods->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		/* END LEFTMENU METHODS */
		/* BEGIN LEFTMENU EQUIPMENT */
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=lims',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'Equipment',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_equipment',
			'url'=>'/lims/equipment_list.php',
			'langs'=>'lims@lims',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1100+$r,
			'enabled'=>'$conf->lims->enabled',  // Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->lims->equipment->read',			                // Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_equipment',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New Equipment',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_equipment_new',
			'url'=>'/lims/equipment_card.php?action=create',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->equipment->write',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_equipment',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List Equipment',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_equipment_list',
			'url'=>'/lims/equipment_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->equipment->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		/* END MODULEBUILDER LEFTMENU EQUIPMENT */
		/* BEGIN LEFTMENU LIMITS */
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'Limits',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_limits',
			'url'=>'/lims/limits_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'1',
			'target'=>'$user->rights->lims->limits->read',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_limits',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'New Limit Set',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_limits_new',
			'url'=>'/lims/limits_card.php?action=create',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->limits->write',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=lims,fk_leftmenu=lims_limits',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List Limits',
			'mainmenu'=>'lims',
			'leftmenu'=>'lims_limits_list',
			'url'=>'/lims/limits_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'lims@lims',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->lims->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->lims->enabled',
			// Use 'perms'=>'$user->rights->lims->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->lims->limits->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		/* BEGIN LEFTMENU LIMITS */

		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT SAMPLES */
		/*
		$langs->load("lims@lims");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='SamplesLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='samples@lims';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Samples'; $keyforclassfile='/mymobule/class/samples.class.php'; $keyforelement='samples@lims';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'SamplesLine'; $keyforclassfile='/lims/class/samples.class.php'; $keyforelement='samplesline@lims'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='samples'; $keyforaliasextra='extra'; $keyforelement='samples@lims';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='samplesline'; $keyforaliasextra='extraline'; $keyforelement='samplesline@lims';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('samplesline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'samples as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'samples_line as tl ON tl.fk_samples = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('samples').')';
		$r++; */
		/* END MODULEBUILDER EXPORT SAMPLES */
		/* BEGIN MODULEBUILDER EXPORT EQUIPMENT */
		/*
		$langs->load("lims@lims");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='EquipmentLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='equipment@lims';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'Equipment'; $keyforclassfile='/lims/class/equipment.class.php'; $keyforelement='equipment@lims';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'EquipmentLine'; $keyforclassfile='/lims/class/equipment.class.php'; $keyforelement='equipmentline@lims'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='equipment'; $keyforaliasextra='extra'; $keyforelement='equipment@lims';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='equipmentline'; $keyforaliasextra='extraline'; $keyforelement='equipmentline@lims';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('equipmentline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'equipment as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'equipment_line as tl ON tl.fk_equipment = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('equipment').')';
		$r++; */
		/* END MODULEBUILDER EXPORT EQUIPMENT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT SAMPLES */
		/*
		 $langs->load("lims@lims");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='SamplesLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='samples@lims';
		 $keyforclass = 'Samples'; $keyforclassfile='/mymobule/class/samples.class.php'; $keyforelement='samples@lims';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='samples'; $keyforaliasextra='extra'; $keyforelement='samples@lims';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'samples as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('samples').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT SAMPLES */
		/* BEGIN MODULEBUILDER IMPORT EQUIPMENT */
		/*
		 $langs->load("lims@lims");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='EquipmentLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='equipment@lims';
		 $keyforclass = 'Equipment'; $keyforclassfile='/lims/class/equipment.class.php'; $keyforelement='equipment@lims';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='equipment'; $keyforaliasextra='extra'; $keyforelement='equipment@lims';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'equipment as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('equipment').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT EQUIPMENT */
	}
/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/lims/sql/');
		if ($result < 0) return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')

		// Create extrafields during init
		//include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		//$extrafields = new ExtraFields($this->db);
		//$result1=$extrafields->addExtraField('lims_myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'lims@lims', '$conf->lims->enabled');
		//$result2=$extrafields->addExtraField('lims_myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'lims@lims', '$conf->lims->enabled');
		//$result3=$extrafields->addExtraField('lims_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'lims@lims', '$conf->lims->enabled');
		//$result4=$extrafields->addExtraField('lims_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'lims@lims', '$conf->lims->enabled');
		//$result5=$extrafields->addExtraField('lims_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'lims@lims', '$conf->lims->enabled');

		// Permissions
		$this->remove($options);

		$sql = array();

		// Document templates
		$moduledir = 'lims';
		$myTmpObjects = array();
		$myTmpObjects['Equipment']=array('includerefgeneration'=>0, 'includedocgeneration'=>0);
		dol_syslog(__METHOD__.' module parts = '.var_export($this->module_parts, true), LOG_DEBUG);

/*		if (is_array($this->module_parts) && !empty($this->module_parts)) {
			foreach ($this->module_parts as $key => $value)
*/
		foreach ($myTmpObjects as $myTmpObjectKey => $myTmpObjectArray) {
			//if ($myTmpObjectKey == 'Equipment') continue;
			if ($myTmpObjectArray['includerefgeneration']) {
				$src=DOL_DOCUMENT_ROOT.'/install/doctemplates/lims/template_equipments.odt';
				$dirodt=DOL_DATA_ROOT.'/doctemplates/lims';
				$dest=$dirodt.'/template_equipments.odt';

				if (file_exists($src) && ! file_exists($dest))
				{
					require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
					dol_mkdir($dirodt);
					$result=dol_copy($src, $dest, 0, 0);
					if ($result < 0)
					{
						$langs->load("errors");
						$this->error=$langs->trans('ErrorFailToCopyFile', $src, $dest);
						return 0;
					}
				}

				$sql = array_merge($sql, array(
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'standard_".strtolower($myTmpObjectKey)."' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('standard_".strtolower($myTmpObjectKey)."','".strtolower($myTmpObjectKey)."',".$conf->entity.")",
					"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'generic_".strtolower($myTmpObjectKey)."_odt' AND type = '".strtolower($myTmpObjectKey)."' AND entity = ".$conf->entity,
					"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('generic_".strtolower($myTmpObjectKey)."_odt', '".strtolower($myTmpObjectKey)."', ".$conf->entity.")"
				));
				dol_syslog(__METHOD__.' sql = '.var_export($sql, true), LOG_DEBUG);
			}
		}

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}