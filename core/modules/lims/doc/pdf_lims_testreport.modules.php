<?php
/* Copyright (C) 2004-2014	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2008		Raphael Bertrand		<raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2014	Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2012		Christophe Battarel	<christophe.battarel@altairis.fr>
 * Copyright (C) 2012		Cédric Salvador		<csalvador@gpcsolutions.fr>
 * Copyright (C) 2012-2014	Raphaël Doursenaud	<rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2015		Marcos García		<marcosgdf@gmail.com>
 * Copyright (C) 2017-2018	Ferran Marcet		<fmarcet@2byte.es>
 * Copyright (C) 2018       Frédéric France     <frederic.france@netlogic.fr>
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
 * or see https://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/facture/doc/pdf_crabe.modules.php
 *	\ingroup    facture
 *	\brief      File of class to generate customers invoices from crabe model
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/facture/modules_facture.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
dol_include_once('/lims/class/methods.class.php', 'Methods');
dol_include_once('/lims/class/lims_functions.class.php', 'lims_functions');


/**
 *	Class to generate the customer invoice PDF with template Crabe
 */
class pdf_lims_testreport extends CommonDocGenerator
{
     /**
     * @var DoliDb Database handler
     */
    public $db;

	/**
     * @var string model name
     */
    public $name;

	/**
     * @var string model description (short text)
     */
    public $description;

    /**
     * @var int 	Save the name of generated file as the main doc when generating a doc with this template
     */
    public $update_main_doc_field;

	/**
     * @var string document type
     */
    public $type;

	/**
     * @var array Minimum version of PHP required by module.
     * e.g.: PHP ≥ 5.5 = array(5, 5)
     */
	public $phpmin = array(5, 5);

	/**
     * Dolibarr version of the loaded document
     * @var string
     */
	public $version = 'dolibarr';

	/**
     * @var int page_width
     */
    public $page_width;

	/**
     * @var int page_height
     */
    public $page_height;

	/**
     * @var array format
     */
    public $format;

	/**
     * @var int margin_left
     */
	public $margin_left;

	/**
     * @var int marge_droite
     */
	public $margin_right;

	/**
     * @var int margin_top
     */
	public $margin_top;

	/**
     * @var int margin_bottom
     */
	public $margin_bottom;

	/**
	 * Issuer
	 * @var Societe Object that emits
	 */
	public $issuer;

	/**
	 * @var bool Situation invoice type
	 */
	public $situationinvoice;

	/**
	 * @var float X position for the situation progress column
	 */
	public $posxprogress;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;

		// Translations
		$langs->loadLangs(array("main", "bills", "lims@lims"));

		$this->db = $db;
		$this->name = "lims_testreport";
		$this->description = $langs->trans('PDFlims_testreportDescription');
		$this->update_main_doc_field = 1; // Save the name of generated file as the main doc when generating a doc with this template

		// Dimension page
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_width = $formatarray['width'];
		$this->page_height = $formatarray['height'];
		$this->format = array($this->page_width, $this->page_height);
		$this->margin_left = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
		$this->margin_right = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
		$this->margin_top = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
		$this->margin_bottom = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;
		$this->page_textwidth = $this->page_width - $this->margin_right - $this->margin_right; // =196 as opposed to 190 used in code for boxes spanning the whole width.
		
		$this->option_logo = 1; // Display logo
		$this->option_codeproduitservice = 1; // Display product-service code
		$this->option_multilang = 1; // Available in several languages
		$this->option_freetext = 0; // Support add of a personalised text
		$this->option_draft_watermark = 1; // Support add of a watermark on drafts

		// Get source company
		$this->issuer = $mysoc;
		if (empty($this->issuer->country_code)) $this->issuer->country_code = substr($langs->defaultlang, -2); // By default, if was not defined

		// Define position of columns
		$this->posxnum = $this->margin_left + 1;
		$this->numwidth = 10;
		$this->posxdesc = $this->margin_left + 1;
		$this->posxsampleplace = 120;
		$this->posxsampleperson = 165;
		$this->sample_gap = 5;
		
		$this->posxstandard = 60;
		$this->posxaccuracy = 90;
		$this->posxtestdate = 110;
		$this->posxminimum = 135;
		$this->posxmaximum = 150;
		$this->posxresult= 165;
		$this->posxunit= 180;

		$this->posxpicture = $this->posxstandard - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH) ? 20 : $conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH); // width of images
		if ($this->page_width < 210) // To work with US executive format
		{
			$this->posxsampleplace -= 20;
			$this->posxsampleperson -= 20;
			
			$this->posxstandard -= 20;
			$this->posxaccuracy -= 20;
			$this->posxtestdate -= 20;
			$this->posxminimum -= 20;
			$this->posxmaximum -= 20;
			$this->posxresult -= 20;
			$this->posxunit -= 20;
		}
	}

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
     *  Function to build pdf onto disk
     *
     *  @param		Object		$object				Object to generate
     *  @param		Translate	$outputlangs		Lang output object
     *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int			$hidedetails		Do not show line details
     *  @param		int			$hidedesc			Do not show desc
     *  @param		int			$hideref			Do not show ref
     *  @return     int         	    			1=OK, 0=KO
	 */
    public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
        // phpcs:enable
		global $user, $langs, $conf, $mysoc, $hookmanager, $nblines;

		dol_syslog("write_file outputlangs->defaultlang=".(is_object($outputlangs) ? $outputlangs->defaultlang : 'null'));

		if (!is_object($outputlangs)) $outputlangs = $langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output = 'ISO-8859-1';

		// Load translation files required by the page
		$outputlangs->loadLangs(array("main", "bills", "products", "dict", "companies", "lims@lims"));

		$nblines = count($object->lines);

		// Loop on each lines to detect if there is at least one image to show
		$realpatharray = array();
		if (!empty($conf->global->MAIN_GENERATE_INVOICES_WITH_PICTURE))
		{
			for ($i = 0; $i < $nblines; $i++)
			{
				$method = new Methods($this->db);
				$method->fetch($object->lines[$i]->fk_method);
				if (empty($method->fk_product)) continue;

				$objphoto = new Product($this->db);
				$objphoto->fetch($method->fk_product);

				$pdir = get_exdir($method->fk_product, 2, 0, 0, $objphoto, 'product').$method->fk_product."/photos/";
				$dir = $conf->product->dir_output.'/'.$pdir;
				
				//dol_syslog(__METHOD__." path=".$dir, LOG_DEBUG);
				
				$realpath = '';
				foreach ($objphoto->liste_photos($dir, 1) as $key => $obj)
				{
					$filename = $obj['photo'];
					//if ($obj['photo_vignette']) $filename='thumbs/'.$obj['photo_vignette'];
					$realpath = $dir.$filename;
					break;
				}

				if ($realpath) $realpatharray[$i] = $realpath;
			}
		}
		if (count($realpatharray) == 0) $this->posxpicture = $this->posxstandard;

		if ($conf->facture->dir_output)
		{
			$object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->lims->dir_output;
				$file = $dir."/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->lims->dir_output."/".$objectref;
				$file = $dir."/".$objectref.".pdf";
			}
			if (!file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (!is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager = new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('beforePDFCreation', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks

				// Set nblines with the new sample lines content after hook
				$nblines = count($object->lines);
				// Create pdf instance
				$pdf = pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
                $pdf->SetAutoPageBreak(1, 0);

                $heightforinfotests = 35; // Height reserved to output information in tests and signatures
		        $heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5); // Height reserved to output the free text on last page
				if ($this->option_freetext==0) 
					$heightforfreetext = 0;
					
	            $heightforfooter = $this->margin_bottom + 8; // Height reserved to output the footer (value include bottom margin)
	            if ($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS > 0) $heightforfooter += 6;

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));

                // Set path to the background PDF File
                if (!empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
                {
                	$pagecount = $pdf->setSourceFile($conf->mycompany->multidir_output[$object->entity].'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
				    $tplidx = $pdf->importPage(1);
                }

				$pdf->Open();
				$pagenb = 0;
				$pdf->SetDrawColor(128, 128, 128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("PdfSampleTitle"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("PdfSampleTitle")." ".$outputlangs->convToOutputCharset($object->thirdparty->name));
				if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				// Set certificate
				$cert=empty($user->conf->CERTIFICATE_CRT) ? '' : $user->conf->CERTIFICATE_CRT;
				// If user has no certificate, we try to take the company one
				if (!$cert) {
					$cert = empty($conf->global->CERTIFICATE_CRT) ? '' : $conf->global->CERTIFICATE_CRT;
				}
				// If a certificate is found
				if ($cert) {
					$info = array(
						'Name' => $this->issuer->name,
						'Location' => getCountry($this->issuer->country_code, 0),
						'Reason' => 'Test Report',
						'ContactInfo' => $this->issuer->email
					);
					$pdf->setSignature($cert, $cert, $this->issuer->name, '', 2, $info);
				}

				$pdf->SetMargins($this->margin_left, $this->margin_top, $this->margin_right);   // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (!empty($tplidx)) $pdf->useTemplate($tplidx); // with background
				$pagenb++;

				$top_shift = $this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->MultiCell(0, 3, ''); // Set interline to 3
				$pdf->SetTextColor(0, 0, 0);

				$tab_top = 90 + $top_shift;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 42 + $top_shift : 10);
				
				// Print sample description
				$sampledescription = empty($object->description) ? '' : $object->description;
				$nexYsampleline = array();
				if ($sampledescription)
				{
					$tab_top -= 2;
					$pdf->SetFont('', 'B', $default_font_size-1);
					$pdf->writeHTMLCell($this->posxsampleplace-$this->posxdesc, 3, $this->posxdesc - 1, $tab_top - 1, $outputlangs->transnoentities("HeaderSampleDescription"), 0, 1);
					$tab_top_sampleplace = $tab_top;
					
					$nexY = $pdf->GetY();
					$tab_top = $nexY + 1;
					$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$sampledescription = make_substitutions($sampledescription, $substitutionarray, $outputlangs);
					$sampledescription = convertBackOfficeMediasLinksToPublicLinks($sampledescription);

					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->writeHTMLCell($this->posxsampleplace-$this->posxdesc, 3, $this->posxdesc - 1, $tab_top - 1, dol_htmlentitiesbr($sampledescription), 0, 1);
					$nexY = $pdf->GetY();
					$height_note = $nexY - $tab_top;

					// Rect takes a length in 3rd parameter
					$pdf->SetDrawColor(192, 192, 192);
					$pdf->Rect($this->margin_left, $tab_top - 1, $this->posxsampleplace - $this->posxdesc, $height_note + 1);
					
					$nexYsampleline[0] = $nexY;
				}
				
				// Print Sampling Place 
				$sample_place = empty($object->place) ? '' : $object->place;
				$lon = empty($object->place_lon) ? '' : $object->place_lon;
				$lat = empty($object->place_lat) ? '' : $object->place_lat;
				if( !empty($lon) && !empty($lat) ) $sample_place .= '<br />'.$lon.' | '.$lat;
				
				if ($sample_place)
				{
					$pdf->SetFont('', 'B', $default_font_size-1);
					$pdf->writeHTMLCell($this->posxsampleperson - $this->posxsampleplace, 3, $this->posxsampleplace, $tab_top_sampleplace - 1, $outputlangs->transnoentities("HeaderSamplePlace"), 0, 1);

					$nexY = $pdf->GetY();
					$tab_top = $nexY + 1;
					$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$sample_place = make_substitutions($sample_place, $substitutionarray, $outputlangs);
					$sample_place = convertBackOfficeMediasLinksToPublicLinks($sample_place);

					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->writeHTMLCell($this->posxsampleperson - $this->posxsampleplace, 3, $this->posxsampleplace, $tab_top - 1, dol_htmlentitiesbr($sample_place), 0, 1);
					$nexY = $pdf->GetY();
					$height_note = $nexY - $tab_top;

					// Rect takes a length in 3rd parameter
					$pdf->SetDrawColor(192, 192, 192);
					$pdf->Rect($this->posxsampleplace, $tab_top - 1, $this->posxsampleperson - $this->posxsampleplace - 1, $height_note + 1);

					$nexYsampleline[1] = $nexY;
				}
				
				// Print Sampling Person
				// TODO: fk_socpeople not yet working!!
				// $sample_person_user = empty($object->fk_user) ? '' : $object->fk_user;
				if ( empty($object->fk_user) )
					$sample_person_user = '';
				else{
					$userobj = new User($object->db);
					$userobj->fetch($object->fk_user);
					$sample_person_user = $userobj->getFullName($outputlangs);
				}
				if ($sample_person_user)
				{
					$pdf->SetFont('', 'B', $default_font_size-1);
					$pdf->writeHTMLCell($this->page_textwidth - $this->posxsampleperson, 3, $this->posxsampleperson, $tab_top_sampleplace - 1, $outputlangs->transnoentities("HeaderSamplePerson"), 0, 1);

					$nexY = $pdf->GetY();
					$tab_top = $nexY + 1;
					$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$sample_person_user = make_substitutions($sample_person_user, $substitutionarray, $outputlangs);
					$sample_person_user = convertBackOfficeMediasLinksToPublicLinks($sample_person_user);

					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->writeHTMLCell($this->page_textwidth - $this->posxsampleplace, 3, $this->posxsampleperson, $tab_top - 1, dol_htmlentitiesbr($sample_person_user), 0, 1);
					$nexY = $pdf->GetY();
					$height_note = $nexY - $tab_top;

					// Rect takes a length in 3rd parameter
					$pdf->SetDrawColor(192, 192, 192);
					$pdf->Rect($this->posxsampleperson, $tab_top - 1, $this->page_width - $this->margin_right - $this->posxsampleperson, $height_note + 1);
					
					$nexYsampleline[2] = $nexY;
				}
				
				// Print notes
				$notetoshow = empty($object->note_public) ? '' : $object->note_public;
				
				$nexY = max($nexYsampleline);
				$tab_top_note = $nexY + 4;
				
				if ($notetoshow)
				{	
					$tab_top = $tab_top_note - 2;
					//$tab_top -= 2;
					$pdf->SetFont('', 'B', $default_font_size-1);
					$pdf->writeHTMLCell($this->page_textwidth, 3, $this->posxdesc - 1, $tab_top - 1, $outputlangs->transnoentities("HeaderSampleNote"), 0, 1);
					$nexY = $pdf->GetY();
					
					$tab_top = $nexY + 1;
					$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$notetoshow = make_substitutions($notetoshow, $substitutionarray, $outputlangs);
					$notetoshow = convertBackOfficeMediasLinksToPublicLinks($notetoshow);

					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->writeHTMLCell(190, 3, $this->posxdesc - 1, $tab_top - 1, dol_htmlentitiesbr($notetoshow), 0, 1);
					$nexY = $pdf->GetY();
					$height_note = $nexY - $tab_top;

					// Rect takes a length in 3rd parameter
					$pdf->SetDrawColor(192, 192, 192);
					$pdf->Rect($this->margin_left, $tab_top - 1, $this->page_textwidth, $height_note + 1);

					$tab_top = $nexY + 6;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Loop on each lines
				$method = new Methods($this->db);
				
				for ($i = 0; $i < $nblines; $i++)
				{
					$method->fetch($object->lines[$i]->fk_method);
					
					$curY = $nexY;
					$pdf->SetFont('', '', $default_font_size - 1); // Into loop to work with multipage
					$pdf->SetTextColor(0, 0, 0);

					// Define size of image if we need it
					$imglinesize = array();
					if (!empty($realpatharray[$i])) $imglinesize = pdf_getSizeForImage($realpatharray[$i]);

					$pdf->setTopMargin($tab_top_newpage);
					$pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext + $heightforinfotests); // The only function to edit the bottom margin of current page to set it.
					$pageposbefore = $pdf->getPage();

					$showpricebeforepagebreak = 1;
					$posYAfterImage = 0;
					$posYAfterDescription = 0;

					// We start with Photo of product line
					if (isset($imglinesize['width']) && isset($imglinesize['height']) && ($curY + $imglinesize['height']) > ($this->page_height - ($heightforfooter + $heightforfreetext + $heightforinfotests)))	// If photo too high, we moved completely on new page
					{
						$pdf->AddPage('', '', true);
						if (!empty($tplidx)) $pdf->useTemplate($tplidx);
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						$pdf->setPage($pageposbefore + 1);

						$curY = $tab_top_newpage;

						// Allows data in the first page if description is long enough to break in multiples pages
						if (!empty($conf->global->MAIN_PDF_DATA_ON_FIRST_PAGE))
							$showpricebeforepagebreak = 1;
						else
							$showpricebeforepagebreak = 0;
					}

					if (isset($imglinesize['width']) && isset($imglinesize['height']))
					{
						$curX = $this->posxpicture - 1;
						$pdf->Image($realpatharray[$i], $curX + (($this->posxstandard - $this->posxpicture - $imglinesize['width']) / 2), $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300); // Use 300 dpi
						// $pdf->Image does not increase value return by getY, so we save it manually
						$posYAfterImage = $curY + $imglinesize['height'];
					}

					// Description of product line
					$curX = $this->posxdesc - 1;

					$pdf->startTransaction();
					// hook on pdf_writelinedesc called here
					pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->posxpicture - $curX - $progress_width, 3, $curX, $curY, $hideref, $hidedesc);
					$pageposafter = $pdf->getPage();
					if ($pageposafter > $pageposbefore)	// There is a pagebreak
					{
						$pdf->rollbackTransaction(true);
						$pageposafter = $pageposbefore;
						//print $pageposafter.'-'.$pageposbefore;exit;
						$pdf->setPageOrientation('', 1, $heightforfooter); // The only function to edit the bottom margin of current page to set it.
						pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->posxpicture - $curX - $progress_width, 3, $curX, $curY, $hideref, $hidedesc);
						$pageposafter = $pdf->getPage();
						$posyafter = $pdf->GetY();
						//var_dump($posyafter); var_dump(($this->page_height - ($heightforfooter+$heightforfreetext+$heightforinfotests))); exit;
						if ($posyafter > ($this->page_height - ($heightforfooter + $heightforfreetext + $heightforinfotests)))	// There is no space left for total+free text
						{
							if ($i == ($nblines - 1))	// No more lines, and no space left to show total, so we create a new page
							{
								$pdf->AddPage('', '', true);
								if (!empty($tplidx)) $pdf->useTemplate($tplidx);
								if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
								$pdf->setPage($pageposafter + 1);
							}
						}
						else
						{
							// We found a page break

							// Allows data in the first page if description is long enough to break in multiples pages
							if (!empty($conf->global->MAIN_PDF_DATA_ON_FIRST_PAGE))
								$showpricebeforepagebreak = 1;
							else
								$showpricebeforepagebreak = 0;
						}
					}
					else	// No pagebreak
					{
						$pdf->commitTransaction();
					}
					$posYAfterDescription = $pdf->GetY();

					$nexY = $pdf->GetY();
					$pageposafter = $pdf->getPage();
					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->margin_top);
					$pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.

					// We suppose that a too long description or photo were moved completely on next page
					if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
					}

					$pdf->SetFont('', '', $default_font_size - 1); // We reposition the default font

					// Standard (ISO...)
					$standard = $method->standard;
					$pdf->SetXY($this->posxstandard, $curY);
					$pdf->MultiCell($this->posxaccuracy - $this->posxstandard, 3, $standard, 0, 'L');

					// Accuracy 
					$accuracy = $method->accuracy;
					$pdf->SetXY($this->posxaccuracy, $curY);
					$pdf->MultiCell($this->posxtestdate - $this->posxaccuracy, 3, $accuracy, 0, 'C');

					// Test-Date
					$testdate = dol_print_date($object->lines[$i]->end, 'dayrfc');
					$pdf->SetXY($this->posxtestdate, $curY);
					$pdf->MultiCell($this->posxminimum - $this->posxtestdate, 3, $testdate, 0, 'C'); // Enough for 6 chars

					// Minimum
					$minimum = $object->lines[$i]->minimum;
					$pdf->SetXY($this->posxminimum, $curY);
					$pdf->MultiCell($this->posxmaximum - $this->posxminimum, 3, $minimum, 0, 'C');

					// Maximum
					$pdf->SetXY($this->posxmaximum, $curY);
					$maximum = $object->lines[$i]->maximum;
					$pdf->MultiCell($this->posxresult - $this->posxmaximum, 3, $maximum, 0, 'C');

					// Result
					$result = lims_functions::numberFormatPrecision($object->lines[$i]->result,$method->resolution);
					// check if min/max is set and $result outside min/max
					if ( (!is_null($minimum) && $result < $minimum) || (!is_null($maximum) && $result > $maximum) )
						$pdf->SetFont('', 'B', $default_font_size - 1); // Make bold if outside min/max
					
					// check if result outside of measurement range
					if (!is_null($method->range_lower) && $result < $method->range_lower)
						$result = '< '.$method->range_lower;
					if (!is_null($method->range_upper) && $result > $method->range_upper)
						$result = '> '.$method->range_upper;
					
					$pdf->SetXY($this->posxresult, $curY);
					$pdf->MultiCell($this->posxunit - $this->posxresult, 3, $result, 0, 'C');
					$pdf->SetFont('', '', $default_font_size - 1); // in any case reset text style

					// Unit
					$unit = $method->unit;
					$pdf->SetXY($this->posxunit, $curY);
					$pdf->MultiCell($this->page_width - $this->margin_right - $this->posxunit, 3, $unit, 0, 'L');

					if ($posYAfterImage > $posYAfterDescription) $nexY = $posYAfterImage;

					// Add line
					if (!empty($conf->global->MAIN_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1))
					{
						$pdf->setPage($pageposafter);
						$pdf->SetLineStyle(array('dash'=>'1,1', 'color'=>array(80, 80, 80)));
						//$pdf->SetDrawColor(190,190,200);
						$pdf->line($this->margin_left, $nexY + 1, $this->page_width - $this->margin_right, $nexY + 1);
						$pdf->SetLineStyle(array('dash'=>0));
					}

					$nexY += 2; // Add space between lines

					// Detect if some page were added automatically and output _tableau for past pages
					while ($pagenb < $pageposafter)
					{
						$pdf->setPage($pagenb);
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $this->page_height - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_height - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
						}
						$this->_pagefoot($pdf, $object, $outputlangs, $this->option_freetext);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
					}
					if (isset($object->lines[$i + 1]->pagebreak) && $object->lines[$i + 1]->pagebreak)
					{
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $this->page_height - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_height - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
						}
						$this->_pagefoot($pdf, $object, $outputlangs, $this->option_freetext);
						// New page
						$pdf->AddPage();
						if (!empty($tplidx)) $pdf->useTemplate($tplidx);
						$pagenb++;
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
					}
				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $this->page_height - $tab_top - $heightforinfotests - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0, $object->multicurrency_code);
					$bottomlasttab = $this->page_height - $heightforinfotests - $heightforfreetext - $heightforfooter + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_height - $tab_top_newpage - $heightforinfotests - $heightforfreetext - $heightforfooter, 0, $outputlangs, 1, 0, $object->multicurrency_code);
					$bottomlasttab = $this->page_height - $heightforinfotests - $heightforfreetext - $heightforfooter + 1;
				}

				// Display info area, maximal height=heightforinfotests
				$posy = $this->tests_info($pdf, $object, $bottomlasttab, $outputlangs);

				// Pagefoot
				$this->_pagefoot($pdf, $object, $outputlangs, $this->option_freetext);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file, 'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters = array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
				global $action;
				$reshook = $hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
				if ($reshook < 0)
				{
				    $this->error = $hookmanager->error;
				    $this->errors = $hookmanager->errors;
				}

				if (!empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				$this->result = array('fullpath'=>$file);

				return 1; // No error
			}
			else
			{
				$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		}
		else
		{
			$this->error = $langs->transnoentities("ErrorConstantNotDefined", "FAC_OUTPUTDIR");
			return 0;
		}
	}


	
	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   Show table for lines
	 *
	 *   @param		PDF			$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y (not used)
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @param		string		$currency		Currency code
	 *   @return	void
	 */
	
	protected function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop = 0, $hidebottom = 0, $currency = '')
	{
		global $conf;

		// Force to disable hidetop and hidebottom
		$hidebottom = 0;
		if ($hidetop) $hidetop = -1;

		$currency = !empty($currency) ? $currency : $conf->currency;
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Amount in (at tab_top - 1)
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('', '', $default_font_size - 2);

		if (empty($hidetop))
		{
			//$titre = $outputlangs->transnoentities("AmountInCurrency", $outputlangs->transnoentitiesnoconv("Currency".$currency));
			//$pdf->SetXY($this->page_width - $this->margin_right - ($pdf->GetStringWidth($titre) + 3), $tab_top - 4);
			//$pdf->MultiCell(($pdf->GetStringWidth($titre) + 3), 2, $titre);

			//$conf->global->MAIN_PDF_TITLE_BACKGROUND_COLOR='230,230,230';
			if (!empty($conf->global->MAIN_PDF_TITLE_BACKGROUND_COLOR)) $pdf->Rect($this->margin_left, $tab_top, $this->page_width - $this->margin_right - $this->margin_left, 5, 'F', null, explode(',', $conf->global->MAIN_PDF_TITLE_BACKGROUND_COLOR));
		}

		$pdf->SetDrawColor(128, 128, 128);
		$pdf->SetFont('', '', $default_font_size - 1);

		// Output Rect
		$this->printRect($pdf, $this->margin_left, $tab_top, $this->page_width - $this->margin_left - $this->margin_right, $tab_height, $hidetop, $hidebottom); // Rect takes a length in 3rd parameter and 4th parameter

		// Number
		$pdf->line($this->posxnum - 1, $tab_top, $this->posxnum - 1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxnum - 1, $tab_top + 1);
			$pdf->MultiCell($this->posxdesc - $this->posxnum + 3, 2, $outputlangs->transnoentities("ReportTitleNum"), '', 'L');
		}
		
		if (empty($hidetop))
		{
			$pdf->line($this->margin_left, $tab_top + 5, $this->page_width - $this->margin_right, $tab_top + 5); // line takes a position y in 2nd parameter and 4th parameter

			$pdf->SetXY($this->posxdesc - 1, $tab_top + 1);
			$pdf->MultiCell(108, 2, $outputlangs->transnoentities("ReportTitleDescription"), '', 'L');
		}

		if (!empty($conf->global->MAIN_GENERATE_INVOICES_WITH_PICTURE))
		{
			$pdf->line($this->posxpicture - 1, $tab_top, $this->posxpicture - 1, $tab_top + $tab_height);
			if (empty($hidetop))
			{
				$pdf->SetXY($this->posxpicture-1, $tab_top+1);
				$pdf->MultiCell($this->posxstandard-$this->posxpicture-1,2, $outputlangs->transnoentities("Photo"),'','C');
			}
		}
		
		// STANDARD (ISO ...)
		$pdf->line($this->posxstandard - 1, $tab_top, $this->posxstandard - 1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxstandard - 1, $tab_top + 1);
			$pdf->MultiCell($this->posxaccuracy - $this->posxstandard + 3, 2, $outputlangs->transnoentities("MethodMethod"), '', 'L');
		}
		
		// ACCURACY
		$pdf->line($this->posxaccuracy - 1, $tab_top, $this->posxaccuracy - 1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxaccuracy, $tab_top + 1);
			$pdf->MultiCell($this->posxtestdate - $this->posxaccuracy - 1, 2, $outputlangs->transnoentities("ReportTitleAccuracy"), '', 'C');
		}

		// TESTDATE
		$pdf->line($this->posxtestdate - 1, $tab_top, $this->posxtestdate - 1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxtestdate, $tab_top + 1);
			$pdf->MultiCell($this->posxminimum - $this->posxtestdate - 1, 2, $outputlangs->transnoentities("ReportTitleDate"), '', 'C');
		}

		// MINIMUM
		$pdf->line($this->posxminimum - 1, $tab_top, $this->posxminimum - 1, $tab_top + $tab_height);
		if (empty($hidetop)) {
			$pdf->SetXY($this->posxminimum, $tab_top + 1);
			$pdf->MultiCell($this->posxmaximum - $this->posxminimum, 2, $outputlangs->transnoentities("ReportTitleMinimum"), '', 'C');
		}

		// MAXIMUM
		$pdf->line($this->posxmaximum - 1, $tab_top, $this->posxmaximum - 1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxmaximum, $tab_top + 1);
			$pdf->MultiCell($this->posxresult - $this->posxmaximum, 2, $outputlangs->transnoentities("ReportTitleMaximum"), '', 'C');
		}

		// RESULT
		$pdf->line($this->posxresult - 1, $tab_top, $this->posxresult - 1, $tab_top + $tab_height);
		if (empty($hidetop)) {
			$pdf->SetXY($this->posxresult, $tab_top + 1);
			$pdf->MultiCell($this->posxunit - $this->posxresult, 2, $outputlangs->transnoentities("Result"), '', 'C');
		}

		// UNIT
		$pdf->line($this->posxunit - 1, $tab_top, $this->posxunit - 1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxunit - 1, $tab_top + 1);
			$pdf->MultiCell(30, 2, $outputlangs->transnoentities("MethodUnit"), '', 'L');
		}
	}
	
	/* Function shall print a note like:
		The results relate only to the items tested.
		If not stated otherwise tests have been conducted at our own laboratory and without any abnormality.
		<optional: Test 1,3,5 performed at __other laboratory__.
		<optional: Test 2 showed abormality.
	*/
	public function tests_info(&$pdf, $object, $posy, $outputlangs)
	{
		global $conf, $langs;

		dol_syslog(__METHOD__, LOG_DEBUG);
		// Load traductions files required by page
		$outputlangs->loadLangs(array("lims@lims"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetFont('', '', $default_font_size - 1);

		// Print limit set name
		$limits = new Limits($this->db);
		$limits->fetch($object->fk_limits);

		if($limits)
		{
			$pdf->SetXY($this->margin_left, $posy);
			$pdf->MultiCell($this->page_textwidth, 2, $outputlangs->transnoentities("ReportLimitsApplied").$limits->label, 0, 'L', 0);
			$posy = $pdf->GetY() + 1;
		}

		// Statement: The results relate only to the items tested
		$pdf->SetXY($this->margin_left, $posy);
		$pdf->MultiCell($this->page_textwidth, 2, $outputlangs->transnoentities("ReportStatementA"), 0, 'L', 0);
		$posy = $pdf->GetY() + 1;

		// Statement: tests conducted at own laboratory without any abnormality
		$pdf->SetXY($this->margin_left, $posy);
		$pdf->MultiCell($this->page_textwidth, 2, $outputlangs->transnoentities("ReportStatementB"), 0, 'L', 0);
		$posy = $pdf->GetY() + 1;
		
		// If tests with 'abnormality' set
		$nblines = count($object->lines);
		$i = 0;
		$abnormalitiesfound = false;
		$abnormalities = $outputlangs->transnoentities("ReportTestsWithAbnormalities");
		
		// Technicians array
		$technician_arr = array();
		$technician_arr_i = 0;
		
		while ($i < $nblines)
		{
			if ($object->lines[$i]->abnormalities){
				$abnormalities .= '('.$i.')';
				$abnormalitiesfound = true;
			}
			
			if (!in_array($object->lines[$i]->fk_user, $technician_arr)){
				$technician_arr[$technician_arr_i] = $object->lines[$i]->fk_user;
				$technician_arr_i++;
				dol_syslog('technician_arr='.var_export($technician_arr,true), LOG_DEBUG);
			}
			$i++;
		}
		if ($abnormalitiesfound){
			$pdf->SetXY($this->margin_left, $posy);
			$pdf->MultiCell($this->page_textwidth, 2, $abnormalities, 0, 'L', 0);
			$posy = $pdf->GetY() + 3;
		}
		$posy_column = $posy;
		// Show responsible person
		$responsible = $outputlangs->transnoentities("ReportResponsible").'<br />';
		$signingperson = new User($this->db);
		$i = 0;
		while ($i < $technician_arr_i)
		{
			$signingperson->fetch($technician_arr[$i]);
			$responsible .= $signingperson->getFullName($outputlangs).' ('.$signingperson->job.')';
			if ($technician_arr_i > 0 && $i != $technician_arr_i)
				$responsible .= '<br />';
			
			$i++;
		}
		
		$pdf->writeHTMLCell($this->page_textwidth, 3, $this->margin_left, $posy, $responsible, 0, 1);
		$posy = $posy_column;
		
		if (is_numeric($object->fk_user_approval)){	
			$responsible = $outputlangs->transnoentities("ReportAuthorizing").'<br />';
			$signingperson->fetch($object->fk_user_approval);
			
			$responsible .= $signingperson->getFullName($outputlangs).' ('.$signingperson->job.')';
			$responsible .= '<br />'.$outputlangs->transnoentities("DigitalSigned");
			$responsible .= dol_print_date(dol_now(),'dayrfc');
			$pdf->writeHTMLCell($this->page_textwidth/2, 3, $this->margin_left+$this->page_textwidth/2, $posy, $responsible, 0, 1);
			$posy = $pdf->GetY() + 3;
		}
		return $posy > $posy_column ? $posy : $posy_column;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf, $langs;

		// Load traductions files required by page
		$outputlangs->loadLangs(array("main", "bills", "propal", "companies", "lims@lims"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf, $outputlangs, $this->page_height);

		// Show Draft Watermark
		if ($object->statut == Facture::STATUS_DRAFT && (!empty($conf->global->FACTURE_DRAFT_WATERMARK)))
        {
		      pdf_watermark($pdf, $outputlangs, $this->page_height, $this->page_width, 'mm', $conf->global->FACTURE_DRAFT_WATERMARK);
        }

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$w = 110;

		$posy = $this->margin_top;
        $posx = $this->page_width - $this->margin_right - $w;

		$pdf->SetXY($this->margin_left, $posy);

		// Logo
		if (empty($conf->global->PDF_DISABLE_MYCOMPANY_LOGO))
		{
			if ($this->issuer->logo)
			{
				$logodir = $conf->mycompany->dir_output;
				if (!empty($conf->mycompany->multidir_output[$object->entity])) $logodir = $conf->mycompany->multidir_output[$object->entity];
				if (empty($conf->global->MAIN_PDF_USE_LARGE_LOGO))
				{
					$logo = $logodir.'/logos/thumbs/'.$this->issuer->logo_small;
				}
				else {
					$logo = $logodir.'/logos/'.$this->issuer->logo;
				}
				if (is_readable($logo))
				{
				    $height = pdf_getHeightForLogo($logo);
					$pdf->Image($logo, $this->margin_left, $posy, 0, $height); // width=0 (auto)
				}
				else
				{
					$pdf->SetTextColor(200, 0, 0);
					$pdf->SetFont('', 'B', $default_font_size - 2);
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
					$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
				}
			}
			else
			{
				$text = $this->issuer->name;
				$pdf->MultiCell($w, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
			}
		}

		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$title = $outputlangs->transnoentities("PdfSampleTitle");
		/*
		if ($object->type == 1) $title = $outputlangs->transnoentities("InvoiceReplacement");
		if ($object->type == 2) $title = $outputlangs->transnoentities("InvoiceAvoir");
		if ($object->type == 3) $title = $outputlangs->transnoentities("InvoiceDeposit");
		if ($object->type == 4) $title = $outputlangs->transnoentities("InvoiceProForma");
		if ($this->situationinvoice) $title = $outputlangs->transnoentities("InvoiceSituation");
		*/
		$pdf->MultiCell($w, 3, $title, '', 'R');

		$pdf->SetFont('', 'B', $default_font_size);

		$posy += 5;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$textref = $outputlangs->transnoentities("Ref")." : ".$outputlangs->convToOutputCharset($object->ref);
		if ($object->status != $object::STATUS_VALIDATED)
		{
			$pdf->SetTextColor(128, 0, 0);
			$textref .= ' - '.$outputlangs->transnoentities("NotValidated");
		}
		$pdf->MultiCell($w, 4, $textref, '', 'R');

		$posy += 1;
		$pdf->SetFont('', '', $default_font_size - 2);

		// CLIENT REF --- NOT USED BY SAMPLE
		if ($object->ref_client)
		{
			$posy += 4;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("RefCustomer")." : ".$outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}

		// SHOW PROJECT
		if (!empty($conf->global->PDF_SHOW_PROJECT_TITLE))
		{
			$object->fetch_projet();
			if (!empty($object->project->ref))
			{
				$posy += 3;
				$pdf->SetXY($posx, $posy);
				$pdf->SetTextColor(0, 0, 60);
				$pdf->MultiCell($w, 3, $outputlangs->transnoentities("Project")." : ".(empty($object->project->title) ? '' : $object->projet->title), '', 'R');
			}
		}

		if (!empty($conf->global->PDF_SHOW_PROJECT))
		{
			$object->fetch_projet();
			if (!empty($object->project->ref))
			{
				$posy += 3;
				$pdf->SetXY($posx, $posy);
				$pdf->SetTextColor(0, 0, 60);
				$pdf->MultiCell($w, 3, $outputlangs->transnoentities("RefProject")." : ".(empty($object->project->ref) ? '' : $object->projet->ref), '', 'R');
			}
		}

// Report Replacing other Report not wished for ?
/*
		$objectidnext = $object->getIdReplacingInvoice('validated');
		if ($object->type == 0 && $objectidnext)
		{
			$objectreplacing = new Facture($this->db);
			$objectreplacing->fetch($objectidnext);

			$posy += 3;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ReplacementByInvoice").' : '.$outputlangs->convToOutputCharset($objectreplacing->ref), '', 'R');
		}
		if ($object->type == 1)
		{
			$objectreplaced = new Facture($this->db);
			$objectreplaced->fetch($object->fk_facture_source);

			$posy += 4;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("ReplacementInvoice").' : '.$outputlangs->convToOutputCharset($objectreplaced->ref), '', 'R');
		}
		if ($object->type == 2 && !empty($object->fk_facture_source))
		{
			$objectreplaced = new Facture($this->db);
			$objectreplaced->fetch($object->fk_facture_source);

			$posy += 3;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("CorrectionInvoice").' : '.$outputlangs->convToOutputCharset($objectreplaced->ref), '', 'R');
		}
*/

		// SHOW DATE OF SAMPLING
		$posy += 4;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell($w, 3, $outputlangs->transnoentities("DateSampling")." : ".dol_print_date($object->date, "day", false, $outputlangs, true), '', 'R');
		// SHOW DATE OF SAMPLE RECEIPT
		$posy += 3;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell($w, 3, $outputlangs->transnoentities("DateSampleReceived")." : ".dol_print_date($object->date_arrival, "day", false, $outputlangs, true), '', 'R');
		// SHOW DATE OF REPORT
		$posy += 3;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->MultiCell($w, 3, $outputlangs->transnoentities("DateReport")." : ".dol_print_date($object->tms, "day", false, $outputlangs), '', 'R');
		
		if ($object->thirdparty->code_client)
		{
			$posy += 4;
			$pdf->SetXY($posx, $posy);
			$pdf->SetTextColor(0, 0, 60);
			$pdf->MultiCell($w, 3, $outputlangs->transnoentities("CustomerCode")." : ".$outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		// Get contact
		if (!empty($conf->global->DOC_SHOW_FIRST_SALES_REP))
		{
		    $arrayidcontact = $object->getIdContact('internal', 'SALESREPFOLL');
		    if (count($arrayidcontact) > 0)
		    {
		        $usertmp = new User($this->db);
		        $usertmp->fetch($arrayidcontact[0]);
                $posy += 4;
                $pdf->SetXY($posx, $posy);
		        $pdf->SetTextColor(0, 0, 60);
		        $pdf->MultiCell($w, 3, $langs->transnoentities("SalesRepresentative")." : ".$usertmp->getFullName($langs), '', 'R');
		    }
		}

		$posy += 1;

		$top_shift = 0;
		// Show list of linked objects
		$current_y = $pdf->getY();
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, $w, 3, 'R', $default_font_size);
		if ($current_y < $pdf->getY())
		{
			$top_shift = $pdf->getY() - $current_y;
		}

		if ($showaddress)
		{
			// Sender properties
			$carac_issuer = pdf_build_address($outputlangs, $this->issuer, $object->thirdparty, '', 0, 'source', $object);

			// Show sender
			$posy = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 42;
			$posy += $top_shift;
			$posx = $this->margin_left;
			if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx = $this->page_width - $this->margin_right - 80;

			$hautcadre = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 38 : 40;
			$widthrecbox = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 82;


			// Show sender frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx, $posy - 5);
			$pdf->MultiCell(66, 5, $outputlangs->transnoentities("BillFrom").":", 0, 'L');
			$pdf->SetXY($posx, $posy);
			$pdf->SetFillColor(230, 230, 230);
			$pdf->MultiCell($widthrecbox, $hautcadre, "", 0, 'R', 1);
			$pdf->SetTextColor(0, 0, 60);

			// Show sender name
			$pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox - 2, 4, $outputlangs->convToOutputCharset($this->issuer->name), 0, 'L');
			$posy = $pdf->getY();

			// Show sender information
			$pdf->SetXY($posx + 2, $posy);
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox - 2, 4, $carac_issuer, 0, 'L');



			// If BILLING contact defined on invoice, we use it
			$usecontact = false;
			$arrayidcontact = $object->getIdContact('external', 'BILLING');
			if (count($arrayidcontact) > 0)
			{
				$usecontact = true;
				$result = $object->fetch_contact($arrayidcontact[0]);
			}

			//Recipient name
			// On peut utiliser le nom de la societe du contact
			if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) {
				$thirdparty = $object->contact;
			} else {
				$thirdparty = $object->thirdparty;
			}

			$carac_client_name = pdfBuildThirdpartyName($thirdparty, $outputlangs);

			$carac_client = pdf_build_address($outputlangs, $this->issuer, $object->thirdparty, ($usecontact ? $object->contact : ''), $usecontact, 'target', $object);

			// Show recipient
			$widthrecbox = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 92 : 100;
			if ($this->page_width < 210) $widthrecbox = 84; // To work with US executive format
			$posy = !empty($conf->global->MAIN_PDF_USE_ISO_LOCATION) ? 40 : 42;
			$posy += $top_shift;
			$posx = $this->page_width - $this->margin_right - $widthrecbox;
			if (!empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx = $this->margin_left;

			// Show recipient frame
			$pdf->SetTextColor(0, 0, 0);
			$pdf->SetFont('', '', $default_font_size - 2);
			$pdf->SetXY($posx + 2, $posy - 5);
			$pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillTo").":", 0, 'L');
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$pdf->SetXY($posx + 2, $posy + 3);
			$pdf->SetFont('', 'B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 2, $carac_client_name, 0, 'L');

			$posy = $pdf->getY();

			// Show recipient information
			$pdf->SetFont('', '', $default_font_size - 1);
			$pdf->SetXY($posx + 2, $posy);
			$pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
		}

		$pdf->SetTextColor(0, 0, 0);
		return $top_shift;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   	Show footer of page. Need this->issuer object
     *
	 *   	@param	PDF			$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		global $conf;
		$showdetails = $conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_pagefoot($pdf, $outputlangs, 'INVOICE_FREE_TEXT', $this->issuer, $this->margin_bottom, $this->margin_left, $this->page_height, $object, $showdetails, $hidefreetext);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return list of active generation modules
	 *
	 *  @param	DoliDB	$db     			Database handler
	 *  @param  integer	$maxfilenamelength  Max length of value to show
	 *  @return	array						List of templates
	 */
	public static function liste_modeles($db, $maxfilenamelength = 0)
	{
		// phpcs:enable
		global $conf;

		$type = 'lims';
		$liste = array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$liste = getListOfModels($db, $type, $maxfilenamelength);

		return $liste;
	}
}