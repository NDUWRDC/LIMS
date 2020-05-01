<?php
/* Copyright (C) 2010-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2010-2014	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012-2013	Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2014		Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2014       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015-2016	Marcos García		<marcosgdf@gmail.com>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2018		Ferran Marcet		<fmarcet@2byte.es>
 * Copyright (C) 2019		Nicolas ZABOURI		<info@inovea-conseil.com>
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
 *
 * Need to have following variables defined:
 * $object (invoice, order, ...)
 * $conf
 * $langs
 * $dateSelector
 * $forceall (0 by default, 1 for supplier invoices/orders)
 * $senderissupplier (0 by default, 1 or 2 for supplier invoices/orders)
 * $inputalsopricewithtax (0 by default, 1 to also show column with unit price including tax)
 */
// Protection to avoid direct call of template
if (empty($object) || !is_object($object)) {
	print "Error: this template page cannot be called directly as an URL";
	exit;
}
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
if (!empty($conf->multicurrency->enabled) && $this->multicurrency_code != $conf->currency) $colspan++; //Add column for Total (currency) if required
if (in_array($object->element, array('propal', 'commande', 'order', 'facture', 'facturerec', 'invoice', 'supplier_proposal', 'order_supplier', 'invoice_supplier'))) $colspan++; // With this, there is a column move button
//print $object->element;
// Lines for extrafield
$objectline = null;
if (!empty($extrafields))
{
	$objectline = new SamplesLine($this->db); /*
	if ($this->table_element_line == 'commandedet') {
		$objectline = new OrderLine($this->db);
	}
	elseif ($this->table_element_line == 'propaldet') {
		$objectline = new PropaleLigne($this->db);
	}
	elseif ($this->table_element_line == 'supplier_proposaldet') {
		$objectline = new SupplierProposalLine($this->db);
	}
	elseif ($this->table_element_line == 'facturedet') {
		$objectline = new FactureLigne($this->db);
	}
	elseif ($this->table_element_line == 'contratdet') {
		$objectline = new ContratLigne($this->db);
	}
	elseif ($this->table_element_line == 'commande_fournisseurdet') {
		$objectline = new CommandeFournisseurLigne($this->db);
	}
	elseif ($this->table_element_line == 'facture_fourn_det') {
		$objectline = new SupplierInvoiceLine($this->db);
	}
	elseif ($this->table_element_line == 'facturedet_rec') {
		$objectline = new FactureLigneRec($this->db);
	}*/
}
print "<!-- BEGIN PHP TEMPLATE objectline_create.tpl.php LIMS -->\n";
$nolinesbefore = (count($this->lines) == 0 || $forcetoshowtitlelines);
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
		<?php if (!empty($conf->multicurrency->enabled) && $this->multicurrency_code != $conf->currency) { ?>
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
		if ($this->situation_cycle_ref) {
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
		print $this->DropDownProductMethod($sql, $key, $this, '');
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

	
print "<!-- END PHP TEMPLATE objectline_create.tpl.php LIMS-->\n";
