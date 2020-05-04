<?php

require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/samples.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/methods.class.php';
require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/results.class.php';



class ActionsLims Extends CommonObject
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
		  // do something only for the context 'somecontext'

			$this->ObjectlineView($object, $parameters['line'], $parameters['num']);
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
		print '<td class="linecolresultabnorm left" style="width: 80px">'.$langs->trans('ResultAbnormality').'</td>';

		// ??ToDo: Title with colspan=2 for Limits Lower and Upper

		// Limit Standard lower (UNBS or other)
		print '<td class="linecolstandardlow center" style="width: 80px">'.$langs->trans('StandardLowerLimit').'</td>';

		// Limit Standard upper (UNBS or other)
		print '<td class="linecolstandardupper center" style="width: 80px">'.$langs->trans('StandardUpperLimit').'</td>';

		// Result
		print '<td class="linecolresult center" style="width: 80px">'.$langs->trans('Result').'</td>';

		// Method Unit
		print '<td class="linecolmethodunit center" style="width: 160px">'.$langs->trans('MethodUnit').'</td>';

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
	
	function ObjectlineView ($object, $line, $num)
	{
		global $forceall, $senderissupplier, $inputalsopricewithtax, $outputalsopricetotalwithtax;
		global $langs;
		global $conf;
		global $form;
		
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
		<!-- BEGIN ObjectlineCreate LIMS -->
		<tr id="row-<?php print $line->id?>" class="drag drop oddeven" <?php print $domData; ?> >
		<?php if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) { ?>
			<td class="linecolnum center"><?php $coldisplay++; ?><?php print ($i + 1); ?></td>
		<?php } ?>
			<td class="linecoldescription minwidth300imp"><?php $coldisplay++; ?><div id="line_<?php print $line->id; ?>"></div>
		<?php
		/*
		if (($line->info_bits & 2) == 2) {
			print '<a href="'.DOL_URL_ROOT.'/comm/remx.php?id='.$this->socid.'">';
			$txt = '';
			print img_object($langs->trans("ShowReduc"), 'reduc').' ';
			if ($line->description == '(DEPOSIT)') $txt = $langs->trans("Deposit");
			elseif ($line->description == '(EXCESS RECEIVED)') $txt = $langs->trans("ExcessReceived");
			elseif ($line->description == '(EXCESS PAID)') $txt = $langs->trans("ExcessPaid");
			
			//else $txt=$langs->trans("Discount");
			print $txt;
			print '</a>';
			if ($line->description)
			{
				print ($txt ? ' - ' : '').dol_htmlentitiesbr($line->description);
			}
		}
		else
		{
		*/
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
			print '<td class="linecolresultabnorm">';
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
			print '<td class="linecolresult center">';
			print $line->result;
			print '</td>';
		}

		if ($method->ref != ''){
			// Units
			print '<td class="linecolmethodunit center">';
			print $method->unit;
			print '</td>';
		}
		/*
		if ($user->rights->fournisseur->lire && $line->fk_fournprice > 0)
		{
			require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
			$productfourn = new ProductFournisseur($this->db);
			$productfourn->fetch_product_fournisseur_price($line->fk_fournprice);
			print '<div class="clearboth"></div>';
			print '<span class="opacitymedium">'.$langs->trans('Supplier').' : </span>'.$productfourn->getSocNomUrl(1, 'supplier').' - <span class="opacitymedium">'.$langs->trans('Ref').' : </span>';
			// Supplier ref
			if ($user->rights->produit->creer || $user->rights->service->creer) // change required right here
			{
				print $productfourn->getNomUrl();
			}
			else
			{
				print $productfourn->ref_supplier;
			}
		}
		*/
		/*
		if (!empty($conf->accounting->enabled) && $line->fk_accounting_account > 0)
		{
			$accountingaccount = new AccountingAccount($this->db);
			$accountingaccount->fetch($line->fk_accounting_account);
			print '<div class="clearboth"></div><br><span class="opacitymedium">'.$langs->trans('AccountingAffectation').' : </span>'.$accountingaccount->getNomUrl(0, 1, 1);
		}

		print '</td>';
		*/
		/*
		if ($object->element == 'supplier_proposal' || $object->element == 'order_supplier' || $object->element == 'invoice_supplier')	// We must have same test in printObjectLines
		{
			print '<td class="linecolrefsupplier">';
			print ($line->ref_fourn ? $line->ref_fourn : $line->ref_supplier);
			print '</td>';
		}
		// VAT Rate
		print '<td class="linecolvat nowrap right">';
		$coldisplay++;
		$positiverates = '';
		if (price2num($line->tva_tx))          $positiverates .= ($positiverates ? '/' : '').price2num($line->tva_tx);
		if (price2num($line->total_localtax1)) $positiverates .= ($positiverates ? '/' : '').price2num($line->localtax1_tx);
		if (price2num($line->total_localtax2)) $positiverates .= ($positiverates ? '/' : '').price2num($line->localtax2_tx);
		if (empty($positiverates)) $positiverates = '0';
		print vatrate($positiverates.($line->vat_src_code ? ' ('.$line->vat_src_code.')' : ''), '%', $line->info_bits);
		//print vatrate($line->tva_tx.($line->vat_src_code?(' ('.$line->vat_src_code.')'):''), '%', $line->info_bits);
		?></td>

			<td class="linecoluht nowrap right"><?php $coldisplay++; ?><?php print price($line->subprice); ?></td>

		<?php if (!empty($conf->multicurrency->enabled) && $this->multicurrency_code != $conf->currency) { ?>
			<td class="linecoluht_currency nowrap right"><?php $coldisplay++; ?><?php print price($line->multicurrency_subprice); ?></td>
		<?php }

		if ($inputalsopricewithtax) { ?>
			<td class="linecoluttc nowrap right"><?php $coldisplay++; ?><?php print (isset($line->pu_ttc) ?price($line->pu_ttc) : price($line->subprice)); ?></td>
		<?php } ?>

			<td class="linecolqty nowrap right"><?php $coldisplay++; ?>
		<?php
		if ((($line->info_bits & 2) != 2) && $line->special_code != 3) {
			// I comment this because it shows info even when not required
			// for example always visible on invoice but must be visible only if stock module on and stock decrease option is on invoice validation and status is not validated
			// must also not be output for most entities (proposal, intervention, ...)
			//if($line->qty > $line->stock) print img_picto($langs->trans("StockTooLow"),"warning", 'style="vertical-align: bottom;"')." ";
			print price($line->qty, 0, '', 0, 0); // Yes, it is a quantity, not a price, but we just want the formating role of function price
		} else print '&nbsp;';
		print '</td>';

		if ($conf->global->PRODUCT_USE_UNITS)
		{
			print '<td class="linecoluseunit nowrap left">';
			$label = $line->getLabelOfUnit('short');
			if ($label !== '') {
				print $langs->trans($label);
			}
			print '</td>';
		}
		if (!empty($line->remise_percent) && $line->special_code != 3) {
			print '<td class="linecoldiscount right">';
			$coldisplay++;
			include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
			print dol_print_reduction($line->remise_percent, $langs);
			print '</td>';
		} else {
			print '<td class="linecoldiscount">&nbsp;</td>';
			$coldisplay++;
		}

		// Fields for situation invoices
		if ($this->situation_cycle_ref)
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';
			$coldisplay++;
			print '<td class="linecolcycleref nowrap right">'.$line->situation_percent.'%</td>';
			$coldisplay++;
			$locataxes_array = getLocalTaxesFromRate($line->tva.($line->vat_src_code ? ' ('.$line->vat_src_code.')' : ''), 0, ($senderissupplier ? $mysoc : $object->thirdparty), ($senderissupplier ? $object->thirdparty : $mysoc));
			$tmp = calcul_price_total($line->qty, $line->pu, $line->remise_percent, $line->txtva, -1, -1, 0, 'HT', $line->info_bits, $line->type, ($senderissupplier ? $object->thirdparty : $mysoc), $locataxes_array, 100, $object->multicurrency_tx, $line->multicurrency_subprice);
			print '<td align="right" class="linecolcycleref2 nowrap">'.price($tmp[0]).'</td>';
		}

		if ($usemargins && !empty($conf->margin->enabled) && empty($user->socid))
		{
			if (!empty($user->rights->margins->creer)) { ?>
				<td class="linecolmargin1 nowrap margininfos right"><?php $coldisplay++; ?><?php print price($line->pa_ht); ?></td>
			<?php }
			if (!empty($conf->global->DISPLAY_MARGIN_RATES) && $user->rights->margins->liretous) { ?>
				<td class="linecolmargin2 nowrap margininfos right"><?php $coldisplay++; ?><?php print (($line->pa_ht == 0) ? 'n/a' : price(price2num($line->marge_tx, 'MT')).'%'); ?></td>
			<?php }
			if (!empty($conf->global->DISPLAY_MARK_RATES) && $user->rights->margins->liretous) {?>
			  <td class="linecolmargin2 nowrap margininfos right"><?php $coldisplay++; ?><?php print price(price2num($line->marque_tx, 'MT')).'%'; ?></td>
			<?php }
		}
		if ($line->special_code == 3) { ?>
			<td class="linecoloption nowrap right"><?php $coldisplay++; ?><?php print $langs->trans('Option'); ?></td>
		<?php } else {
			print '<td class="linecolht nowrap right">';
			$coldisplay++;
			if (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				print '<span class="classfortooltip" title="';
				print $langs->transcountry("TotalHT", $mysoc->country_code).'='.price($line->total_ht);
				print '<br>'.$langs->transcountry("TotalVAT", ($senderissupplier ? $object->thirdparty->country_code : $mysoc->country_code)).'='.price($line->total_tva);
				if (price2num($line->total_localtax1)) print '<br>'.$langs->transcountry("TotalLT1", ($senderissupplier ? $object->thirdparty->country_code : $mysoc->country_code)).'='.price($line->total_localtax1);
				if (price2num($line->total_localtax2)) print '<br>'.$langs->transcountry("TotalLT2", ($senderissupplier ? $object->thirdparty->country_code : $mysoc->country_code)).'='.price($line->total_localtax2);
				print '<br>'.$langs->transcountry("TotalTTC", $mysoc->country_code).'='.price($line->total_ttc);
				print '">';
			}
			print price($line->total_ht);
			if (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				print '</span>';
			}
			print '</td>';
			if (!empty($conf->multicurrency->enabled) && $this->multicurrency_code != $conf->currency) {
				print '<td class="linecolutotalht_currency nowrap right">'.price($line->multicurrency_total_ht).'</td>';
				$coldisplay++;
			}
		}
		if ($outputalsopricetotalwithtax) {
			print '<td class="linecolht nowrap right">'.price($line->total_ttc).'</td>';
			$coldisplay++;
		}
		*/
		if ($object->statut == 0 && ($object_rights->creer) && $action != 'selectlines') {
			print '<td class="linecoledit center">';
			$coldisplay++;
			if (($line->info_bits & 2) == 2 || !empty($disableedit)) {
			} else { ?>
				<a class="editfielda reposition" href="<?php print $_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=editline&amp;lineid='.$line->id.'#line_'.$line->id; ?>">
				<?php print img_edit().'</a>';
			}
			print '</td>';

			print '<td class="linecoldelete center">';
			$coldisplay++;
			if (($line->fk_prev_id == null) && empty($disableremove)) { //La suppression n'est autorisée que si il n'y a pas de ligne dans une précédente situation
				print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=ask_deleteline&amp;lineid='.$line->id.'">';
				print img_delete();
				print '</a>';
			}
			print '</td>';

			if ($num > 1 && $conf->browser->layout != 'phone' && ($object->situation_counter == 1 || !$object->situation_cycle_ref) && empty($disablemove)) {
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
		$colspan = 3; // Columns: total ht + col edit + col delete
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
		print "<!-- BEGIN PHP TEMPLATE objectline_create.tpl.php LIMS -->\n";
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
			// Adds a line numbering column
			if (!empty($conf->global->MAIN_VIEW_LINE_NUMBER)) {
				$coldisplay++;
				echo '<td class="nobottom linecolnum center"></td>';
			}
			?>
			<!-- Predefined product/service => LIMS only allows to select of products listed in methods -->
			
			<td class="nobottom linecoldescription minwidth300imp"><?php $coldisplay++;?>
				<span class="prod_entry_mode_predef">
				<label for="prod_entry_mode_predef">
				<?php 
				echo $langs->trans('AddLineTitle');
				
				echo '</label>';
				echo ' ';
				$filtertype = '';  // ''=nofilter, 0=product, 1=service
				$statustoshow = 1; //1=Return all products, 0=Products not on sell, 1=Products on sell
				//$form->select_produits(GETPOST('idprod'), 'idprod', $filtertype, $conf->product->limit_size, $buyer->price_level, $statustoshow, 2, '', 0, array(), $buyer->id, '1', 0, 'maxwidth500', 1, '', GETPOST('combinations', 'array'));
				$sql = 'SELECT p.rowid, p.ref, p.label, p.description,';
				$sql .= ' m.rowid as mrowid, m.ref as mref, m.label as mlabel, m.fk_product';
				$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_methods as m';
				$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON m.fk_product=p.rowid';
				
				$key='Product'; 
				print $object->DropDownProductMethod($sql, $key, $object, '');
				?>
				</span>
			</td>
			
			 <!-- Test-ID -->
			<td class="nobottom linecoltestid left"><?php $coldisplay++; ?>
				
			</td>

			<!-- Method -->
			<td class="nobottom linecolmethod left">
			</td>

			<!-- Accuracy -->
			<td class="nobottom linecolaccuracy center"><?php $coldisplay++; ?>
				<?php
				print $method->standard;
				dol_syslog(__METHOD__.'HIER $method='.var_export($method, true), LOG_DEBUG);
				?>
			</td>

			<!-- Abnormalities -->
			<td class="nobottom linecolabnormalities left"><?php $coldisplay++; ?>
				<?php 
				echo $form->selectyesno('abnormalities', $line->abnormalities, 1);
				?>
			</td>

			 <!-- Lower limit -->
			<td class="nobottom linecollowerlimit right"><?php $coldisplay++; ?>
				
			</td>

			 <!-- Upper limit -->
			<td class="nobottom linecolupperlimit right"><?php $coldisplay++; ?>
				
			</td>

			 <!-- Result -->
			<td class="nobottom linecolresult right"><?php $coldisplay++; ?>
				<input type="text" size="5" name="result" id="result" class="flat right" value="<?php echo (isset($_POST["result"]) ?GETPOST("result", 'alpha', 2) : 0); ?>">
			</td>
			
			 <!-- Unit -->
			<td class="nobottom linecolunit left"><?php $coldisplay++; ?>
				
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
			print $date_start=$form->selectDate('', 'Start',1, 1, 0, "Start", 1, 1,0,'','','','',1);
			print '<br>';
			print $langs->trans('to');
			print '&emsp;'.$date_end=$form->selectDate('', 'End',1, 1, 0, "End", 1, 1,0,'','','','',1);
			print '</td>';

			// User who did the test 
			print '<td colspan=2>';
			print $langs->trans('TestingTechnician').'<br>';
			//public function select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0')
			print $fk_user=$form->select_users('', 'userid');
			//print &user=select_users($selected = '', $htmlname = 'userid', $show_empty = 0, $exclude = null, $disabled = 0, $include = '', $enableonly = '', $force_entity = '0');;
			print '</td>';
			print '</tr>'."\n";

			
			print "<!-- END ObjectlineCreate LIMS-->\n";
	}
}