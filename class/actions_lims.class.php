<?php

require_once DOL_DOCUMENT_ROOT.'/custom/lims/class/samples.class.php';


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

		dol_syslog(__METHOD__.' hook on printObjectLineTitle, paramters='.var_export($paramters, true).' action='.$action.' object='.var_export($object,true), LOG_DEBUG);

		if (in_array('', explode(':', $parameters['samplescard'])))
		{
		  // do something only for the context 'somecontext'
			//$tpl = dol_buildpath('lims/core/tpl/objectline_title.tpl.php');
			$this->ObjectlinesTitle();
		include $tpl;
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

		print "<!-- END PHP LIMS ObjectlinesTitle -->\n";
	}
}