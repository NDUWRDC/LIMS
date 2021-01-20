<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2020  David Bensel <david.bensel@gmail.com>
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
 * \file        class/samples.class.php
 * \ingroup     lims
 * \brief       This file is a CRUD class file for Samples (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commoninvoice.class.php';
dol_include_once('/lims/class/results.class.php', 'Results');
dol_include_once('/lims/class/methods.class.php', 'Methods');
dol_include_once('/lims/class/limits.class.php', 'Limits');
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Samples
 */
class Samples extends CommonObject
{
	/**
	 * @var string ID of module.
	 */
	public $module = 'lims';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'samples';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'lims_samples';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for samples. Must be the part after the 'object_' into object_samples.png
	 */
	public $picto = 'samples@lims';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'maxwidth200', 'wordbreak', 'tdoverflowmax200'
	 *  'help' is a string visible as a tooltip on field
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>1, 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'label'=>'SAlabelCustomer', 'enabled'=>1, 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'help'=>"SAlabelCustomerHelp",),
		'fk_propal' => array('type'=>'integer:Propal:comm/propal/class/propal.class.php', 'label'=>'SAlabelCustomerProposal', 'enabled'=>0, 'position'=>30, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'help'=>"SAlabelCustomerProposalHelp",),
		'fk_facture' => array('type'=>'integer:Facture:compta/facture/class/facture.class.php', 'label'=>'SAlabelCustomerInvoice', 'enabled'=>1, 'position'=>40, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'help'=>"SAlabelCustomerInvoiceHelp",),
		'samplingbyclient' => array('type'=>'integer', 'label'=>'SAlabelSamplingByClient', 'enabled'=>1, 'position'=>50, 'notnull'=>-1, 'visible'=>3, 'default'=>'1', 'index'=>1, 'arrayofkeyval'=>array('0'=>'No', '1'=>'Yes'), 'help'=>"SAlabelSamplingByClientHelp",),
		'fk_user' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'SAlabelSaboratorySampleTaker', 'enabled'=>1, 'position'=>60, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'help'=>"SAlabelSaboratorySampleTakerHelp",),
		'fk_user_approval' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'SAlabelManager', 'enabled'=>1, 'position'=>65, 'notnull'=>-1, 'visible'=>0, 'default'=>null, 'index'=>1, 'help'=>"SAlabelManagerHelp",),
		'description' => array('type'=>'text', 'label'=>'SAlabelDescription', 'enabled'=>1, 'position'=>67, 'notnull'=>0, 'visible'=>3, 'help'=>"SAlabelDescriptionHelp",),
		'label' => array('type'=>'varchar(255)', 'label'=>'SAlabelSampleName', 'enabled'=>1, 'position'=>70, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'help'=>"SAlabelSampleNameHelp",),
		'volume' => array('type'=>'real', 'label'=>'SAlabelVolume', 'enabled'=>1, 'position'=>80, 'notnull'=>0, 'visible'=>3, 'help'=>"SAlabelVolumeHelp",),
		'qty' => array('type'=>'integer', 'label'=>'SAlabelNumberOfContainers', 'enabled'=>1, 'position'=>90, 'notnull'=>0, 'visible'=>1, 'index'=>1, 'isameasure'=>'1', 'help'=>"SAlabelNumberOfContainersHelp",),
		'fk_location' => array('type'=>'sellist:lims_location:short_label', 'label'=>'SAlabelLocation', 'enabled'=>1, 'position'=>95, 'notnull'=>0, 'visible'=>1, 'foreignkey'=>'lims_location.rowid', 'help'=>"SAlabelLocationHelp",),
		'version' => array('type'=>'integer', 'label'=>'SAlabelVersion', 'enabled'=>1, 'position'=>97, 'notnull'=>1, 'default'=>'0', 'visible'=>5, 'index'=>1, 'isameasure'=>'0', 'help'=>"SAlabelVersionHelp",),
		'date' => array('type'=>'datetime', 'label'=>'SAlabelSamplingDateTime', 'enabled'=>1, 'position'=>100, 'notnull'=>0, 'visible'=>1, 'help'=>"SAlabelSamplingDateTimeHelp",),
		'place' => array('type'=>'varchar(128)', 'label'=>'SAlabelSamplingPlace', 'enabled'=>1, 'position'=>110, 'notnull'=>0, 'visible'=>3, 'help'=>"SAlabelSamplingPlaceHelp",),
		'place_lon' => array('type'=>'real', 'label'=>'SAlabelGPSlong', 'enabled'=>1, 'position'=>120, 'notnull'=>-1, 'visible'=>3, 'help'=>"SAlabelGPSlongHelp",),
		'place_lat' => array('type'=>'real', 'label'=>'SAlabelGPSlat', 'enabled'=>1, 'position'=>130, 'notnull'=>-1, 'visible'=>3, 'help'=>"SAlabelGPSlatHelp",),
		'date_arrival' => array('type'=>'datetime', 'label'=>'SAlabelArrivalDateTime', 'enabled'=>1, 'position'=>140, 'notnull'=>0, 'visible'=>1, 'help'=>"SAlabelArrivalDateTimeHelp",),
		'date_approval' => array('type'=>'datetime', 'label'=>'SAlabelApprovalDateTime', 'enabled'=>1, 'position'=>145, 'notnull'=>0, 'visible'=>5,'help'=>"SAlabelApprovalDateTimeHelp",),
		'fk_project' => array('type'=>'integer:Project:projet/class/project.class.php:1', 'label'=>'SAlabelProject', 'enabled'=>1, 'position'=>150, 'notnull'=>-1, 'visible'=>-1, 'index'=>1, 'help'=>"SAlabelProjectHelp",),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'position'=>170, 'notnull'=>0, 'visible'=>-1, 'help'=>"SAlabelNotePublicHelp",),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'position'=>180, 'notnull'=>0, 'visible'=>-1, 'help'=>"SAlabelNotePrivateHelp",),
		'fk_limits' => array('type'=>'integer:Limits:lims/class/limits.class.php', 'label'=>'SAlabelLimitSet', 'enabled'=>1, 'position'=>200, 'notnull'=>0, 'visible'=>3, 'help'=>"SAlabelLimitSetHelp",),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>1, 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>1, 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>1, 'position'=>600, 'notnull'=>0, 'visible'=>0,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>1, 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>1, 'position'=>1011, 'notnull'=>1, 'visible'=>2, 'default'=>'0', 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Validated', '9'=>'Canceled'),),
	);
	public $rowid;
	public $ref;
	public $fk_soc;
	public $fk_propal;
	public $fk_facture;
	public $samplingbyclient;
	public $fk_user;
	public $fk_user_approval;
	public $label;
	public $volume;
	public $qty;
	public $fk_location;
	public $version;
	public $date;
	public $place;
	public $place_lon;
	public $place_lat;
	public $date_arrival;
	public $date_approval;
	public $fk_project;
	public $description;
	public $note_public;
	public $note_private;
	public $fk_limits;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $import_key;
	public $model_pdf;
	public $status;
	// END MODULEBUILDER PROPERTIES

	// If this object has a subtable with lines

	/**
	 * @var int    Name of subtable line
	 */
	public $table_element_line = 'lims_results';

	/**
	 * @var int    Field with ID of parent key if this object has a parent
	 */
	public $fk_element = 'fk_samples';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	public $class_element_line = 'Results';

	/**
	 * @var array	List of child tables. To test if we can delete object.
	 */
	protected $childtables = array();

	/**
	 * @var array    List of child tables. To know object to delete on cascade.
	 *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	 *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	 */
	protected $childtablesoncascade = array('lims_results');

	/**
	 * @var SamplesLine[]     Array of subtable lines
	 */
	public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		// TODO: must be handled via constants?
		$this->model_pdf = 'lims_testreport';
		$this->status = self::STATUS_DRAFT;

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->lims->samples->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs))
		{
			foreach ($this->fields as $key => $val)
			{
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval']))
				{
					foreach ($val['arrayofkeyval'] as $key2 => $val2)
					{
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();
		$ResultObj = new Results($this->db);
	    $i = 0;
	    foreach($this->lines as $line){
	    	$line->fetch_optionals();
			$object->lines[$i]->ref = empty($ResultObj->fields['ref']['default']) ? "copy_of_".$line->ref : $ResultObj->fields['ref']['default'];
			$object->lines[$i]->setDraft($user);
			$object->lines[$i]->label = empty($ResultObj->fields['label']['default']) ? $langs->trans("CopyOf")." ".$line->label : $ResultObj->fields['label']['default'];
			unset($object->lines[$i]->id);
			unset($object->lines[$i]->ref);
			
			$i++;
		}

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) $object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		if (property_exists($object, 'label')) $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		if (property_exists($object, 'status')) { $object->status = self::STATUS_DRAFT; }
		if (property_exists($object, 'date_creation')) { $object->date_creation = dol_now(); }
		if (property_exists($object, 'date_modification')) { $object->date_modification = null; }
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey]))
				{
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error)
		{
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0)
			{
				$error++;
			}
		}

		if (!$error)
		{
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid)
			{
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{	/*
		// Module Builder START
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
		// Module Builder END
		*/
		$morewhere = ' ORDER BY rang ASC';
		$objectlineclassname = 'Results';
		dol_syslog(__METHOD__.' objectlineclassname='.$objectlineclassname, LOG_DEBUG);
		$objectline = new $objectlineclassname($this->db);
		$sql = 'SELECT '.$objectline->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$objectline->table_element;
		$sql .= ' WHERE fk_'.$this->element.' = '.$this->id;
		if ($morewhere)  $sql .= $morewhere;
		//dol_syslog(__METHOD__.' $sql='.$sql, LOG_DEBUG);
		$resqlRESULTS = $this->db->query($sql);
		
		$limits = new Limits ($this->db);
		$limits->fetch($this->fk_limits);
		$rows_limits = count($limits->lines);
		//$filter = array('customsql' => 'fk_limits = '.$this->fk_limits);
		//$limitlines->fetchLines();
		//$limitlines->fetchAll('', '', '', '', $filter);
		/*
		$sql = 'SELECT minimum, maximum, fk_method';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_limits_entries';
		$sql .= ' WHERE fk_limits = '.$this->fk_limits;
		$resqlLIMITS = $this->db->query($sql);
		*/
		if ($resqlRESULTS)
		{
			$num_rows = $this->db->num_rows($resqlRESULTS);
			//dol_syslog(__METHOD__.' num_rows='.$num_rows, LOG_DEBUG);
			$i = 0;
			while ($i < $num_rows){	
				$obj = $this->db->fetch_object($resqlRESULTS);
				if ($obj){
					$newline = new $objectlineclassname($this->db);
					$newline->setVarsFromFetchObj($obj);

					$this->lines[$i] = $newline;
					
					$l = 0;
					while ($l < $rows_limits ){
						if ($this->lines[$i]->fk_method == $limits->lines[$l]->fk_method){
							$this->lines[$i]->minimum = $limits->lines[$l]->minimum;
							$this->lines[$i]->maximum = $limits->lines[$l]->maximum;
							
							// DEFINE LABEL HERE??$this->lines[$i]->label = ...
							//dol_syslog(" line->maximum=".var_export($this->lines[$i]->maximum, true), LOG_DEBUG);
							//dol_syslog(" limits->maximum=".var_export($limits->lines[$i]->maximum, true), LOG_DEBUG);
							break;
						}
						$l++;
					}
				}
				//dol_syslog(__METHOD__." $this->lines[i]=".var_export($this->lines[$i], true), LOG_DEBUG);
				$i++;
			}
			return 1;
		}
		else{
			$this->error = $this->db->lasterror();
			$this->errors[] = $this->error;
			return -1;
		}
		
		return $result;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key.' IN ('.$this->db->sanitize($this->db->escape($value)).')';
				} else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0)
		{
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->samples->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->samples->samples_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;
		$this->version++;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) $sql .= ", date_validation = '".$this->db->idate($now)."'";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
			if (!empty($this->fields['version'])) $sql .= ", version = ".$this->version;
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql)
			{
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger)
			{
				// Call trigger
				$result = $this->call_trigger('SAMPLES_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'samples/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'samples/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->lims->dir_output.'/samples/'.$oldref;
				$dirdest = $conf->lims->dir_output.'/samples/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->lims->dir_output.'/samples/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry)
						{
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error)
		{
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error)
		{
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->lims_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'SAMPLES_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->lims_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'SAMPLES_CLOSE');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_CANCELED)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->lims->lims_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'SAMPLES_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = img_picto('', $this->picto).' <u>'.$langs->trans("Samples").'</u>';
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;
		if (isset($this->status)) {
			$label .= '<br><b>'.$langs->trans("Status").":</b> ".$this->getLibStatut(5);
		}

		$url = dol_buildpath('/lims/samples_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink')
		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label = $langs->trans("ShowSamples");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) $result .= $this->ref;

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('samplesdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("lims@lims");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->trans('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->trans('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		// Module Builder START
		/*$this->lines = array();

		$objectline = new SamplesLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_samples = '.$this->id));

		if (is_numeric($result))
		{
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}*/
		// Module Builder END
		return $this->fetchLines();
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("lims@lims");

		if (empty($conf->global->LIMS_SAMPLES_ADDON)) {
			$conf->global->LIMS_SAMPLES_ADDON = 'mod_samples_standard';
		}

		if (!empty($conf->global->LIMS_SAMPLES_ADDON))
		{
			$mybool = false;

			$file = $conf->global->LIMS_SAMPLES_ADDON.".php";
			$classname = $conf->global->LIMS_SAMPLES_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/lims/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $conf, $langs;

		$result = 0;
		$includedocgeneration = 1;

		$langs->load("lims@lims");

		if (!dol_strlen($modele)) {
			$modele = 'standard_samples';

			if ($this->model_pdf) {
				$modele = $this->model_pdf;
			} elseif (!empty($conf->global->SAMPLES_ADDON_PDF)) {
				$modele = $conf->global->SAMPLES_ADDON_PDF;
			}
		}

		$modelpath = "core/modules/lims/doc/";

		if ($includedocgeneration) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	// COPIED from facture.class.php
	public function addline(
		$fk_product,
		$fk_method,
		$abnormalities,
		$testresult,
		$fk_user,
		$date_start = '',
		$date_end = '',
		$rang = -1,
		$origin = '',
		$origin_id = 0,
		$fk_parent_line = 0) {

		global $langs, $user;
		
		$error=0;
		
		dol_syslog(__METHOD__." with sample id=".$this->id, LOG_DEBUG);

		if ($this->state == self::STATUS_DRAFT)
		{
			// Clean parameters
			if (empty($fk_parent_line) || $fk_parent_line < 0) $fk_parent_line = 0;
			if (empty($fk_prev_id)) $fk_prev_id = 'null';
			
			// Check parameters
			if ($date_start > $date_end) {
				$langs->load("errors");
				$this->error = $langs->trans('ErrorStartDateGreaterEnd');
				return -1;
			}

			$this->db->begin();

			if (!empty($fk_product))
			{
				$product = new Product($this->db);
				$result = $product->fetch($fk_product);
			}

			// Rank to use
			$ranktouse = $rang;
			if ($rang == -1) {
				$rangmax = $this->line_max($fk_parent_line);
				$ranktouse = $rangmax + 1;
			}

			// Insert line necessary??
			//$sampleline = new SamplesLine($this->db);

			// ToDo: variable defined where?
			//$this->line->context = $this->context;
			
			$obj = new Results($this->db);

			/*
			$this->line->fk_parent_line	 = $fk_parent_line;
			$this->line->origin			 = $origin;
			$this->line->origin_id		 = $origin_id;
			*/
			$obj->fk_samples = $this->id;
			$obj->fk_user = $fk_user;
			$obj->fk_method = $fk_method;
			$obj->result = $testresult;
			$obj->start = $date_start;
			$obj->end = $date_end;
			$obj->abnormalities = $abnormalities;
			$obj->rang = $ranktouse;
			$obj->status = self::STATUS_DRAFT;

			//dol_syslog(__METHOD__." obj->create where obj=".var_export($obj, true), LOG_DEBUG);
			
			$res = $obj->create($user); //<0 if KO, Id of created object if OK
			if ($res<0) $error++;
			
			//dol_syslog(__METHOD__." results->create()=".$res, LOG_DEBUG);
			
			//$res = $obj->validate($user); // <=0 if OK, 0=Nothing done, >0 if KO
			//if ($res >0) $error++;
			
			//	$result = $this->line->insert();
			if ($error < 1)
			{
				// Reorder if child line
				if (!empty($fk_parent_line)) $this->line_order(true, 'DESC');
				$this->db->commit();
			}
			else
			{
				$this->error = $this->line->error;
				$this->db->rollback();
				return -2;
			}
		}
		else
		{
			dol_syslog(__METHOD__." status of sample must be Draft to allow use of ->addline()", LOG_ERR);
			return -3;
		}
	}

	public function PrintReport()
	{
		global $langs, $conf;
		
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		// Define output language and generate document
		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
		{
			$outputlangs = $langs;
			$newlang = '';
			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09')) $newlang = GETPOST('lang_id', 'aZ09');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $this->thirdparty->default_lang;
			if (!empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
				$outputlangs->load('products');
			}
			$model = $this->model_pdf;
			$ret = $this->fetch($id); // Reload to get new records

			$result = $this->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
			if ($result < 0) setEventMessages($this->error, $this->errors, 'errors');
		}
	}

	/**
	 *  Check whether a result is conform or nonconform to the method applied.
	 *
	 *  @param	    float 		$testresult		Test result
	 *  @param		int 		$fk_method		Method applied to get test result
	 *  @return     int         				<0 if error, 1 if nonconform, 0 if conform
	 */
	public function checkConformity($testresult, $fk_method)
	{
		if (!empty($fk_method)) {
			$method = new Methods($this->db);
			$result = $method->fetch($fk_method);
			if ($result<1) {
				return -1;
			}
		}
		else {
			return -1;
		}
		
		if ( (!is_null($method->range_lower) && $testresult < $method->range_lower) || (!is_null($method->range_upper) && $testresult > $method->range_upper) ) {
			$conform = 1;	
		} 
		else {
			$conform = 0;
		}

		dol_syslog(__METHOD__.' result='.$testresult.' fk_method='.$fk_method.' conformity='.$conform, LOG_DEBUG);

		return $conform;
	}
}


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class SamplesLine. You can also remove this and generate a CRUD class for lines objects.
 */
class SamplesLine extends CommonObjectLine
{
	// To complete with content of an object SamplesLine
	// We should have a field rowid, fk_samples and position

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 0;

	/////
	// @var string ID to identify managed object
	///
	public $element = 'results';
	
	/////
	 // @var string Name of table without prefix where object is stored
	 ///
	public $table_element = 'lim_results';
	
	public $oldline;

	//! From llx_lims_results
	//! Id sample @var integer NOT NULL
	public $fk_samples;
	//! Id user who did the test @var integer NOT NULL
	public $fk_user;
	//! Id method applied to get test result @var integer NOT NULL
	public $fk_method;
	//! Id product - to be linked with lims_methods
	public $fk_product;
	// public $product_type = 0;	// @var int		Type of the product. 0 for product 1 for service
	//! Result of test @var real NOT NULL
	public $result;
	//! Start time of test @var datetime NOT NULL
	public $date_start;
	//! End time of test @var datetime NOT NULL
	public $date_end;
	//! Abnormalities with this test @var text
	public $abnormalities;
	//! ToDo: Check on possible status
	public $status;
	//! Id parent line  -- NOT USED FOR NOW
	// public $fk_parent_line; 
	
	public $rang = 0;


	public $origin;		// Should be used with templates or copying 
	public $origin_id;

	// From llx_lims_methods
	/////
	public $methods_ref;			// Method ref
	public $methods_label;			// Method label				@ var varchar(255)
	public $methods_standard;		// Method standard			@ var varchar(128)
	public $methods_fk_product;		// Pointer to sales item	@ var int
	public $methods_fk_unit;		// Pointer to Unit			@ var int
	public $methods_accuracy;		// Accuracy					@ var varchar(14)
	public $methods_lower_range;	// Lower range				@ var real
	public $methods_upper_range;	// Upper range				@ var real
	public $methods_resolution;		// Resolution				@ var integer
	
	
	// From llx_product
	/////
	//public $product_ref;	// Product ref
	//public $product_label;	// Product label
	//public $product_desc;	// Description product

	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

	/////
	 //	Load results line from database
	 //
	 //	@param	int			$rowid      id of results line to get
	 //	@return	int			<0 if KO, >0 if OK
	 ///
    public function fetch($rowid)
	{
		$sql = 'SELECT fd.rowid, fd.fk_samples, fd.fk_method, fd.fk_user, fd.abnormalities, fd.result,';
		$sql .= ' fd.start as date_start, fd.end as date_end,';
		$sql .= ' fd.rang,';
		$sql .= ' fd.fk_user_creat, fd.fk_user_modif,';
		$sql .= ' m.ref as methods_ref, m.label as methods_label, m.standard as methods_standard,';
		$sql .= ' m.fk_unit as methods_unit, m.accuracy as methods_accuracy,';
		$sql .= ' m.range_lower as methods_lower_range, m.range_upper as methods_upper_range, m.resolution as methods_resolution';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_results as fd';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'lims_methods as m ON fd.fk_method = m.rowid';
		$sql .= ' WHERE fd.rowid = '.$rowid;

		$result = $this->db->query($sql);
		if ($result)
		{
			$objp = $this->db->fetch_object($result);

			$this->rowid				 = $objp->rowid;
			$this->id					 = $objp->rowid;
			$this->fk_samples			 = $objp->fk_samples;
			$this->fk_method			 = $objp->fk_method;
			$this->fk_user				 = $objp->fk_user;
			//$this->fk_parent_line		 = $objp->fk_parent_line;
			$this->abnormalities		 = $objp->abnormalities;
			$this->result				 = $objp->result;

			$this->date_start			 = $this->db->jdate($objp->date_start);
			$this->date_end				 = $this->db->jdate($objp->date_end);
			$this->rang					 = $objp->rang;
			
			$this->methods_ref			 = $objp->methods_ref;
			$this->methods_label		 = $objp->methods_label;
			$this->methods_standard		 = $objp->methods_standard;
			$this->methods_fk_unit		 = $objp->methods_fk_unit;
			$this->methods_accuracy		 = $objp->methods_accuracy;
			$this->methods_lower_range	 = $objp->methods_lower_range;
			$this->methods_upper_range	 = $objp->methods_upper_range;
			$this->methods_resolution	 = $objp->methods_resolution;

			//$this->product_type			 = $objp->product_type;
			//$this->methods_fk_product	 = $objp->methods_fk_product;		// Pointer to sales item	@ var int
			//$this->product_ref 		 = $objp->product_ref;
			//$this->product_label		 = $objp->product_libelle;
			//$this->product_desc		 = $objp->product_desc;
			
			$this->fk_user_modif		 = $objp->fk_user_modif;
			$this->fk_user_creat		 = $objp->fk_user_creat;

			$this->db->free($result);

			return 1;
		}
		else
		{
		    $this->error = $this->db->lasterror();
			return -1;
		}
	}
	
	
	/*   COPIED class FactureLigne extends CommonInvoiceLine
	**************************************************************************

	/////
	 //	Insert line into database
	 //
	 //	@param      int		$notrigger		                 1 no triggers
	 
	 //	@return		int						                 <0 if KO, >0 if OK
	 */
    public function insert($notrigger = 0)
	{
		global $langs, $user, $conf;

		$error = 0;

        dol_syslog(__METHOD__." rang=".$this->rang, LOG_DEBUG);

		// Clean parameters
		if (empty($this->line->rang)) $this->line->rang = 0;
		if (empty($this->line->fk_parent_line)) $this->line->fk_parent_line = 0;
		if (empty($this->line->$abnormalities)) $this->line->$abnormalities =0;
		
		// Check parameters
		if (!empty($this->line->fk_product))
		{
			// Check product exists
			$result = Product::isExistingObject('product', $this->line->fk_product);
			if ($result <= 0)
			{
				$this->error = 'ErrorProductIdDoesNotExists';
				dol_syslog(get_class($this)."::insert Error ".$this->error, LOG_ERR);
				return -1;
			}
		}

		//$this->db->begin();

		// Use Results class to store new line to database
		$results = new Results($this->db);
		$res = $results->create($user, true); //disables triggers
		
		dol_syslog(__METHOD__." results->create()=".$res, LOG_DEBUG);
		
		$results->$fk_samples = $this->line->fk_samples;
		$results->fk_user = $this->line->fk_user;
		$results->fk_method = $this->line->fk_method;
		$results->result = $this->line->result;
		$results->start = $this->line->start;
		$results->end = $this->line->start;
		$results->abnormalities = $this->line->abnormalities;
		$results->rang = $this->line->rang;
		
		$res = $results->update($user, true); //disables triggers
		
		dol_syslog(__METHOD__." results->update()=".$res, LOG_DEBUG);
		/*
		$resql = $this->db->query($sql);
		if ($resql)
		{
			$this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.'facturedet');
			$this->rowid = $this->id; // For backward compatibility

            if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
            {
            	$result = $this->insertExtraFields();
            	if ($result < 0)
            	{
            		$error++;
            	}
            }

			// Triggers not handled yet
			
			if (!$notrigger)
			{
                // Call trigger
                $result = $this->call_trigger('LINEBILL_INSERT', $user);
                if ($result < 0)
                {
					$this->db->rollback();
					return -2;
				}
                // End call triggers
			}
			
			$this->db->commit();
			return $this->id;
		}
		else
		{
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -2;
		}*/
	}

	 /*
	 //	Update line into database
	 //
	 //	@param		User	$user		User object
	 //	@param		int		$notrigger	Disable triggers
	 //	@return		int					<0 if KO, >0 if OK
	 */

	 public function update($user = '', $notrigger = 0)
	{
		global $user, $conf;

		$error = 0;

		// Clean parameters		
		if (empty($this->fk_parent_line))	$this->fk_parent_line	 = 0;

		dol_syslog(__METHOD__
		.' fkuser='.$this->fk_user 
		.' result='.$this->testresult 
		.' abnormalities='.$this->abnormalities, LOG_DEBUG);
			
		$resql = $this->db->query($sql);
		
		if ($resql)
		{
        	if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // avoid conflicts if trigger used
        	{
        		$this->id = $this->rowid;
        		$result = $this->insertExtraFields();
        		if ($result < 0)
        		{
        			$error++;
        		}
        	}

			if (!$error && !$notrigger)
			{
                // Call trigger
                $result = $this->call_trigger('LINEBILL_UPDATE', $user);
                if ($result < 0)
 				{
					$this->db->rollback();
					return -2;
				}
                // End call triggers
			}
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error = $this->db->error();
			$this->db->rollback();
			return -2;
		}
	}

	/*  Function not used, instead ::deleteLineCommon is called
	// 	Delete line in database
	//  TODO Add param User $user and notrigger (see skeleton)
	//
	//	@return	    int		           <0 if KO, >0 if OK
	*/
/*    public function delete()
	{
		global $user;

		$this->db->begin();

		// Call trigger
		$result = $this->call_trigger('LINEBILL_DELETE', $user);
		if ($result < 0)
		{
			$this->db->rollback();
			return -1;
		}
		// End call triggers


		$sql = "DELETE FROM ".MAIN_DB_PREFIX."facturedet WHERE rowid = ".$this->rowid;
		dol_syslog(get_class($this)."::delete", LOG_DEBUG);
		if ($this->db->query($sql))
		{
			$this->db->commit();
			return 1;
		}
		else
		{
			$this->error = $this->db->error()." sql=".$sql;
			$this->db->rollback();
			return -1;
		}
	}*/
	// COPIED from class Samples
//   $result = $objectline->fetchAll('ASC', 'rang', 0, 0, array('customsql'=>'fk_samples = '.$this->id));
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;
		
		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();
		
		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.'lims_'.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				}
				elseif (strpos($key, 'date') !== false) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				}
				elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				}
				else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}
		if (!empty($sortfield)) {
		//	$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
            $i = 0;
			while ($i < min($limit, $num))
			{
			    $obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}
}