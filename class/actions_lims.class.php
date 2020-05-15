<?php

require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/samples.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/methods.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/results.class.php';



class ActionsLims
{ 
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

		//dol_syslog(__METHOD__.' hook on printObjectLineTitle, paramters='.var_export($paramters, true).' action='.$action.' object='.var_export($object,true), LOG_DEBUG);

		if (in_array('', explode(':', $parameters['samplescard'])))
		{
		  // do something only for the context 'somecontext'
			//$tpl = dol_buildpath('lims/core/tpl/objectline_title.tpl.php');
			$this->ObjectlinesTitle();
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

		
		//dol_syslog(__METHOD__.' DB hook on printObjectLine, paramters='.var_export($paramters, true).' action='.$action.' object='.var_export($object,true), LOG_DEBUG);

		if (in_array('', explode(':', $parameters['samplescard'])))
		{
		  // do something only for the context 'samplescard'

			$selected = $parameters['selected'];
			$line = $parameters['line'];
			// Line in view mode
			if ($action != 'editline' || $selected != $line->id) 
			{
				$this->ObjectlineView($object, $parameters['line'], $parameters['num'], $parameters['i']);
			}
			
			// Line in update mode
			if ($action == 'editline' && $selected == $line->id)
			{
				dol_syslog(__METHOD__.' EDIT LINE #='.$line->id, LOG_DEBUG);
				$this->ObjectlineEdit($object, $line, $parameters['i']);
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

		//dol_syslog(__METHOD__.' DB hook on formAddObjectLine, paramters='.var_export($paramters, true).' action='.$action.' object='.var_export($object,true), LOG_DEBUG);

		if (in_array('', explode(':', $parameters['samplescard'])))
		{
		  // do something only for the context 'somecontext'

			$this->ObjectlineCreate($object);
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
	
	function ObjectlinesTitle ()
	{
		global $langs;

		print "<!-- BEGIN PHP LIMS ObjectlinesTitle -->\n";

		// Title line
		print "<thead>\n";

		print '<tr class="liste_titre nodrag nodrop">';

		// Adds a line numbering column
		if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) print '<td class="linecolnum center">&nbsp;</td>';

		// Description => $methods->label
		print '<td class="linecoldescription">'.$langs->trans('Description').'</td>';
		dol_syslog(__METHOD__.' $this->element='.$this->element, LOG_DEBUG);
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
		/*
		// Lower Limit of Method
		print '<td class="linecolmethodlimitlow center" style="width: 80px">'.$langs->trans('MethodLowerLimit').'</td>';

		// Upper Limit of Method
		print '<td class="linecolmethodlimitupper center" style="width: 80px">'.$langs->trans('MethodUpperLimit').'</td>';
		*/
		// Abnormalities
		print '<td class="linecolresultabnorm center" style="width: 80px">'.$langs->trans('ResultAbnormality').'</td>';

		// ??ToDo: Title with colspan=2 for Limits Lower and Upper

		// Limit Standard lower (UNBS or other)
		print '<td class="linecolstandardlow center" style="width: 80px">'.$langs->trans('StandardLowerLimit').'</td>';

		// Limit Standard upper (UNBS or other)
		print '<td class="linecolstandardupper center" style="width: 80px">'.$langs->trans('StandardUpperLimit').'</td>';

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

		print "<!-- END PHP LIMS ObjectlinesTitle -->\n";
	}
	
	function ObjectlineView ($object, $line, $num, $i)
	{
		global $forceall, $senderissupplier, $inputalsopricewithtax, $outputalsopricetotalwithtax;
		global $langs;
		global $conf;
		global $form;
		global $permissiontodelete, $action, $isdraft;
		
		$method = new Methods($object->db);
		$method->fetch($line->fk_method);
		//dol_syslog(__METHOD__.' ABC line='.var_export($line, true), LOG_DEBUG);

		$product = new Product ($object->db);
		$product->fetch($method->fk_product);
		//dol_syslog('Fetch $line->fk_method->fk_product='.$method->fk_product, LOG_DEBUG);
		
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
		<!-- BEGIN ObjectlineView LIMS -->
		<tr id="row-<?php print $line->id?>" class="drag drop oddeven" <?php print $domData; ?> >
		<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
			<td class="linecolnum center"><?php $coldisplay++; ?><?php print ($i + 1); ?></td>
		<?php } ?>
			<td class="linecoldescription minwidth300imp"><?php $coldisplay++; ?><div id="line_<?php print $line->id; ?>"></div>
		<?php
			$format = $conf->global->MAIN_USE_HOURMIN_IN_DATE_RANGE ? 'dayhour' : 'day';
			
			$text = $product->getNomUrl(1);
			
			if ($method->fk_product > 0)
			{	
				dol_syslog('$method->fk_product > 0', LOG_DEBUG);

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
			if ($method->fk_product > 0 ) // && !empty($conf->global->PRODUIT_DESC_IN_FORM))
			{
				dol_syslog('Add description in form $line->fk_method->fk_product='.$method->fk_product, LOG_DEBUG);
				//print (!empty($product->description) && $product->description != $product->product_label) ? ' - '.dol_htmlentitiesbr($product->description) : '';
				
				print (!empty($method->label)) ? ' - '.dol_htmlentitiesbr($method->label) : '';
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
		}

		if ($object->ref != ''){
			// Abnormalities
			print '<td class="linecolresultabnorm center">';
			print $line->abnormalities;
			print '</td>';
		}

			// Lower Limit
			print '<td class="linecolstandardlower center">';
			print "";
			print '</td>';
			// Upper Limit
			print '<td class="linecolstandardupper center">';
			print "";
			print '</td>';

		if ($object->ref != ''){
			// Result
			print '<td class="linecolresult right">';
			print $line->result;
			print '</td>';
		}

		if ($method->ref != ''){
			// Units
			print '<td class="linecolmethodunit left">';
			print $method->unit;
			print '</td>';
		}
		
		// Edit - Delete - Move up/down
		if ($isdraft && $permissiontodelete && $action != 'selectlines') {
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
				dol_syslog('Linecoledit .... $num='.$num.' $i='.$i, LOG_DEBUG);
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

		print "<!-- END ObjectlineView LIMS -->\n";
	}

	function ObjectlineCreate($object)
	{
		global $conf, $langs, $form;
		
		$usemargins = 0;
		if (!empty($conf->margin->enabled) && !empty($object->element) && in_array($object->element, array('facture', 'facturerec', 'propal', 'commande')))
		{
			$usemargins = 1;
		}
		if (!isset($dateSelector)) global $dateSelector; // Take global var only if not already defined into function calling (for example formAddObjectLine)
		global $forceall, $forcetoshowtitlelines, $senderissupplier, $inputalsopricewithtax;
		if (!isset($dateSelector)) $dateSelector = 1; // For backward compatibility
		elseif (empty($dateSelector)) $dateSelector = 0;
		if (empty($forceall)) $forceall = 0;
		if (empty($senderissupplier)) $senderissupplier = 0;
		if (empty($inputalsopricewithtax)) $inputalsopricewithtax = 0;
		// Define colspan for the button 'Add'
		$colspan = 2; // Columns: total ht + col edit + col delete
		if (!empty($conf->multicurrency->enabled) && $object->multicurrency_code != $conf->currency) $colspan++; //Add column for Total (currency) if required
		if (in_array($object->element, array('propal', 'commande', 'order', 'facture', 'facturerec', 'invoice', 'supplier_proposal', 'order_supplier', 'invoice_supplier'))) $colspan++; // With this, there is a column move button
		//print $object->element;
		// Lines for extrafield
		$objectline = null;
		if (!empty($extrafields))
		{
			$objectline = new SamplesLine($object->db); /*
			if ($object->table_element_line == 'commandedet') {
				$objectline = new OrderLine($object->db);
			}
			elseif ($object->table_element_line == 'propaldet') {
				$objectline = new PropaleLigne($object->db);
			}
			elseif ($object->table_element_line == 'supplier_proposaldet') {
				$objectline = new SupplierProposalLine($object->db);
			}
			elseif ($object->table_element_line == 'facturedet') {
				$objectline = new FactureLigne($object->db);
			}
			elseif ($object->table_element_line == 'contratdet') {
				$objectline = new ContratLigne($object->db);
			}
			elseif ($object->table_element_line == 'commande_fournisseurdet') {
				$objectline = new CommandeFournisseurLigne($object->db);
			}
			elseif ($object->table_element_line == 'facture_fourn_det') {
				$objectline = new SupplierInvoiceLine($object->db);
			}
			elseif ($object->table_element_line == 'facturedet_rec') {
				$objectline = new FactureLigneRec($object->db);
			}*/
		}
		print "<!-- BEGIN ObjectlineCreate LIMS -->\n";
		$nolinesbefore = (count($object->lines) == 0 || $forcetoshowtitlelines);
		if ($nolinesbefore) {
			?>
			<tr class="liste_titre<?php echo (($nolinesbefore || $object->element == 'contrat') ? '' : ' liste_titre_add_') ?> nodrag nodrop">
				<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
					<td class="linecolnum center"></td>
				<?php } ?>
				<td class="linecoldescription minwidth500imp">
					<div id="add"></div><span class="hideonsmartphone"><?php echo $langs->trans('AddNewLine'); ?></span><?php // echo $langs->trans("FreeZone"); ?>
				</td>
				/*
				<?php
				if ($object->element == 'supplier_proposal' || $object->element == 'order_supplier' || $object->element == 'invoice_supplier')	// We must have same test in printObjectLines
				{
					?>
					<td class="linecolrefsupplier"><span id="title_fourn_ref"><?php echo $langs->trans('SupplierRef'); ?></span></td>
					<?php
				}
				?>
				<td class="linecolvat right"><span id="title_vat"><?php echo $langs->trans('VAT'); ?></span></td>
				<td class="linecoluht right"><span id="title_up_ht"><?php echo $langs->trans('PriceUHT'); ?></span></td>
				<?php if (!empty($conf->multicurrency->enabled) && $object->multicurrency_code != $conf->currency) { ?>
					<td class="linecoluht_currency right"><span id="title_up_ht_currency"><?php echo $langs->trans('PriceUHTCurrency'); ?></span></td>
				<?php } ?>
				<?php if (!empty($inputalsopricewithtax)) { ?>
					<td class="linecoluttc right"><span id="title_up_ttc"><?php echo $langs->trans('PriceUTTC'); ?></span></td>
				<?php } ?>
				<td class="linecolqty right"><?php echo $langs->trans('Qty'); ?></td>
				<?php
				if ($conf->global->PRODUCT_USE_UNITS)
				{
					print '<td class="linecoluseunit left">';
					print '<span id="title_units">';
					print $langs->trans('Unit');
					print '</span></td>';
				}
				?>
				<td class="linecoldiscount right"><?php echo $langs->trans('ReductionShort'); ?></td>
				<?php
				// Fields for situation invoice
				if ($object->situation_cycle_ref) {
					print '<td class="linecolcycleref right">'.$langs->trans('Progress').'</td>';
					print '<td class="linecolcycleref2 right"></td>';
				}
				if (!empty($usemargins))
				{
					if (empty($user->rights->margins->creer)) {
						$colspan++;
					}
					else {
						print '<td class="margininfos linecolmargin1 right">';
						if ($conf->global->MARGIN_TYPE == "1")
							echo $langs->trans('BuyingPrice');
						else
							echo $langs->trans('CostPrice');
						echo '</td>';
						if (!empty($conf->global->DISPLAY_MARGIN_RATES)) echo '<td class="margininfos linecolmargin2 right"><span class="np_marginRate">'.$langs->trans('MarginRate').'</span></td>';
						if (!empty($conf->global->DISPLAY_MARK_RATES)) echo '<td class="margininfos linecolmargin2 right"><span class="np_markRate">'.$langs->trans('MarkRate').'</span></td>';
					}
				}
				?>
				*/
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
				echo $langs->trans('AddLineTitle');
				
				echo '</label>';
				echo '<br>';
				$filtertype = '';  // ''=nofilter, 0=product, 1=service
				$statustoshow = 1; //1=Return all products, 0=Products not on sell, 1=Products on sell
				
				// select
				//$form->select_produits(GETPOST('idprod'), 'idprod', $filtertype, $conf->product->limit_size, 0, 1, 2, '', 0, array(), $buyer->id, '1', 0, 'maxwidth500', 1, '', GETPOST('combinations', 'array'));
				
				$sql = 'SELECT p.rowid, p.ref, p.label, p.description,';
				$sql .= ' m.rowid as mrowid, m.ref as mref, m.label as mlabel, m.fk_product';
				$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as m';
				$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON m.fk_product=p.rowid';
				$sql .= ' GROUP BY p.rowid';  // don't show duplicates
				
				$nameID='ProdID'; 
				// ToDo ??: GETPOST('idprod')
				$idprod = $object->DropDownProduct($sql, $nameID, $object, 'ref', '', '');
				
				GETPOST('idprod');
				
				if ($idprod > 0){
					
					$sql = 'SELECT p.rowid, p.ref, p.label, p.description, p.fk_product';
					$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as p';
					$sql .= ' WHERE fk_product='.$idprod;
					
					$nameID='MethodID';
					
					$methodID = $object->DropDownProduct($sql, $nameID, $object, 'label', '', '');
				}
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

			<!-- Abnormalities -->
			<td class="nobottom linecolabnormalities center"><?php $coldisplay++; ?>
				<?php 
				echo $form->selectyesno('abnormalities', $line->abnormalities, 1);
				?>
			</td>

			 <!-- Lower limit -->
			<td class="nobottom linecollowerlimit right"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodLower" id="MethodLower" class="flat center" value="<?php echo (isset($_POST["MethodLower"]) ?GETPOST("MethodLower", 'alpha', 2) : ''); ?>" disabled>
			</td>

			 <!-- Upper limit -->
			<td class="nobottom linecolupperlimit right"><?php $coldisplay++; ?>
				<input type="text" size="5" name="MethodUpper" id="MethodUpper" class="flat center" value="<?php echo (isset($_POST["MethodUpper"]) ?GETPOST("MethodUpper", 'alpha', 2) : ''); ?>" disabled>
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

			// Test Start and End
			// Form::selectDate($set_time = '', $prefix = 're', $h = 0, $m = 0, $empty = 0, $form_name = "", $d = 1, $addnowlink = 0, $disabled = 0, $fullday = '', $addplusone = '', $adddateof = '', $openinghours = '', $stepminutes = 1, $labeladddateof = '')
			print '<tr id="trlinefordates" class="oddeven">'."\n";
			print '<td colspan=1>';
			print $langs->trans('TestDuration').'<br>';
			print ' '.$langs->trans('From').' ';
			print $date_start=$form->selectDate('', 'date_start',1, 1, 0, "Start", 1, 1,0,'','','','',1);
			print '<br>';
			print $langs->trans('to');
			print '&emsp;'.$date_end=$form->selectDate('', 'date_end',1, 1, 0, "End", 1, 1,0,'','','','',1);
			print '</td>';

			// User who did the test 
			print '<td colspan=2>';
			print $langs->trans('TestingTechnician').'<br>';
			//public function select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0')
			print $fk_user=$form->select_users('', 'userid');
			//print &user=select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0');;
			print '</td>';
			print '</tr>'."\n";

			print "<script>\n";
			?>
			// When changing MethodID, columns are set: MethodStandard, Accuracy, Lower, Upper, Unit
			$("#MethodID").change(function()
			{
				console.log("MethodID.change: value="+$(this).val());
				
				//$.post('<?php echo dol_buildpath("lims"); ?> /methods_ajax.php?action=fetch',
				$.post('<?php echo DOL_URL_ROOT; ?>/custom/lims/methods_ajax.php?action=fetch',
				{ 'idmethod': $(this).val() },
					function(data) {
							jQuery("#MethodStandard").val(data.label);
							jQuery("#MethodAccuracy").val(data.accuracy);
							jQuery("#MethodLower").val(data.lower);
							jQuery("#MethodUpper").val(data.upper);
							jQuery("#MethodUnit").val(data.unit);
					},
					'json'
				);
			});

			// When changing ProdID, options are populated with available methods for this product
			$("#ProdID").change(function()
			{
				console.log("ProdID.change: value="+$(this).val());

				// ?Path concat needs repair (no custom?!)
				//$.post('<?php echo dol_buildpath("lims"); ?> /methods_ajax.php?action=fetch',
				
				$.post('<?php echo DOL_URL_ROOT; ?>/custom/lims/methods_ajax.php?action=fetch',
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
			
			<?php
			print '</script>';
			
			print "<!-- END ObjectlineCreate LIMS-->\n";
	}
	
	// Copied from objectline_edit.tpl.php
	function ObjectlineEdit($object, $line, $i)
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
		//dol_syslog(__METHOD__.' ABC line='.var_export($line, true), LOG_DEBUG);

		$product = new Product ($object->db);
		$product->fetch($method->fk_product);
		//dol_syslog('Fetch $line->fk_method->fk_product='.$method->fk_product, LOG_DEBUG);
		
		print "<!-- BEGIN ObjectlineEdit LIMS -->\n";
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
		
		<td class="linecolresultabnorm center">
			<?php
			$coldisplay++;
			echo $form->selectyesno('abnormalities', $line->abnormalities, 1); // Abnormalities
			?>
		</td>

		<td class="linecolstandardlower center">
			<?php
			$coldisplay++;
			print "";		// Lower Limit
			?>
		</td>
			
		<td class="linecolstandardupper center">
			<?php
			$coldisplay++;
			print "";		// Upper Limit
			?>
		</td>

		<td class="linecolresult right">
			<?php
			$coldisplay++;
			print '<input type="text" size="5" name="result" id="result" class="flat right" value="';
			print $line->result;  // Result
			?>
			">
		</td>

		<td class="linecolmethodunit left">
			<?php
			$coldisplay++;
			print $method->unit;	// Units
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
		<!-- END ObjectlineEDIT LIMS -->
		<?php
	}
}