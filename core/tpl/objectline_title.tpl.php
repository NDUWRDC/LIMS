<?php
/* Copyright (C) 2010-2013	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2010-2011	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2012-2013	Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012       Cédric Salvador     <csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014  Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2013		Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2017		Juanjo Menent		<jmenent@2byte.es>
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
 * $element     (used to test $user->rights->$element->creer)
 * $permtoedit  (used to replace test $user->rights->$element->creer)
 * $inputalsopricewithtax (0 by default, 1 to also show column with unit price including tax)
 * $outputalsopricetotalwithtax
 * $usemargins (0 to disable all margins columns, 1 to show according to margin setup)
 *
 * $type, $text, $description, $line
 */

// Protection to avoid direct call of template
if (empty($object) || !is_object($object))
{
	print "Error, template page can't be called as URL";
	exit;
}

print "<!-- BEGIN PHP TEMPLATE LIMS objectline_title.tpl.php -->\n";

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

// ToDo: Title with colspan=2 for Limits Lower and Upper

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

print "<!-- END PHP TEMPLATE LIMS objectline_title.tpl.php -->\n";
