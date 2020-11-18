table<?php
/* Copyright (C) 2017 	Laurent Destailleur <eldy@stocks.sourceforge.net>
 * Copyright (C) 2020 David Bensel <david.bensel@gmail.com>
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

/** original file: htdocs/core/modules/stock/doc/pdf_standard.modules.php
 *	\file       core/modules/lims/doc/pdf_standardlist.modules.php
 *  \ingroup    lims
 *  \brief      File of class to build PDF documents for lists of equipment
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/stock/modules_stock.php';
require_once DOL_DOCUMENT_ROOT.'/product/stock/class/entrepot.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';


/**
 *	Class to build documents using ODF templates generator
 */
class pdf_standard_equipmentlist extends ModelePDFStock
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
	 * @var string document type
	 */
	public $type;

	/**
	 * @var array Minimum version of PHP required by module.
	 * e.g.: PHP ≥ 5.6 = array(5, 6)
	 */
	public $phpmin = array(5, 6);

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
	 * @var int left_margin
	 */
	public $left_margin;

	/**
	 * @var int right_margin
	 */
	public $right_margin;

	/**
	 * @var int top_margin
	 */
	public $top_margin;

	/**
	 * @var int bottom_margin
	 */
	public $bottom_margin;

	/**
	 * Issuer
	 * @var Societe
	 */
	public $issuer;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;

		// Load traductions files required by page
		$langs->loadLangs(array("main", "companies", "lims"));

		$this->db = $db;
		$this->name = "standard";
		$this->description = $langs->trans("DocumentModelStandardPDF");

		// Page size for A4 format
		$this->type = 'pdf';
		$formatarray = pdf_getFormat();
		$this->page_width = $formatarray['width'];
		$this->page_height = $formatarray['height'];
		$this->format = array($this->page_width, $this->page_height);
		$this->left_margin = isset($conf->global->MAIN_PDF_MARGIN_LEFT) ? $conf->global->MAIN_PDF_MARGIN_LEFT : 10;
		$this->right_margin = isset($conf->global->MAIN_PDF_MARGIN_RIGHT) ? $conf->global->MAIN_PDF_MARGIN_RIGHT : 10;
		$this->top_margin = isset($conf->global->MAIN_PDF_MARGIN_TOP) ? $conf->global->MAIN_PDF_MARGIN_TOP : 10;
		$this->bottom_margin = isset($conf->global->MAIN_PDF_MARGIN_BOTTOM) ? $conf->global->MAIN_PDF_MARGIN_BOTTOM : 10;

		$this->option_logo = 1; // Affiche logo
		$this->option_issuer = 1; // Show Company Name
		$this->option_codestockservice = 0; // Affiche code stock-service
		$this->option_multilang = 1; // Dispo en plusieurs langues
		$this->option_freetext = 0; // Support add of a personalised text

		// Recupere issuer
		$this->issuer = $mysoc;
		if (!$this->issuer->country_code) $this->issuer->country_code = substr($langs->defaultlang, -2); // By default if not defined

		// Define columns: name - left position - alignment
		$this->tblposx = array(
			 array("EquipmentListReportTblHeadEquipment", $this->left_margin + 1, 'L'),
			 array("EquipmentListReportTblHeadLabel", $this->left_margin + 37, 'L'),
			 array("EquipmentListReportTblHeadDescription", $this->left_margin + 81, 'L'),
			 array("EquipmentListReportTblHeadIntervall", $this->left_margin + 115, 'C'),
			 array("EquipmentListReportTblHeadLastDate", $this->left_margin + 130, 'L'),
			 array("EquipmentListReportTblHeadLastUser", $this->left_margin + 150, 'L'),
			 array("EquipmentListReportTblHeadStatus", $this->left_margin + 170, 'C'),
		);

		// Report does not handle pics
		//$this->posxpicture = $this->tblposx[2][1] - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH) ? 20 : $conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH); // width of images

		if ($this->page_width < 210) // To work with US executive format
		{
			for ($i = 0; $i < count($this->tblposx); $i++)  {
				$this->tblposx[$i][1] -= 20;
			}
		}
	}


	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *	Function to build a document on disk using the generic odt module.
	 *
	 *	@param		Entrepot	$object				Object source to build document
	 *	@param		Translate	$outputlangs		Lang output object
	 * 	@param		string		$srctemplatepath	Full path of source filename for generator using a template file
	 *  @param		int			$hidedetails		Do not show line details
	 *  @param		int			$hidedesc			Do not show desc
	 *  @param		int			$hideref			Do not show ref
	 *	@return		int         					1 if OK, <=0 if KO
	 */
	public function write_file($object, $outputlangs, $srctemplatepath = '', $hidedetails = 0, $hidedesc = 0, $hideref = 0)
	{
		// phpcs:enable
		global $user, $langs, $conf, $mysoc, $db, $hookmanager;

    //unset($object); // terminate object of class
    $object_entrepot = new Entrepot($db);
    //TODO: which warehouse to pick?
    $id=2;
    $ret = $object_entrepot->fetch($id);
    if ($ret <= 0) {
      setEventMessages($object_entrepot->error, $object_entrepot->errors, 'errors');
    }

		if (!is_object($outputlangs)) $outputlangs = $langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (!empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output = 'ISO-8859-1';

		// Load traductions files required by page
		$outputlangs->loadLangs(array("main", "dict", "companies", "bills", "stocks", "orders", "deliveries"));

    $records = array();
    $records = $object->fetchAll();

		$nblines = count($records);

		if ($conf->stock->dir_output)
		{
			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->lims->dir_output;
				$file = $dir."/SPECIMEN.pdf";
			} else {
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->lims->dir_output."/".$objectref;
				$file = $dir."/".$objectref.".pdf";
			}

			$stockFournisseur = new ProductFournisseur($this->db);
			$supplierprices = $stockFournisseur->list_product_fournisseur_price($object->id);
			$object->supplierprices = $supplierprices;

			if (!file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error = $langs->transnoentities("ErrorCanNotCreateDir", $dir);
					return -1;
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

				// Create pdf instance
				$pdf = pdf_getInstance($this->format);
				$default_font_size = pdf_getPDFFontSize($outputlangs); // Must be after pdf_getInstance
				$pdf->SetAutoPageBreak(1, 0);

				$heightforinfotot = 40; // Height reserved to output the info and total part
				$heightforfreetext = (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT) ? $conf->global->MAIN_PDF_FREETEXT_HEIGHT : 5); // Height reserved to output the free text on last page
				$heightforfooter = $this->bottom_margin + 20; // Height reserved to output the footer (value include bottom margin)

				if (class_exists('TCPDF'))
				{
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (empty($conf->global->MAIN_DISABLE_FPDI) && !empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
				{
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				$pdf->Open();

        $pdf->SetDrawColor(128, 128, 128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("Stock"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Stock")." ".$outputlangs->convToOutputCharset($object->label));
				if (!empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->left_margin, $this->top_margin, $this->right_margin); // Left, Top, Right


				// New page
				$pdf->AddPage();
				if (!empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb = 1;
				$top_shift = $this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('', '', $default_font_size - 1);
				$pdf->MultiCell(0, 3, ''); // Set interline to 3
				$pdf->SetTextColor(0, 0, 0);

				$tab_top = $top_shift + 40;
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD) ? 40 + $top_shift : 10);

				// Not used
				//$tab_height = 130;


				/* ************************************************************************** */
				/*                                                                            */
				/* Show list of product in warehouse                                          */
				/*                                                                            */
				/* ************************************************************************** */

				$totalunit = 0;
				$totalvalue = $totalvaluesell = 0;

				$sortfield = 'p.ref';
				$sortorder = 'ASC';

				$sql = "SELECT p.rowid as rowid, p.ref, p.label as produit, p.tobatch, p.fk_product_type as type, p.pmp as ppmp, p.price, p.price_ttc, p.entity,";
				$sql .= " ps.reel as value";
				$sql .= " FROM ".MAIN_DB_PREFIX."product_stock as ps, ".MAIN_DB_PREFIX."product as p";
				$sql .= " WHERE ps.fk_product = p.rowid";
				$sql .= " AND ps.reel <> 0"; // We do not show if stock is 0 (no product in this warehouse)
				$sql .= " AND ps.fk_entrepot = ".$object->id;
				$sql .= $this->db->order($sortfield, $sortorder);

				//dol_syslog('List products', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if ($resql)
				{
					$num = $this->db->num_rows($resql);
					$i = 0;
					$nblines = $num;

					$this->tabTitleHeight = 0;
					$nexY = $tab_top + $this->tabTitleHeight;

					for ($i = 0; $i < $nblines; $i++)
					{
						$curY = $nexY;

						$objp = $this->db->fetch_object($resql);

						// Multilangs
						if (!empty($conf->global->MAIN_MULTILANGS)) // if the option is active
						{
							$sql = "SELECT label";
							$sql .= " FROM ".MAIN_DB_PREFIX."product_lang";
							$sql .= " WHERE fk_product=".$objp->rowid;
							$sql .= " AND lang='".$this->db->escape($langs->getDefaultLang())."'";
							$sql .= " LIMIT 1";

							$result = $this->db->query($sql);
							if ($result)
							{
								$objtp = $this->db->fetch_object($result);
								if ($objtp->label != '') $objp->produit = $objtp->label;
							}
						}

						$pdf->SetFont('', '', $default_font_size - 1); // Into loop to work with multipage
						$pdf->SetTextColor(0, 0, 0);

						$pdf->setTopMargin($tab_top_newpage);
						$pdf->setPageOrientation('', 1, $heightforfooter + $heightforfreetext + $heightforinfotot); // The only function to edit the bottom margin of current page to set it.
						$pageposbefore = $pdf->getPage();

						// Description of product line
						$curX = $this->tblposx[0][1] - 1;

						$showpricebeforepagebreak = 1;

						$pdf->startTransaction();
						pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->tblposx[2][1] - $curX, 3, $curX, $curY, $hideref, $hidedesc);
						$pageposafter = $pdf->getPage();
						if ($pageposafter > $pageposbefore)	// There is a pagebreak
						{
							$pdf->rollbackTransaction(true);
							$pageposafter = $pageposbefore;
							//print $pageposafter.'-'.$pageposbefore;exit;
							$pdf->setPageOrientation('', 1, $heightforfooter); // The only function to edit the bottom margin of current page to set it.
							pdf_writelinedesc($pdf, $object, $i, $outputlangs, $this->tblposx[2][1] - $curX, 4, $curX, $curY, $hideref, $hidedesc);
							$pageposafter = $pdf->getPage();
							$posyafter = $pdf->GetY();
							if ($posyafter > ($this->page_height - ($heightforfooter + $heightforfreetext + $heightforinfotot)))	// There is no space left for total+free text
							{
								if ($i == ($nblines - 1))	// No more lines, and no space left to show total, so we create a new page
								{
									$pdf->AddPage('', '', true);
									if (!empty($tplidx)) $pdf->useTemplate($tplidx);
									if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $top_shift = $this->_pagehead($pdf, $object, 0, $outputlangs);
									$pdf->setPage($pageposafter + 1);
								}
							} else {
								// We found a page break

								// Allows data in the first page if description is long enough to break in multiples pages
								if (!empty($conf->global->MAIN_PDF_DATA_ON_FIRST_PAGE))
									$showpricebeforepagebreak = 1;
								else $showpricebeforepagebreak = 0;
							}
						} else // No pagebreak
						{
							$pdf->commitTransaction();
						}
						$posYAfterDescription = $pdf->GetY();

						$nexY = $pdf->GetY();
						$pageposafter = $pdf->getPage();

						$pdf->setPage($pageposbefore);
						$pdf->setTopMargin($this->top_margin);
						$pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.

						// We suppose that a too long description is moved completely on next page
						if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
							$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
						}

						$pdf->SetFont('', '', $default_font_size - 1); // We reset the default font
						// Print Rows
						$this->tablerow($pdf, $nexY, $objp, $i, $nblines, $totalunit, $pageposafter);
						$nexY = $pdf->GetY(); // Linebreak in cells possible

						// Detect if some page were added automatically and output table for past pages
						while ($pagenb < $pageposafter)
						{
							$pdf->setPage($pagenb);
							if ($pagenb == 1) // First page of report
							{
								// parameter $currency $object->multicurrency_code to show text
								$this->tableheader($pdf, $tab_top-8, $this->page_height - $tab_top - $heightforfooter, $outputlangs, 0, 1, 'none');
							}
							else
							{
								$this->tableheader($pdf, $tab_top_newpage-8, $this->page_height - $tab_top_newpage - $heightforfooter, $outputlangs, 0, 1, 'none');
							}
							$this->_pagefoot($pdf, $object, $outputlangs, 1);
							$pagenb++;
							$pdf->setPage($pagenb);
							$pdf->setPageOrientation('', 1, 0); // The only function to edit the bottom margin of current page to set it.
							if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $top_shift = $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
						if (isset($object->lines[$i + 1]->pagebreak) && $object->lines[$i + 1]->pagebreak)
						{
							if ($pagenb == 1)
							{
								$this->tableheader($pdf, $tab_top-8, $this->page_height - $tab_top - $heightforfooter, $outputlangs, 0, 1, 'none');
							} else {
								$this->tableheader($pdf, $tab_top_newpage-8, $this->page_height - $tab_top_newpage - $heightforfooter, $outputlangs, 0, 1, 'none');
							}
							$this->_pagefoot($pdf, $object, $outputlangs, 1);
							// New page
							$pdf->AddPage();
							if (!empty($tplidx)) $pdf->useTemplate($tplidx);
							$pagenb++;
							if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $top_shift = $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
					}

					$this->db->free($resql);

					/**
					 * Footer table
					 */

					$nexY += 2;
					$curY = $nexY;

					$this->tablesum($pdf, $curY, $outputlangs, $nblines, $totalunit);
				} else {
					dol_print_error($this->db);
				}

				// Displays notes
				$notetoshow = empty($object->note_public) ? '' : $object->note_public;

				if ($notetoshow)
				{
					$substitutionarray = pdf_getSubstitutionArray($outputlangs, null, $object);
					complete_substitutions_array($substitutionarray, $outputlangs, $object);
					$notetoshow = make_substitutions($notetoshow, $substitutionarray, $outputlangs);
					$notetoshow = convertBackOfficeMediasLinksToPublicLinks($notetoshow);

					$tab_top = 88;

					$pdf->SetFont('', '', $default_font_size - 1);
					$pdf->writeHTMLCell(190, 3, $this->tblposx[1][1] - 1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					$nexY = $pdf->GetY();
					$height_note = $nexY - $tab_top;

					// Rect takes a length in 3rd parameter
					$pdf->SetDrawColor(192, 192, 192);
					$pdf->Rect($this->left_margin, $tab_top - 1, $this->page_width - $this->left_margin - $this->right_margin, $height_note + 1);

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY + 6;
				} else {
					$height_note = 0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				$tab_top = $tab_top_newpage + 25 + $top_shift;

				// Show square
				if ($pagenb == 1)
				{
					$this->tableheader($pdf, $tab_top-8, $this->page_height - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter, $outputlangs, 0, 1, 'none');
				} else {
					$this->tableheader($pdf, $tab_top_newpage-8, $this->page_height - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, $outputlangs, 0, 1, 'none');
				}

				$bottomlasttab = $this->page_height - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;

				// Displays info zone
				//$posy=$this->_tableau_info($pdf, $object, $bottomlasttab, $outputlangs);

				// Displays totals area
				//$posy=$this->_tableau_tot($pdf, $object, $deja_regle, $bottomlasttab, $outputlangs);

				// Footer
				$this->_pagefoot($pdf, $object, $outputlangs);
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
			} else {
				$this->error = $langs->trans("ErrorCanNotCreateDir", $dir);
				return 0;
			}
		} else {
			$this->error = $langs->trans("ErrorConstantNotDefined", "PRODUCT_OUTPUTDIR");
			return 0;
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.Protected
	/**
	 *   Print a row of the table
	 *
	 *   @param     TCPDF		$pdf						Object PDF
	 *   @param     int			$curY						Y
	 *	 @param			object  $objp
	 *	 @param			int     $i
	 *	 @param			int			$nblines
	 *	 @param			int			$totalunit
	 *	 @param			int			$pageposafter
	 *   @return    void
	 */
	protected function tablerow(&$pdf, &$curY, $objp, $i, $nblines, &$totalunit, $pageposafter)
	{
		global $conf;

		$productstatic = new Product($this->db);

		$productstatic->id = $objp->rowid;
		$productstatic->ref = $objp->ref;
		$productstatic->label = $objp->produit;
		$productstatic->type = $objp->type;
		$productstatic->entity = $objp->entity;
		$productstatic->status_batch = $objp->tobatch;

		$valtoshow = price2num($objp->value, 'MS');
		$towrite = (empty($valtoshow) ? '0' : $valtoshow);
		$totalunit += $objp->value;

		$rowitems = array();
		$rowitems = $this->tblposx;
		$rowitems[0][1] = dol_trunc($productstatic->ref, 18);
		$rowitems[1][1] = dol_trunc($productstatic->label, 24);
		$rowitems[2][1] = $towrite;
		$rowitems[3][1] = '1d';
		$rowitems[4][1] = 'yyyy-mm-dd';
		$rowitems[5][1] = 'Some User';
		$rowitems[6][1] = 'x';
		//$curY_max = $curY;
		$index = 0;
		$num = count($this->tblposx);
		foreach ($this->tblposx as $key => $value) {
			$pdf->SetXY($value[1], $curY);
			if ($index < $num-1)
				$pdf->MultiCell($this->tblposx[$index+1][1] - $value[1], 3, $rowitems[$index][1], '', $value[2]);
			else
				$pdf->MultiCell($this->page_width - $this->left_margin - $value[1], 3, $rowitems[$index][1], '', $value[2]);
			//$curY_max = ($curY_max > $pdf->GetY() ? $curY_max : $pdf->GetY()); // In case height gets set dynamically
			$index++;
		}

		// Draw line
		if (!empty($conf->global->MAIN_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1))
		{
			$pdf->setPage($pageposafter);
			$pdf->SetLineStyle(array('dash'=>'1,1', 'color'=>array(80, 80, 80)));
			//$pdf->SetDrawColor(190,190,200);
			$pdf->line($this->left_margin, $curY-1, $this->page_width - $this->right_margin, $curY-1);
			$pdf->SetLineStyle(array('dash'=>0));
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.Public
	/**
	 *   Print table header for lines
	 *
	 *   @param     TCPDF			$pdf     			Object PDF
	 *   @param     string		$tab_top			Top position of table
	 *   @param     string		$tab_height		Height of table (rectangle)
	 *   @param     Translate	$outputlangs	Langs object
	 *   @param     int				$hidetop			1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param     int				$hidebottom		Hide bottom bar of array
	 *   @param     string		$currency			Currency code
	 *   @return    void
	 */
	protected function tableheader(&$pdf, $tab_top, $tab_height, $outputlangs, $hidetop = 0, $hidebottom = 0, $currency = '')
	{
		global $conf;

		if ($hidebottom) $hidebottom = -1;
		if ($hidetop) $hidetop = -1;

		$currency = !empty($currency) ? $currency : $conf->currency;
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Amount in (at tab_top - 1)
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFont('', '', $default_font_size - 2);

		if (empty($hidetop) && $currency != 'none')
		{
			$titre = $outputlangs->transnoentities("AmountInCurrency", $outputlangs->transnoentitiesnoconv("Currency".$currency));
			$pdf->SetXY($this->page_width - $this->right_margin - ($pdf->GetStringWidth($titre) + 3), $tab_top - 4);
			$pdf->MultiCell(($pdf->GetStringWidth($titre) + 3), 2, $titre);

			//$conf->global->MAIN_PDF_TITLE_BACKGROUND_COLOR='230,230,230';
			if (!empty($conf->global->MAIN_PDF_TITLE_BACKGROUND_COLOR)) $pdf->Rect($this->left_margin, $tab_top, $this->page_width - $this->right_margin - $this->left_margin, 5, 'F', null, explode(',', $conf->global->MAIN_PDF_TITLE_BACKGROUND_COLOR));
		}

		$pdf->SetDrawColor(128, 128, 128);
		$pdf->SetFont('', 'B', $default_font_size - 3);

		// Output Rect
		//$this->printRect($pdf,$this->left_margin, $tab_top, $this->page_width-$this->left_margin-$this->right_margin, $tab_height, $hidetop, $hidebottom);	// Rect takes a length in 3rd parameter and 4th parameter

		$pdf->SetLineStyle(array('dash'=>'0', 'color'=>array(200, 200, 200)));
		$pdf->SetDrawColor(200, 200, 200);
		$pdf->line($this->left_margin, $tab_top, $this->page_width - $this->right_margin, $tab_top);
		$pdf->SetLineStyle(array('dash'=>0));
		$pdf->SetDrawColor(128, 128, 128);
		$pdf->SetTextColor(0, 0, 120);

		$i = 0;
		$num = count($this->tblposx);
		foreach ($this->tblposx as $value) {
			$pdf->SetXY($value[1], $tab_top);
			if ($i < $num-1)
				$pdf->MultiCell($this->tblposx[$i+1][1] - $value[1], 3, $outputlangs->transnoentities($value[0]), '', $value[2]);
			else
				$pdf->MultiCell($this->page_width - $this->left_margin - $value[1], 3, $outputlangs->transnoentities($value[0]), '', $value[2]);
			$i++;
		}

		if (empty($hidebottom))
		{
			$pdf->SetDrawColor(200, 200, 200);
			$pdf->SetLineStyle(array('dash'=>'0', 'color'=>array(200, 200, 200)));
			$pdf->line($this->left_margin, $tab_top + 11, $this->page_width - $this->right_margin, $tab_top + 11);
			$pdf->SetLineStyle(array('dash'=>0));
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.Protected
	/**
	 *   Print sum of table column
	 *
	 *   @param     TCPDF		$pdf						Object PDF
	 *   @param     int			$curY						Y
	 *	 @param			int		  $outputlangs
	 *	 @param			int			$nblines
	 *	 @param			int			$totalunit
	 *   @return    void
	 */
	protected function tablesum(&$pdf, $curY, $outputlangs, $nblines, $totalunit)
	{
		global $conf, $langs;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		if ($nblines > 0) {
			$pdf->SetLineStyle(array('dash'=>'0', 'color'=>array(200, 200, 200)));
			$pdf->line($this->left_margin, $curY - 1, $this->page_width - $this->right_margin, $curY - 1);
			$pdf->SetLineStyle(array('dash'=>0));

			$pdf->SetFont('', 'B', $default_font_size - 1);
			$pdf->SetTextColor(0, 0, 120);

			// Print "Total" to column #2
			$pdf->SetXY($this->tblposx[1][1], $curY);
			$pdf->MultiCell($this->tblposx[2][1] - $this->tblposx[2][1], 3, $langs->trans("Total"), 0, $this->tblposx[1][2]);

			// Print quantity to column #3
			$valtoshow = price2num($totalunit, 'MS');
			$towrite = empty($valtoshow) ? '0' : $valtoshow;

			$pdf->SetXY($this->tblposx[2][1], $curY);
			$pdf->MultiCell($this->tblposx[3][1] - $this->tblposx[2][1] - 0.8, 3, $towrite, 0, $this->tblposx[1][2]);
		}
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *  Show top header of page.
	 *
	 *  @param	TCPDF		$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @param	string		$titlekey		Translation key to show as title of document
	 *  @return	int                         Return topshift value
	 */
	protected function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $titlekey = "")
	{
		global $conf, $langs, $db, $hookmanager;

		// Load translation files required by page
		// TODO: remove not required files
		$outputlangs->loadLangs(array("main", "propal", "companies", "bills", "orders", "stocks", "lims"));

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		// Print Company Logo in background if MAIN_USE_BACKGROUND_ON_PDF is set
		pdf_pagehead($pdf, $outputlangs, $this->page_height);

		// Show Draft Watermark
		if ($object->statut == 0 && (!empty($conf->global->COMMANDE_DRAFT_WATERMARK)))
		{
			pdf_watermark($pdf, $outputlangs, $this->page_height, $this->page_width, 'mm', $conf->global->COMMANDE_DRAFT_WATERMARK);
		}

		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', 'B', $default_font_size + 3);

		$posy = $this->top_margin;
		$posx = $this->page_width - $this->right_margin - 100;

		$pdf->SetXY($this->left_margin, $posy);

		// Logo
		$logo = $conf->mycompany->dir_output.'/logos/'.$this->issuer->logo;
		if ($this->issuer->logo)
		{
			if (is_readable($logo))
			{
				$height = pdf_getHeightForLogo($logo);
				$pdf->Image($logo, $this->left_margin, $posy, 0, $height); // width=0 (auto)
			} else {
				$pdf->SetTextColor(200, 0, 0);
				$pdf->SetFont('', 'B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound", $logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
			$posy = $pdf->GetY() + $height;
		}

		$posyAfterCompanyName = $posy;

		if ($this->option_issuer) // Print Company Name
		{
			$pdf->SetXY($this->left_margin, $posy);
			$text = $this->issuer->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
			$posyAfterCompanyName = $pdf->GetY();
			$posy = $this->top_margin;
		}

		$pdf->SetFont('', 'B', $default_font_size + 3);
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		if ($titlekey == "")
			$title = $outputlangs->transnoentities("EquipmentListReportTitle");
		else
			$title = $outputlangs->transnoentities($titlekey);
		$pdf->MultiCell(100, 3, $title, '', 'R');

		$posy += 7;
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', '', $default_font_size);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("DateBuild")." : ".dol_print_date(dol_now(), 'dayrfc'), '', 'R');

		$posy += 5;
		$numCalibrated = $object->numCalibrated();
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', '', $default_font_size);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("EquipmentListReportNumCalibrated").' : '.$numCalibrated, '', 'R');

		$posy += 5;
		$numMaintained = $object->numMaintained();
		$pdf->SetXY($posx, $posy);
		$pdf->SetTextColor(0, 0, 60);
		$pdf->SetFont('', '', $default_font_size);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("EquipmentListReportNumMaintained").' : '.$numMaintained, '', 'R');

		if ($posyAfterCompanyName > $posy){
			$top_shift = $posyAfterCompanyName - $this->top_margin;
		}
		else {
			$top_shift = $posy - $this->top_margin;
		}

		$pdf->SetTextColor(0, 0, 0);

		return $top_shift;
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.PublicUnderscore
	/**
	 *   	Show footer of page. Need this->issuer object
	 *
	 *   	@param	TCPDF		$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	protected function _pagefoot(&$pdf, $object, $outputlangs, $hidefreetext = 0)
	{
		global $conf;
		$showdetails = $conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_pagefoot($pdf, $outputlangs, 'PRODUCT_FREE_TEXT', $this->issuer, $this->bottom_margin, $this->left_margin, $this->page_height, $object, $showdetails, $hidefreetext);
	}
}
