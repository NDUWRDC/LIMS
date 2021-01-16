<?php
/* Copyright (C) 2020 Module Generator
 * Copyright (C) 2020 David Bensel <david.bensel@gmail.com>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    lims/class/actions_lims.class.php
 * \ingroup lims
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

 // DoTo: Move to place where required
dol_include_once('/lims/class/samples.class.php', 'Samples');
dol_include_once('/lims/class/methods.class.php', 'Methods');
dol_include_once('/lims/class/equipment.class.php', 'Equipment');
dol_include_once('/lims/class/results.class.php', 'Results');
dol_include_once('/lims/class/limits.class.php', 'Limits');
dol_include_once('/lims/class/lims_functions.class.php', 'lims_functions');
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';

/**
 * Class ActionsLIMS
 */
class ActionsLIMS
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/* START MODULE GENERATOR */

	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		dol_syslog(get_class($this).'::doActions action='.$action.' element='.$object->element, LOG_DEBUG);

		// /lims/equipment_list.php => GENERATE button clicked
		// No Equipment-object is selected (list view) -> we select first in list
		// to make sure Equipment::setDocModel is successful-
		if ($action=='builddoc' && $object->element=='equipment') {
			$records = array();
			$records = $object->fetchAll();
			$obj_copy = new Equipment($object->db);
			$obj_copy = reset($records); // set internal pointer to first element
			if(!$obj_copy) {
				$error++;
				// TODO: Message to screen "No equipment listed."
			}
			else {
				// 1st available object is picked
				$object->fetch($obj_copy->id);
				if (!$object->model_pdf) {
					$object->model_pdf = $conf->global->EQUIPMENT_LIST_PDF;
				}
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'doActions context '.$parameters['currentcontext'];
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error doActions context '.$parameters['currentcontext'];
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			foreach ($parameters['toselect'] as $objectid)
			{
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("LIMSMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs, $db;
		global $hookmanager;

		$ret = 0; 
		
		dol_syslog(get_class($this).'::executeHooks action='.$action, LOG_DEBUG);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('equipmentlist')))			// do something only for the context 'equipmentlist'
		{
			dol_syslog('context=equipmentlist: '.get_class($object), LOG_DEBUG);
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$ret = 0;
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("lims@lims");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'lims') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("LIMS");
			$this->results['picto'] = 'lims@lims';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->lims->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}
	/* END MODULE GENERATOR */

	/**
	 * Overriding the printObjectLineTitle function : replacing the parent function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	
	// name of hook = printObjectLineTitle
	// context = samplescard
	function printObjectLineTitle($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		dol_syslog(__METHOD__.' hook on printObjectLineTitle, paramters='.var_export($paramters, true).' action='.$action, LOG_DEBUG);

		if ($object->element == 'facture' )
		{
			return 0;
		}
		
		if ($object->element == 'samples' )
		{
		  // do something only for the context 'samplescard'
			$this->ObjectlinesTitleSample();
		}
		
		if ($object->element == 'limits' )
		{
		  // do something only for the context 'limitscard'
			$this->ObjectlinesTitleLimits();
		}
		
		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 1; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function printObjectLine($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		dol_syslog(__METHOD__.' hook on printObjectLine, paramters='.var_export($paramters, true).' action='.$action, LOG_DEBUG);
		
		if ($object->element == 'facture' )
		{
			return 0;
		}
		
		if ($object->element == 'samples' )
		{
			$selected = $parameters['selected'];
			$line = $parameters['line'];
			// Line in view mode
			if ($action != 'editline' || $selected != $line->id) 
			{
				//dol_syslog(__METHOD__.' VIEW LINE #='.$line->id, LOG_DEBUG);
				$this->ObjectlineViewSamples($object, $parameters['line'], $parameters['num'], $parameters['i']);
			}
			// Line in update mode
			if ($action == 'editline' && $selected == $line->id)
			{
				//dol_syslog(__METHOD__.' EDIT LINE #='.$line->id, LOG_DEBUG);
				$this->ObjectlineEditSamples($object, $line, $parameters['i']);
			}
		}
		
		if ($object->element == 'limits' )
		{
			$selected = $parameters['selected'];
			$line = $parameters['line'];
			// Line in view mode
			if ($action != 'editline' || $selected != $line->id) 
			{	
				//dol_syslog(__METHOD__.' VIEW LINE #='.$line->id, LOG_DEBUG);
				$this->ObjectlineViewlimits($object, $parameters['line'], $parameters['num'], $parameters['i']);
			}
			// Line in update mode
			if ($action == 'editline' && $selected == $line->id)
			{
				//dol_syslog(__METHOD__.' EDIT LINE #='.$line->id, LOG_DEBUG);
				$this->ObjectlineEditLimits($object, $line, $parameters['i']);
			}
		}
		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 1; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function formAddObjectLine($parameters, &$object, &$action, $hookmanager)
	{
		$error = 0; // Error counter
		$myvalue = 'test'; // A result value

		dol_syslog(__METHOD__.' hook on formAddObjectLine, paramters='.var_export($parameters, true).' action='.$action, LOG_DEBUG);
		
		if ($object->element == 'facture' )
		{
			return 0;
		}
		
		if ($object->element == 'samples' )
		{
		  // do something only for the context 'somecontext'
			$this->ObjectlineCreateSample($object);
		}

		if ($object->element == 'limits' )
		{
		  // do something only for the context 'somecontext'
			$this->ObjectlineCreateLimits($object);
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 1; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
	
	function ObjectlinesTitleSample()
	{
		global $langs;

		print "<!-- BEGIN PHP LIMS ObjectlinesTitle Sample-->\n";

		// Title line
		print "<thead>\n";

		print '<tr class="liste_titre nodrag nodrop">';

		// Adds a line numbering column
		if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) print '<td class="linecolnum center">&nbsp;</td>';

		// Description => $methods->label
		print '<td class="linecoldescription">'.$langs->trans('Description').'</td>';
		//dol_syslog(__METHOD__.' $this->element='.$this->element, LOG_DEBUG);
		if ($this->element == 'samples')
		{
			//print '<td class="linerefsupplier"><span id="title_fourn_ref">'.$langs->trans("SupplierRef").'</span></td>';
		}

		// Test-ID  => $results->ref
		print '<td class="linecoltestid left" style="width: 120px">'.$langs->trans('ResultID').'</td>';

		// Method
		print '<td class="linecolmethod left" style="width: 160px">'.$langs->trans('MethodMethod').'</td>';

		// Accuracy
		print '<td class="linecolaccuracy center" style="width: 80px">'.$langs->trans('MethodAccuracy').'</td>';
		
		// Range of Method
		print '<td class="linecolmethodrange center" style="width: 80px">'.$langs->trans('MethodRangeTitle').'</td>';

		// Abnormalities / Nonconformities
		print '<td class="linecolresultabnorm center" style="width: 80px">'.$langs->trans('ResultAbnormality').'</td>';

		/*/ Limit Standard lower (UNBS or other)
		print '<td class="linecolstandardlow center" style="width: 80px">'.$langs->trans('StandardLowerLimit').'</td>';

		// Limit Standard upper (UNBS or other)
		print '<td class="linecolstandardupper center" style="width: 80px">'.$langs->trans('StandardUpperLimit').'</td>';*/
		// Limits combined in one column
		print '<td class="linecolrange center" style="width: 80px">'.$langs->trans('StandardLimitTitle').'</td>';

		// Result
		print '<td class="linecolresult right" style="width: 80px">'.$langs->trans('Result').'</td>';

		// Method Unit
		print '<td class="linecolmethodunit left" style="width: 160px">'.$langs->trans('MethodUnit').'</td>';

		print '<td class="linecoledit"></td>'; // No width to allow autodim

		print '<td class="linecoldelete" style="width: 10px"></td>';

		print '<td class="linecolmove" style="width: 10px"></td>';

		if ($action == 'selectlines')
		{
			print '<td class="linecolcheckall center">';
			print '<input type="checkbox" class="linecheckboxtoggle" />';
			print '<script>$(document).ready(function() {$(".linecheckboxtoggle").click(function() {var checkBoxes = $(".linecheckbox");checkBoxes.prop("checked", this.checked);})});</script>';
			print '</td>';
		}

		print "</tr>\n";
		print "</thead>\n";

		print "<!-- END PHP LIMS ObjectlinesTitle Sample-->\n";
	}
	
	function ObjectlineViewSamples ($object, $line, $num, $i)
	{
		global $forceall, $senderissupplier, $inputalsopricewithtax, $outputalsopricetotalwithtax;
		global $langs;
		global $conf;
		global $form;
		global $permissiontodelete, $action, $isdraft;
		
		$method = new Methods($object->db);
		$method->fetch($line->fk_method);
		
		$equipment = new Equipment ($object->db);
		$equipment->fetch($method->fk_equipment);

		$product = new Product ($object->db);
		$product->fetch($equipment->fk_product);
		
		$usemargins = 0;
		if (!empty($conf->margin->enabled) && !empty($object->element) && in_array($object->element, array('samples', 'results', 'methods'))) $usemargins = 1;

		if (empty($dateSelector)) $dateSelector = 0;
		if (empty($forceall)) $forceall = 0;

		// add html5 elements
		$domData  = ' data-element="'.$line->element.'"';
		$domData .= ' data-id="'.$line->id.'"';
		$domData .= ' data-qty="'.$line->qty.'"';
		$domData .= ' data-product_type="'.$line->product_type.'"';


		$coldisplay = 0; ?>
		<!-- BEGIN ObjectlineView Samples LIMS -->
		<tr id="row-<?php print $line->id?>" class="drag drop oddeven" <?php print $domData; ?> >
		<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
			<td class="linecolnum center"><?php $coldisplay++; ?><?php print ($i + 1); ?></td>
		<?php } ?>
			<td class="linecoldescription minwidth300imp"><?php $coldisplay++; ?><div id="line_<?php print $line->id; ?>"></div>
		<?php
			$format = $conf->global->MAIN_USE_HOURMIN_IN_DATE_RANGE ? 'dayhour' : 'day';
			
			$text = $product->getNomUrl(1);
			
			if ($method->fk_equipment > 0)
			{	
				//dol_syslog('$method->fk_equipment > 0', LOG_DEBUG);

				print $form->textwithtooltip($text, $description, 3, '', '', $i, 0, (!empty($line->fk_parent_line) ?img_picto('', 'rightarrow') : ''));
			}
			else
			{
				$type = (!empty($line->product_type) ? $line->product_type : $line->fk_product_type);
				if ($type == 1) $text = img_object($langs->trans('Service'), 'service');
				else $text = img_object($langs->trans('Product'), 'product');

				if (!empty($line->label)) {
					$text .= ' <strong>'.$line->label.'</strong>';
					print $form->textwithtooltip($text, dol_htmlentitiesbr($line->description), 3, '', '', $i, 0, (!empty($line->fk_parent_line) ?img_picto('', 'rightarrow') : ''));
				} else {
					if (!empty($line->fk_parent_line)) print img_picto('', 'rightarrow');
					if (preg_match('/^\(DEPOSIT\)/', $line->description)) {
						$newdesc = preg_replace('/^\(DEPOSIT\)/', $langs->trans("Deposit"), $line->description);
						print $text.' '.dol_htmlentitiesbr($newdesc);
					}
					else {
						print $text.' '.dol_htmlentitiesbr($line->description);
					}
				}
			}

			// Show date range
			/*
			if ($line->element == 'facturedetrec') {
				if ($line->date_start_fill || $line->date_end_fill) print '<br><div class="clearboth nowraponall">';
				if ($line->date_start_fill) print $langs->trans('AutoFillDateFromShort').': '.yn($line->date_start_fill);
				if ($line->date_start_fill && $line->date_end_fill) print ' - ';
				if ($line->date_end_fill) print $langs->trans('AutoFillDateToShort').': '.yn($line->date_end_fill);
				if ($line->date_start_fill || $line->date_end_fill) print '</div>';
			}
			else {
				if ($line->date_start || $line->date_end) print '<br><div class="clearboth nowraponall">'.get_date_range($line->date_start, $line->date_end, $format).'</div>';
				//print get_date_range($line->date_start, $line->date_end, $format);
			}
			*/
			// Add description in form
			if ($method->fk_equipment > 0 ) // && !empty($conf->global->PRODUIT_DESC_IN_FORM))
			{
				//dol_syslog('Add description in form $line->fk_method->fk_equipment='.$method->fk_equipment, LOG_DEBUG);
				//print (!empty($product->description) && $product->description != $product->product_label) ? ' - '.dol_htmlentitiesbr($product->description) : '';

				print (!empty($method->label)) ? ' - '.$method->getNomUrl(1,'',0,'',-1,$method->label) : '';
			}
		//}

		// Test-ID
		if ($object->ref != ''){
			print '<td class="linecoltestid">';
			print $line->getNomUrl();
			print '</td>';
		}

		// Valid Method?
		if ($method->ref != ''){
			// Method-ISO
			print '<td class="linecolmethod">';
			print $method->standard;
			print '</td>';

			// Accuracy
			print '<td class="linecolaccuracy center">';
			print $method->accuracy;
			print '</td>';
			
			// Range
			print '<td class="linecolrange center"';
			// add color if result out of measurement range
			if ( (!is_null($method->range_lower) && $line->result < $method->range_lower) || (!is_null($method->range_upper) && $line->result > $method->range_upper) ) {
				print ' style="background-color: rgb(234,228,225)"'; //#butactiondeletebg
			}
			print '>';
			print $method->range_lower.' - '.$method->range_upper;
			print '</td>';

		}

		if ($object->ref != ''){
			// Abnormalities / Nonconformities
			print '<td class="linecolresultabnorm center">';
			print ($line->abnormalities ? $langs->trans('Yes') : $langs->trans('None'));
			print '</td>';
		}

		/*/ Lower Limit
		print '<td class="linecolstandardlower center">';
		print $line->minimum;
		print '</td>';
		// Upper Limit
		print '<td class="linecolstandardupper center">';
		print $line->maximum;
		print '</td>';*/
		
		// Limits combined in one column
		print '<td class="linecollimit center"';
		// add color if result out of limits
		if ( (!is_null($line->minimum) && $line->result < $line->minimum) || (!is_null($line->maximum) && $line->result > $line->maximum) )
			print ' style="background-color: rgb(234,228,225)"'; //#butactiondeletebg
		print '">';
		if (is_numeric($line->minimum) && is_numeric($line->maximum))
			print $line->minimum.' - '.$line->maximum;
		elseif (is_numeric($line->minimum) && !is_numeric($line->maximum))
			print '>= '.$line->minimum;
		elseif (!is_numeric($line->minimum) && is_numeric($line->maximum))
			print '<= '.$line->maximum;  // if $line->maximum = 0 it will 
		print '</td>';
			
		if ($object->ref != ''){
			// Result
			print '<td class="linecolresult right">';
			print lims_functions::numberFormatPrecision($line->result,$method->resolution);
			print '</td>';
		}

		if ($method->ref != ''){
			// Units
			print '<td class="linecolmethodunit left">';
			print $method->getUnit();
			print '</td>';
		}
		
		// Edit - Delete - Move up/down
		if ($object->status == $object::STATUS_DRAFT && $permissiontodelete && $action != 'selectlines') {
			print '<td class="linecoledit center">';
			$coldisplay++;
			if (!empty($disableedit)) {
			} else { ?>
				<a class="editfielda reposition" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->id.'#line_'.$line->id; ?>">
				<?php print img_edit().'</a>';
			}
			print '</td>';

			print '<td class="linecoldelete center">';
			$coldisplay++;
			if (empty($disableremove)) { 
				print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_deleteline&amp;lineid='.$line->id.'">';
				print img_delete();
				print '</a>';
			}
			print '</td>';

			if ($num > 1 && $conf->browser->layout != 'phone' && empty($disablemove)) {
				//dol_syslog('Linecoledit .... $num='.$num.' $i='.$i, LOG_DEBUG);
				print '<td class="linecolmove tdlineupdown center">';
				$coldisplay++;
				if ($i > 0) { ?>
					<a class="lineupdown" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=up&amp;rowid='.$line->id; ?>">
					<?php print img_up('default', 0, 'imgupforline'); ?>
					</a>
				<?php }
				if ($i < $num - 1) { ?>
					<a class="lineupdown" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=down&amp;rowid='.$line->id; ?>">
					<?php print img_down('default', 0, 'imgdownforline'); ?>
					</a>
				<?php }
				print '</td>';
			} else {
				print '<td '.(($conf->browser->layout != 'phone' && empty($disablemove)) ? ' class="linecolmove tdlineupdown center"' : ' class="linecolmove center"').'></td>';
				$coldisplay++;
			}
		} else {
			print '<td colspan="3"></td>';
			$coldisplay = $coldisplay + 3;
		}

		if ($action == 'selectlines') { ?>
			<td class="linecolcheck center"><input type="checkbox" class="linecheckbox" name="line_checkbox[<?php print $i + 1; ?>]" value="<?php print $line->id; ?>" ></td>
		<?php }

		print "</tr>\n";

		//Line extrafield
		if (!empty($extrafields))
		{
			print $line->showOptionals($extrafields, 'view', array('style'=>'class="drag drop oddeven"', 'colspan'=>$coldisplay), '', '', 1);
		}

		print "<!-- END ObjectlineView Samples LIMS -->\n";
	}

	function ObjectlineCreateSample($object)
	{
		global $conf, $langs, $form;
		global $user;
		
		if (!isset($dateSelector)) global $dateSelector; // Take global var only if not already defined into function calling (for example formAddObjectLine)
		global $forceall, $forcetoshowtitlelines;
		if (!isset($dateSelector)) $dateSelector = 1; // For backward compatibility
		elseif (empty($dateSelector)) $dateSelector = 0;
		if (empty($forceall)) $forceall = 0;
		
		// Define colspan for the button 'Add'
		$colspan = 3; // Columns: total ht + col edit + col delete
		
		//print $object->element;
		// Lines for extrafield
		$objectline = null;
		if (!empty($extrafields))
		{
			$objectline = new SamplesLine($object->db);
		}
		print "<!-- BEGIN ObjectlineCreate Sample LIMS -->\n";
		$nolinesbefore = (count($object->lines) == 0 || $forcetoshowtitlelines);
		if ($nolinesbefore) {
			?>
			<tr class="liste_titre<?php echo (($nolinesbefore || $object->element == 'contrat') ? '' : ' liste_titre_add_') ?> nodrag nodrop">
				<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
					<td class="linecolnum center"></td>
				<?php } ?>
				<td class="linecoldescription minwidth500imp" colspan="2">
					<div id="add"></div><span class="hideonsmartphone"><?php echo $langs->trans('AddNewLine'); ?></span><?php // echo $langs->trans("FreeZone"); ?>
				</td>
				<?php
				// Method
				print '<td class="linecolmethod left" style="width: 160px">'.$langs->trans('MethodMethod').'</td>';

				// Accuracy
				print '<td class="linecolaccuracy center" style="width: 80px">'.$langs->trans('MethodAccuracy').'</td>';
				
				// Lower and Upper Limit of Method (Measurement Range)
				print '<td class="linecolrange center" style="width: 80px">'.$langs->trans('MethodRangeTitle').'</td>';

				// Abnormalities / Nonconformities
				print '<td class="linecolresultabnorm center" style="width: 80px">'.$langs->trans('ResultAbnormality').'</td>';

				/*/ Limit Standard lower (UNBS or other)
				print '<td class="linecolstandardlow center" style="width: 80px">'.$langs->trans('StandardLowerLimit').'</td>';

				// Limit Standard upper (UNBS or other)
				print '<td class="linecolstandardupper center" style="width: 80px">'.$langs->trans('StandardUpperLimit').'</td>';*/
		
				// Limits combined in one column
				print '<td class="linecollimit center" style="width: 80px">'.$langs->trans('StandardLimitTitle').'</td>';

				// Result
				print '<td class="linecolresult right" style="width: 80px">'.$langs->trans('Result').'</td>';

				// Method Unit
				print '<td class="linecolmethodunit left" style="width: 160px">'.$langs->trans('MethodUnit').'</td>';
				?>
				<td class="linecoledit" colspan="<?php echo $colspan; ?>">&nbsp;</td>
			</tr>
			<?php
		}
		?>
		<tr class="pair nodrag nodrop nohoverpair<?php echo ($nolinesbefore || $object->element == 'contrat') ? '' : ' liste_titre_create'; ?>">
			<?php
			$coldisplay = 0;
			$coldisplay = 0;
			// Adds a line numbering column
			if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) {
				$coldisplay++;
				echo '<td class="nobottom linecolnum center"></td>';
			}
			?>
			<!-- Predefined product/service => LIMS only allows to select of products listed in methods -->
			
			<td class="nobottom linecoldescription minwidth300imp colspan=2"><?php $coldisplay++;?>
				<span class="prod_entry_mode_predef">
				<label form="prod_entry_mode_predef">
				<?php 
				echo $langs->trans('AddLineTitleSamples');
				
				echo '</label>';
				echo '<br>';
				$filtertype = '';  // ''=nofilter, 0=product, 1=service
				$statustoshow = 1; //1=Return all products, 0=Products not on sell, 1=Products on sell
				
				// select
				//$form->select_produits(GETPOST('idprod'), 'idprod', $filtertype, $conf->product->limit_size, 0, 1, 2, '', 0, array(), $buyer->id, '1', 0, 'maxwidth500', 1, '', GETPOST('combinations', 'array'));

				$sql = 'SELECT e.rowid as rowid, e.ref as eref, e.label as elabel, e.description as edescription, e.fk_product,';
				$sql .= ' m.rowid as mrowid, m.ref as mref, m.label, m.fk_equipment,';
				$sql .= ' p.rowid as prowid, p.ref, p.label as plabel, p.description as pdescription';
				$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as m';
				$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'lims_equipment as e ON e.rowid=m.fk_equipment'; 
				$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON p.rowid=e.fk_product';
				$sql .= ' GROUP BY e.rowid';  // don't show duplicates

				$nameID='ProdID'; 
				$idprod = lims_functions::DropDownProduct($sql, $nameID, $object, 'ref', '', '');
				
				GETPOST('ProdID','alpha', 2); // Only POST
				
				if ($idprod > 0){
					
					$sql = 'SELECT p.rowid, p.ref, p.label, p.description, p.fk_equipment';
					$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as p';
					$sql .= ' WHERE fk_equipment='.$idprod;
					
					$nameID='MethodID';
					
					$methodID = lims_functions::DropDownProduct($sql, $nameID, $object, 'label', '', '');
					GETPOST('MethodID','alpha', 2); // Only POST
				}
				// Test Start and End
				// Form::selectDate($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowlink = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '', $openinghours = '', $stepminutes = 1, $labeladddateof = '')
				print '<br>';
				print $langs->trans('From').' ';
				print $date_start=$form->selectDate('', 'date_start',1, 1, 0, "Start", 1, 1,0,'','','','',1);
				print '<br>';
				print $langs->trans('to');
				print '&emsp;'.$date_end=$form->selectDate('', 'date_end',1, 1, 0, "End", 1, 1,0,'','','','',1);
				
				// User who did the test
				$disable_edit = $user->rights->lims->samples->delete ? false : true; // Only Manager should be able to change user
				print '<br>';
				print $langs->trans('TestingTechnician').'<br>';
				//public function select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0');
				print $fk_user=$form->select_users($user->id, 'userid',0,null,$disable_edit);
				//print &user=select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0');
				
				?>
				</span>
			</td>
			
			 <!-- Test-ID -->
			<td class="nobottom linecoltestid left"><?php $coldisplay++; ?>
				
			</td>

			<!-- Method -->
			<td class="nobottom linecolmethod left"><?php $coldisplay++; ?>
				<input type="text" size="16" name="MethodStandard" id="MethodStandard" class="flat left" value="<?php echo (isset($_POST["MethodStandard"]) ?GETPOST("MethodStandard", 'alpha', 2) : ''); ?>" disabled>
			</td>

			<!-- Accuracy -->
			<td class="nobottom linecolaccuracy center"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodAccuracy" id="MethodAccuracy" class="flat center" value="<?php echo (isset($_POST["MethodAccuracy"]) ?GETPOST("MethodAccuracy", 'alpha', 2) : ''); ?>" disabled>
			</td>

			<!-- Measurement Range -->
			<td class="nobottom linecolrange center"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodRangeLower" id="MethodRangeLower" class="flat center" value="<?php echo (isset($_POST["MethodRangeLower"]) ?GETPOST("MethodRangeLower", 'alpha', 2) : ''); ?>" disabled>
				&nbsp;-&nbsp;
				<input type="text" size="5" name="MethodRangeUpper" id="MethodRangeUpper" class="flat center" value="<?php echo (isset($_POST["MethodRangeUpper"]) ?GETPOST("MethodRangeUpper", 'alpha', 2) : ''); ?>" disabled>
			</td>

			<!-- Abnormalities  / Nonconformities -->
			<td class="nobottom linecolabnormalities center"><?php $coldisplay++; ?>
				<?php 
				echo $form->selectyesno('abnormalities', $line->abnormalities, $option = 1, $disabled = true);
				?>
			</td>

			<!-- Limits combined in one column -->
			<td class="nobottom linecollimit center"><?php $coldisplay++; ?>
				<input type="text" size="5" name="LimitMinimum" id="LimitMinimum" class="flat center" value="<?php echo (isset($_POST["LimitMinimum"]) ?GETPOST("LimitMinimum", 'alpha', 2) : ''); ?>" disabled>
				&nbsp;-&nbsp;
				<input type="text" size="5" name="LimitMaximum" id="LimitMaximum" class="flat center" value="<?php echo (isset($_POST["LimitMaximum"]) ?GETPOST("LimitMaximum", 'alpha', 2) : ''); ?>" disabled>
			</td>
			
			<!-- Result -->
			<td class="nobottom linecolresult right"><?php $coldisplay++; ?>
				<input type="text" size="5" name="result" id="result" class="flat right" value="<?php echo (isset($_POST["result"]) ?GETPOST("result", 'alpha', 2) : 0); ?>">
			</td>
			
			 <!-- Unit -->
			<td class="nobottom linecolunit left"><?php $coldisplay++; ?>
				<input type="text" size="16" name="MethodUnit" id="MethodUnit" class="flat left" value="<?php echo (isset($_POST["MethodUnit"]) ?GETPOST("MethodUnit", 'alpha', 2) : ''); ?>" disabled>
			</td>
			
			<!-- ADD button -->
			<td class="nobottom linecoledit center valignmiddle" colspan="<?php echo $colspan; ?>">
				<input type="submit" class="button" value="<?php echo $langs->trans('Add'); ?>" name="addline" id="addline">
			</td>
		</tr>

		<?php
		if (is_object($objectline)) {
			print $objectline->showOptionals($extrafields, 'edit', array('colspan'=>$coldisplay), '', '', 1);
		}
		?>
			<script>
			// When changing MethodID, columns are set: MethodStandard, Accuracy, Lower, Upper, Unit
			$("#MethodID").change(function()
			{
				console.log("MethodID.change: value="+$(this).val());
				
				$.post('<?php echo dol_buildpath('/lims/methods_ajax.php?action=fetch',1);?>',
				{ 'idmethod': $(this).val(), 'idsample':<?php echo $object->id ?> },
					function(data) {
							jQuery("#MethodStandard").val(data.label);
							jQuery("#MethodAccuracy").val(data.accuracy);
							jQuery("#MethodRangeLower").val(data.rangelower);
							jQuery("#MethodRangeUpper").val(data.rangeupper);
							jQuery("#LimitMinimum").val(data.limitmin);
							jQuery("#LimitMaximum").val(data.limitmax);
							jQuery("#MethodUnit").val(data.unit);
					},
					'json'
				);
			});

			// When changing ProdID, options are populated with available methods for this product
			$("#ProdID").change(function()
			{
				console.log("ProdID.change: value="+$(this).val());

				$.post('<?php echo dol_buildpath('/lims/methods_ajax.php?action=fetch',1);?>',
				{ 'idprod': $(this).val() },
					function(data) {
							$('select[name="MethodID"]').empty();
							$.each(data, function(key, value) {
								$('select[name="MethodID"]').append('<option value="'+ key +'">'+ value +'</option>');
							});
					},
					'json'
				).done(function() {
					// This is to avoid a race condition where the field is not updated yet when the change-function is called.
					console.log("post done");
					$("#MethodID").change();	// Update other elements with new method details
					$("#MethodID").focus();		// focus on method selection
				});
			});
			
			$(function() {
				
				console.log("Page is loaded, fields to be updated");
				
				$("#MethodID").change();	// Update other elements with new method details
				$("#ProdID").focus();		// focus on method selection
				
			});
			</script>
			<!-- END ObjectlineCreate Sample LIMS-->
	<?php
	}
	
	// Copied from objectline_edit.tpl.php
	function ObjectlineEditSamples($object, $line, $i)
	{
		global $conf, $langs, $form;
		// Define colspan for the button 'Change'
		$colspan = 3; // Columns: col edit + col delete
		
		// Protection to avoid direct call of template
		if (empty($object) || !is_object($object))
		{
			dol_syslog(__METHOD__.'Object empty or not object', LOG_DEBUG);
			exit;
		}
		
		$method = new Methods($object->db);
		$method->fetch($line->fk_method);
		$equipment = new Equipment ($object->db);
		$equipment->fetch($method->fk_equipment);
		$product = new Product ($object->db);
		$product->fetch($equipment->fk_product);
		
		print "<!-- BEGIN ObjectlineEdit Samples LIMS -->\n";
		$coldisplay = 0;
		?>
		<tr class="oddeven tredited">
		<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
				<td class="linecolnum center"><?php $coldisplay++; ?><?php echo ($i + 1); ?></td>
		<?php }

		?>
		<td>
			<div id="line_<?php echo $line->id; $coldisplay++; ?>"></div>

			<input type="hidden" name="lineid" value="<?php echo $line->id; ?>">
			<input type="hidden" id="fk_parent_line" name="fk_parent_line" value="<?php echo $line->fk_parent_line; ?>">
			<input type="hidden" id="MethodID" name="MethodID" value="<?php echo $line->fk_method; ?>">
			<?php

			$text = $product->getNomUrl(1);		// PRODUCT->REF 
			if ($product > 0)
			{
				print $form->textwithtooltip($text, $description, 3, '', '', $i, 0, (!empty($line->fk_parent_line) ?img_picto('', 'rightarrow') : ''));
				print ' - '.$method->label;		// - METHOD->LABEL
			}
			
			print '<br>';						// DATE START OF TEST
			print ' '.$langs->trans('From').' ';
			print $form->selectDate($line->start, 'date_start', 1, 1, 0, "Start",  1, 1,0,'','','','',1);
			print '<br>';						// DATE END OF TEST
			print ' '.$langs->trans('to').' ';
			print '&emsp;'.$form->selectDate($line->end, 'date_end',1, 1, 0, "End", 1, 1,0,'','','','',1);
			print '<br>';						// USER WHO DID TEST 
			print $langs->trans('TestingTechnician').'<br>';
			print $form->select_users($line->fk_user, $line->fk_user);
		?>
		</td>
		
			?>
		</td>
		<td class="linecoltestid">
			<?php
			$coldisplay++;
			print $line->getNomUrl();	// RESULT-REF (TEST)
			?>
		</td>
		
		<td class="linecolmethod">
			<?php
			$coldisplay++;
			print $method->standard;	// METHOD
			?>
		</td>
		
		<td class="linecolaccuracy center">
			<?php
			$coldisplay++;
			print $method->accuracy; // Accuracy
			?>
		</td>

		<td class="linecolaccuracy center">
			<?php
			$coldisplay++;
			print $method->range_lower.' - '.$method->range_upper; // Range
			?>
		</td>
		
		<td class="linecolresultabnorm center">
			<?php
			$coldisplay++;
			echo $form->selectyesno('abnormalities', $line->abnormalities, $option = 1, $disabled = true); // Abnormalities / Nonconformities
			?>
		</td>
		<!--
		<td class="linecolstandardlower center">
			<?php
			//$coldisplay++;
			//print "";		// Lower Limit
			?>
		</td>

		<td class="linecolstandardupper center">
			<?php
			//$coldisplay++;
			//print "";		// Upper Limit
			?>
		</td>
		-->
		<!-- Limits combined in one column -->
		<td class="linecollimit center">
			<?php
			$coldisplay++;
			print $line->minimum.' - '.$line->maximum;	// Limit min - max
			?>
		</td>

		<td class="linecolresult right">
			<?php
			$coldisplay++;
			print '<input type="text" size="5" name="result" id="result" class="flat right" value="';print $line->result;// Result?>">
		</td>

		<td class="linecolmethodunit left">
			<?php
			$coldisplay++;
			print $method->getUnit(); // Unit
			?>
		</td>
		<td class="center valignmiddle" colspan="<?php echo $colspan; ?>"><?php $coldisplay += $colspan; ?>
			<input type="submit" class="button buttongen marginbottomonly" id="savelinebutton marginbottomonly" name="save" value="<?php echo $langs->trans("Save"); ?>"><br>
			<input type="submit" class="button buttongen marginbottomonly" id="cancellinebutton" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>">
		</td>
		<?php
		if (is_object($objectline)) {
			print $objectline->showOptionals($extrafields, 'edit', array('colspan'=>$coldisplay), '', '', 1);
		}
		?>
		</tr>
		<!-- END ObjectlineEDIT Sample LIMS -->
		<?php
	}
	
	function ObjectlinesTitleLimits()
	{
		global $langs;

		print "<!-- BEGIN PHP LIMS ObjectlinesTitle Limits-->\n";

		// Title line
		print "<thead>\n";

		print '<tr class="liste_titre nodrag nodrop">';

		// Adds a line numbering column
		if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) print '<td class="linecolnum center">&nbsp;</td>';

		// Description => $methods->label
		print '<td class="linecoldescription">'.$langs->trans('Description').'</td>';
		//dol_syslog(__METHOD__.' $this->element='.$this->element, LOG_DEBUG);
		if ($this->element == 'limits')
		{
			//print '<td class="linerefsupplier"><span id="title_fourn_ref">'.$langs->trans("SupplierRef").'</span></td>';
		}

		// Limit-Entry-ID  => $limitsline->ref 
		//print '<td class="linecoltestid left" style="width: 120px">'.$langs->trans('ResultID').'</td>';

		// Method
		print '<td class="linecolmethod left" style="width: 160px">'.$langs->trans('MethodMethod').'</td>';

		// Accuracy
		print '<td class="linecolaccuracy center" style="width: 80px">'.$langs->trans('MethodAccuracy').'</td>';
		
		/*
		// Lower Limit of Method
		print '<td class="linecolmethodlimitlow center" style="width: 80px">'.$langs->trans('MethodLowerLimit').'</td>';

		// Upper Limit of Method
		print '<td class="linecolmethodlimitupper center" style="width: 80px">'.$langs->trans('MethodUpperLimit').'</td>';
		*/
		
		// ??ToDo: Title with colspan=2 for Limits Lower and Upper

		// Method Unit
		print '<td class="linecolmethodunit left" style="width: 160px">'.$langs->trans('MethodUnit').'</td>';

		// Limit Standard lower (UNBS or other)
		print '<td class="linecolstandardlow center" style="width: 80px">'.$langs->trans('StandardLowerLimit').'</td>';

		// Limit Standard upper (UNBS or other)
		print '<td class="linecolstandardupper center" style="width: 80px">'.$langs->trans('StandardUpperLimit').'</td>';

		print '<td class="linecoledit"></td>'; // No width to allow autodim

		print '<td class="linecoldelete" style="width: 10px"></td>';

		print '<td class="linecolmove" style="width: 10px"></td>';

		if ($action == 'selectlines')
		{
			print '<td class="linecolcheckall center">';
			print '<input type="checkbox" class="linecheckboxtoggle" />';
			print '<script>$(document).ready(function() {$(".linecheckboxtoggle").click(function() {var checkBoxes = $(".linecheckbox");checkBoxes.prop("checked", this.checked);})});</script>';
			print '</td>';
		}

		print "</tr>\n";
		print "</thead>\n";

		print "<!-- END PHP LIMS ObjectlinesTitle Limits-->\n";
	}

	function ObjectlineCreateLimits($object)
	{
		global $conf, $langs, $form;
		

		if (!isset($dateSelector)) global $dateSelector; // Take global var only if not already defined into function calling (for example formAddObjectLine)
		global $forceall, $forcetoshowtitlelines;
		if (!isset($dateSelector)) $dateSelector = 1; // For backward compatibility
		elseif (empty($dateSelector)) $dateSelector = 0;
		// Define colspan for the button 'Add'
		$colspan = 2; // Columns: col edit + col delete

		//print $object->element;
		// Lines for extrafield
		$objectline = null;
		if (!empty($extrafields))
		{
			$objectline = new LimitsLine($object->db);
		}
		print "<!-- BEGIN ObjectlineCreate Limits LIMS -->\n";
		$nolinesbefore = (count($object->lines) == 0 || $forcetoshowtitlelines);
		if ($nolinesbefore) {
			?>
			<tr class="liste_titre<?php echo ($nolinesbefore ? '' : ' liste_titre_add_') ?> nodrag nodrop">
				<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
				<td class="linecolnum center"></td>
				<?php } ?>
				<td class="linecoldescription minwidth500imp">
					<div id="add"></div><span class="hideonsmartphone"><?php echo $langs->trans('AddNewLine'); ?></span><?php // echo $langs->trans("FreeZone"); ?>
				</td>
				<?php
				// Method
				print '<td class="linecolmethod left" style="width: 160px">'.$langs->trans('MethodMethod').'</td>';

				// Accuracy
				print '<td class="linecolaccuracy center" style="width: 80px">'.$langs->trans('MethodAccuracy').'</td>';
				
				// Method Unit
				print '<td class="linecolmethodunit left" style="width: 160px">'.$langs->trans('MethodUnit').'</td>';

				/*
				// Lower Limit of Method
				print '<td class="linecolmethodlimitlow center" style="width: 80px">'.$langs->trans('MethodLowerLimit').'</td>';

				// Upper Limit of Method
				print '<td class="linecolmethodlimitupper center" style="width: 80px">'.$langs->trans('MethodUpperLimit').'</td>';
				*/

				// Limit Standard lower (UNBS or other)
				print '<td class="linecolstandardlow center" style="width: 80px">'.$langs->trans('StandardLowerLimit').'</td>';

				// Limit Standard upper (UNBS or other)
				print '<td class="linecolstandardupper center" style="width: 80px">'.$langs->trans('StandardUpperLimit').'</td>';

				?>
				<td class="linecoledit" colspan="<?php echo $colspan; ?>">&nbsp;</td>
			</tr>
			<?php
		}
		?>
		<tr class="pair nodrag nodrop nohoverpair<?php echo ($nolinesbefore) ? '' : ' liste_titre_create'; ?>">
			<?php
			$coldisplay = 0;
			// Adds a line numbering column
			if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) {
				$coldisplay++;
				echo '<td class="nobottom linecolnum center"></td>';
			}
			?>
			<!-- Predefined product/service => LIMS only allows to select of products listed in methods -->
			
			<td class="nobottom linecoldescription minwidth300imp colspan=2"><?php $coldisplay++;?>
				<span class="prod_entry_mode_predef">
				<label form="prod_entry_mode_predef">
				<?php 
				echo $langs->trans('AddLineTitleLimits');
				
				echo '</label>';
				$filtertype = '';  // ''=nofilter, 0=product, 1=service
				$statustoshow = 1; //1=Return all products, 0=Products not on sell, 1=Products on sell
				
				// select
				//$form->select_produits(GETPOST('idprod'), 'idprod', $filtertype, $conf->product->limit_size, 0, 1, 2, '', 0, array(), $buyer->id, '1', 0, 'maxwidth500', 1, '', GETPOST('combinations', 'array'));
				
				$sql = 'SELECT rowid , ref, label, fk_equipment';
				$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods';
				
				$nameID='MethodID'; 
				
				$idmethod = lims_functions::DropDownProduct($sql, $nameID, $object, 'label', '', '');
				
				?>
				</span>
			</td>
			
			<!-- Method -->
			<td class="nobottom linecolmethod left"><?php $coldisplay++; ?>
				<input type="text" size="16" name="MethodStandard" id="MethodStandard" class="flat left" value="<?php echo (isset($_POST["MethodStandard"]) ?GETPOST("MethodStandard", 'alpha', 2) : ''); ?>" disabled>
			</td>

			<!-- Accuracy -->
			<td class="nobottom linecolaccuracy center"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodAccuracy" id="MethodAccuracy" class="flat center" value="<?php echo (isset($_POST["MethodAccuracy"]) ?GETPOST("MethodAccuracy", 'alpha', 2) : ''); ?>" disabled>
			</td>

			 <!-- Unit -->
			<td class="nobottom linecolunit left"><?php $coldisplay++; ?>
				<input type="text" size="16" name="MethodUnit" id="MethodUnit" class="flat left" value="<?php echo (isset($_POST["MethodUnit"]) ?GETPOST("MethodUnit", 'alpha', 2) : ''); ?>" disabled>
			</td>

			 <!-- Lower limit -->
			<td class="nobottom linecollowerlimit right"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodLower" id="MethodLower" class="flat center" value="<?php echo (isset($_POST["MethodLower"]) ?GETPOST("MethodLower", 'alpha', 2) : ''); ?>">
			</td>

			 <!-- Upper limit -->
			<td class="nobottom linecolupperlimit right"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodUpper" id="MethodUpper" class="flat center" value="<?php echo (isset($_POST["MethodUpper"]) ?GETPOST("MethodUpper", 'alpha', 2) : ''); ?>">
			</td>

			<!-- ADD button -->
			<td class="nobottom linecoledit center valignmiddle" colspan="<?php echo $colspan; ?>">
				<input type="submit" class="button" value="<?php echo $langs->trans('Add'); ?>" name="addline" id="addline">
			</td>
		</tr>

		<?php
		if (is_object($objectline)) {
			print $objectline->showOptionals($extrafields, 'edit', array('colspan'=>$coldisplay), '', '', 1);
		}
		
		?>
		<script>
		// When changing MethodID, columns are set: MethodStandard, Accuracy, Unit
		$("#MethodID").change(function()
		{
			console.log("MethodID.change: value="+$(this).val());
			
			$.post('<?php echo dol_buildpath('/lims/methods_ajax.php?action=fetch',1); ?>',
			{ 'idmethod': $(this).val(), 'idsample':<?php echo $object->id ?> },
				function(data) {
						jQuery("#MethodStandard").val(data.label);
						jQuery("#MethodAccuracy").val(data.accuracy);
						jQuery("#MethodUnit").val(data.unit);
				},
				'json'
			);
		});
		
		$(function() {
				
			console.log("Page is loaded, fields to be updated");
			
			$("#MethodID").change();	// Update other elements with new method details
			$("#MethodID").focus();		// focus on method selection
		});
		</script>
		<!-- END ObjectlineCreate Sample LIMS-->
		<?php
	}
	
	function ObjectlineViewLimits ($object, $line, $num, $i)
	{
		global $forceall;
		global $langs;
		global $conf;
		global $form;
		global $permissiontodelete, $action, $isdraft;
		
		$method = new Methods($object->db);
		$method->fetch($line->fk_method);
		
		if (empty($dateSelector)) $dateSelector = 0;
		if (empty($forceall)) $forceall = 0;

		// add html5 elements
		$domData  = ' data-element="'.$line->element.'"';
		$domData .= ' data-id="'.$line->id.'"';

		$coldisplay = 0; ?>
		<!-- BEGIN ObjectlineView Limits LIMS -->
		<tr id="row-<?php print $line->id?>" class="drag drop oddeven" <?php print $domData; ?> >
		<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
			<td class="linecolnum center"><?php $coldisplay++; ?><?php print ($i + 1); ?></td>
		<?php } ?>
			<td class="linecoldescription minwidth300imp"><?php $coldisplay++; ?><div id="line_<?php print $line->id; ?>"></div>
		<?php
			$format = $conf->global->MAIN_USE_HOURMIN_IN_DATE_RANGE ? 'dayhour' : 'day';
			
			$text = $method->getNomUrl(1);
			
			if ($method->fk_equipment > 0)
			{	
				//dol_syslog('$method->fk_equipment > 0', LOG_DEBUG);

				print $form->textwithtooltip($text, $description, 3, '', '', $i, 0, (!empty($line->fk_parent_line) ?img_picto('', 'rightarrow') : ''));
			}
			else
			{
				$type = (!empty($line->product_type) ? $line->product_type : $line->fk_product_type);
				if ($type == 1) $text = img_object($langs->trans('Service'), 'service');
				else $text = img_object($langs->trans('Product'), 'product');

				if (!empty($line->label)) {
					$text .= ' <strong>'.$line->label.'</strong>';
					print $form->textwithtooltip($text, dol_htmlentitiesbr($line->description), 3, '', '', $i, 0, (!empty($line->fk_parent_line) ?img_picto('', 'rightarrow') : ''));
				} else {
					if (!empty($line->fk_parent_line)) print img_picto('', 'rightarrow');
					if (preg_match('/^\(DEPOSIT\)/', $line->description)) {
						$newdesc = preg_replace('/^\(DEPOSIT\)/', $langs->trans("Deposit"), $line->description);
						print $text.' '.dol_htmlentitiesbr($newdesc);
					}
					else {
						print $text.' '.dol_htmlentitiesbr($line->description);
					}
				}
			}

			// Add description in form
			if ($method->fk_equipment > 0 ) // && !empty($conf->global->PRODUIT_DESC_IN_FORM))
			{
				//dol_syslog('Add description in form $line->fk_method->fk_equipment='.$method->fk_equipment, LOG_DEBUG);
				//print (!empty($product->description) && $product->description != $product->product_label) ? ' - '.dol_htmlentitiesbr($product->description) : '';
				
				print (!empty($method->label)) ? ' - '.dol_htmlentitiesbr($method->label) : '';
			}
		//}

		// Test-ID
		/*
		if ($object->ref != ''){
			print '<td class="linecoltestid">';
			print $line->getNomUrl();
			print '</td>';
		}
		*/
		// Valid Method?
		if ($method->ref != ''){
			// Method-ISO
			print '<td class="linecolmethod">';
			print $method->standard;
			print '</td>';

			// Accuracy
			print '<td class="linecolaccuracy center">';
			print $method->accuracy;
			print '</td>';

			// Units
			print '<td class="linecolmethodunit left">';
			print $method->getUnit();
			print '</td>';
		}
			// Lower Limit
			print '<td class="linecolstandardlower center">';
			print (is_null($line->minimum) ? '' : $line->minimum);
			print '</td>';
			// Upper Limit
			print '<td class="linecolstandardupper center">';
			print $line->maximum;
			print '</td>';

		
		// Edit - Delete - Move up/down
		if ($object->status == $object::STATUS_DRAFT && $permissiontodelete && $action != 'selectlines') {
			print '<td class="linecoledit center">';
			$coldisplay++;
			if (!empty($disableedit)) {
			} else { ?>
				<a class="editfielda reposition" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->id.'#line_'.$line->id; ?>">
				<?php print img_edit().'</a>';
			}
			print '</td>';

			print '<td class="linecoldelete center">';
			$coldisplay++;
			if (empty($disableremove)) { 
				print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_deleteline&amp;lineid='.$line->id.'">';
				print img_delete();
				print '</a>';
			}
			print '</td>';

			if ($num > 1 && $conf->browser->layout != 'phone' && empty($disablemove)) {
				//dol_syslog('Linecoledit .... $num='.$num.' $i='.$i, LOG_DEBUG);
				print '<td class="linecolmove tdlineupdown center">';
				$coldisplay++;
				if ($i > 0) { ?>
					<a class="lineupdown" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=up&amp;rowid='.$line->id; ?>">
					<?php print img_up('default', 0, 'imgupforline'); ?>
					</a>
				<?php }
				if ($i < $num - 1) { ?>
					<a class="lineupdown" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=down&amp;rowid='.$line->id; ?>">
					<?php print img_down('default', 0, 'imgdownforline'); ?>
					</a>
				<?php }
				print '</td>';
			} else {
				print '<td '.(($conf->browser->layout != 'phone' && empty($disablemove)) ? ' class="linecolmove tdlineupdown center"' : ' class="linecolmove center"').'></td>';
				$coldisplay++;
			}
		} else {
			print '<td colspan="3"></td>';
			$coldisplay = $coldisplay + 3;
		}

		if ($action == 'selectlines') { ?>
			<td class="linecolcheck center"><input type="checkbox" class="linecheckbox" name="line_checkbox[<?php print $i + 1; ?>]" value="<?php print $line->id; ?>" ></td>
		<?php }

		print "</tr>\n";

		//Line extrafield
		if (!empty($extrafields))
		{
			print $line->showOptionals($extrafields, 'view', array('style'=>'class="drag drop oddeven"', 'colspan'=>$coldisplay), '', '', 1);
		}

		print "<!-- END ObjectlineView Limits LIMS -->\n";
	}

	function ObjectlineEditLimits($object, $line, $i)
	{
		global $conf, $langs, $form;
		// Define colspan for the button 'Change'
		$colspan = 2; // Columns: col edit + col delete
		
		// Protection to avoid direct call of template
		if (empty($object) || !is_object($object))
		{
			dol_syslog(__METHOD__.'Object empty or not object', LOG_DEBUG);
			exit;
		}
		
		$method = new Methods($object->db);
		$method->fetch($line->fk_method);
		$equipment = new Equipment ($object->db);
		$equipment->fetch($method->fk_equipment);
		$product = new Product ($object->db);
		$product->fetch($equipment->fk_product);
		
		print "<!-- BEGIN ObjectlineEdit Limits LIMS -->\n";
		$coldisplay = 0;
		?>
		<tr class="oddeven tredited">
		<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
				<td class="linecolnum center"><?php $coldisplay++; ?><?php echo ($i + 1); ?></td>
		<?php }

		?>
		<td>
			<div id="line_<?php echo $line->id; $coldisplay++; ?>"></div>

			<input type="hidden" name="lineid" value="<?php echo $line->id; ?>">
			<input type="hidden" id="fk_parent_line" name="fk_parent_line" value="<?php echo $line->fk_parent_line; ?>">
			<?php

			$text = $product->getNomUrl(1);		// PRODUCT->REF 
			if ($product > 0)
			{
				print $form->textwithtooltip($text, $description, 3, '', '', $i, 0, (!empty($line->fk_parent_line) ?img_picto('', 'rightarrow') : ''));
				print ' - '.$method->label;		// - METHOD->LABEL

				print ' - '.$line->ref;	// LIMITS-ENTRY-REF

			}
			?>
		</td>
		
		<td class="linecolmethod">
			<?php
			$coldisplay++;
			print $method->standard;	// METHOD
			?>
		</td>
		
		<td class="linecolaccuracy center">
			<?php
			$coldisplay++;
			print $method->accuracy; // Accuracy
			?>
		</td>
		
		<td class="linecolmethodunit left">
			<?php
			$coldisplay++;
			print $method->getUnit();	// Units
			?>
		</td>
		
		<td class="linecolstandardlower center">
			<?php $coldisplay++;	// Lower Limit
			
			print '<input type="text" size="5" name="MethodLower" id="MethodLower" class="flat center" value="'.$line->minimum.'">';
			?>
		</td>

		<td class="linecolstandardupper center">
			<?php $coldisplay++;	// Upper Limit
			print '<input type="text" size="5" name="MethodUpper" id="MethodUpper" class="flat center" value="'.$line->maximum.'">';
			?>
		</td>

		<td class="center valignmiddle" colspan="<?php echo $colspan; ?>"><?php $coldisplay += $colspan; ?>
			<input type="submit" class="button buttongen marginbottomonly" id="savelinebutton marginbottomonly" name="save" value="<?php echo $langs->trans("Save"); ?>"><br>
			<input type="submit" class="button buttongen marginbottomonly" id="cancellinebutton" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>">
		</td>
		<?php
		if (is_object($objectline)) {
			print $objectline->showOptionals($extrafields, 'edit', array('colspan'=>$coldisplay), '', '', 1);
		}
		?>
		</tr>
		<!-- END ObjectlineEDIT Limits LIMS -->
		<?php
	}
	
	function pdf_writelinedesc($parameters, &$object, &$action, $hookmanager)
	{
		//$parameters = array('pdf'=>$pdf, 'i'=>$i, 'outputlangs'=>$outputlangs, 'w'=>$w, 'h'=>$h, 'posx'=>$posx, 'posy'=>$posy, 'hideref'=>$hideref, 'hidedesc'=>$hidedesc, 'issupplierline'=>$issupplierline, 'special_code'=>$special_code);
		
		$w = $parameters['w'];
		$h = $parameters['h'];
		$posx = $parameters['posx']; 
		$posy = $parameters['posy'];
		$pdf = $parameters['pdf'];
		$outputlangs = $parameters['outputlangs'];
		$i = $parameters['i'];
		
		$method = new Methods($object->db);
		$method->fetch($object->lines[$i]->fk_method);
		$equipment = new Equipment ($object->db);
		$equipment->fetch($method->fk_equipment);
		$product = new Product ($object->db);
		$product->fetch($equipment->fk_product);
				
		$labelproductservice = $method->label;
		
		// Print Number starting with 1
		//$pdf->writeHTMLCell(7, $h, $posx, $posy, $i+1, 0, 1, false, true, 'J', true);
		$pdf->MultiCell(7, $h, $i+1, 0, 'L', false, 0, $posx, $posy); // $reseth, 0, true, $autopadding, 0, 'T', false);

		// Print Description
		//$pdf->writeHTMLCell($w-7, $h, $posx+7, $posy, $outputlangs->convToOutputCharset($labelproductservice), 0, 1, false, true, 'J', true);
		$pdf->MultiCell($w-7, $h, $outputlangs->convToOutputCharset($labelproductservice), 0, 'L', false, 0, $posx+7, $posy); // $reseth, 0, true, $autopadding, 0, 'T', false);
		
		$result = $labelproductservice;
	
		//return $result;
	}
	
	function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;
		
		dol_syslog(__METHOD__.' hook on addMoreActionsButtons, paramters='.var_export($paramters, true).' action='.$action, LOG_DEBUG);
		if ($object->element == 'facture'){
		//if (in_array('', explode(':', $parameters['invoicecard']))) // parameters are empty
			if (!empty($conf->lims->enabled) && $user->rights->lims->samples->write && $object->statut == (Facture::STATUS_VALIDATED || Facture::STATUS_CLOSED))
			{
				print '<a class="butAction" href="'.dol_buildpath('/lims/samples_card.php?action=create',1).'&amp;origin='.$object->element.'&amp;originid='.$object->id.'&amp;socid='.$object->socid.'">'.$langs->trans("createsample").'</a>';
				return 0;
			}
		}
		else
			return 0;
	}
}
