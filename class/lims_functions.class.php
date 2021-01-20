<?php
	//copied from Form::select_produits_list
	// Create a html dropdown menu with values in the form of:
	//<option value='obj->rowid' name='nameID'>'obj->key'</option>
	
class lims_functions
{
	public function DropDownProduct($sql, $nameID, $obj, $key='ref', $selected='', $morecss='', $addlabel='')
	{
		global $langs, $conf, $user, $db;

		$out = '';
		$outarray = array();
		$idprod = -1;		//Product used to get list of methods

		dol_syslog(__METHOD__, LOG_DEBUG);
		$result = $obj->db->query($sql);
		
		if ($result)
		{
			$num = $obj->db->num_rows($result);
			
			$out .= '<select class="flat'.($morecss ? ' '.$morecss : '').'" name="'.$nameID.'" id="'.$nameID.'">';
			$key_string = 'objp->'.$key;
			$$key = $key_string;
			$i = 0;
			while ($num && $i < $num)
			{
				$opt = '';
				$objp = $obj->db->fetch_object($result);
				$opt = '<option value="'.$objp->rowid.'"';
				$idprod = ($i == 0 ? $objp->rowid : $idprod); // first element selected
				if ($objp->rowid == $selected){
					$opt .= ' selected';
					$idprod = $objp->rowid;
				}
				$opt .= '>';
				$opt .= $objp->{$key};
				$opt .= empty($addlabel) ? '' : ' - '.$objp->{$addlabel};
				$opt .= "</option>\n";
				
				$out .= $opt;
				$i++;
			}
			if ($num)
				$out .= '</select>';
		}
		
		$obj->db->free($result);
		
		print $out;
		
		return $idprod;
	}
	
	function numberFormatPrecision($number, $precision = 2, $separator = '.')
	{
		$numberParts = explode($separator, $number);
		$response = $numberParts[0];

		if($precision>0){
			$response .= $separator;
			$substr = substr($numberParts[1], 0, $precision);
			$response .= str_pad($substr,$precision,'0');		// Fill trailing 0
		}
		return $response;
	}
}