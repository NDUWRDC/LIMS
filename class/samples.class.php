<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
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
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for Samples
 */
class Samples extends CommonObject
{
	/**
	 * @var string ID to identify managed object
	 */
	public $element = 'samples';

	/**
	 * @var string Name of table without prefix where object is stored
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
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
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
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
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
		'fk_soc' => array('type'=>'integer:Societe:societe/class/societe.class.php:1:status=1 AND entity IN (__SHARED_ENTITIES__)', 'label'=>'Customer', 'enabled'=>1, 'position'=>20, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'help'=>"Link to Third-party",),
		'fk_propal' => array('type'=>'integer:Propal:comm/propal/class/propal.class.php', 'label'=>'Customer Proposal', 'enabled'=>1, 'position'=>30, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'help'=>"Link to Proposal",),
		'fk_facture' => array('type'=>'integer:Facture:compta/facture/class/facture.class.php', 'label'=>'Customer Invoice', 'enabled'=>1, 'position'=>40, 'notnull'=>-1, 'visible'=>1, 'index'=>1, 'help'=>"Link to Customer Invoice",),
		'fk_socpeople' => array('type'=>'integer:Contacts:Contacts/class/contact.class.php:1', 'label'=>'Client sample taker', 'enabled'=>1, 'position'=>50, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'help'=>"If client did the sampling",),
		'fk_user' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'Laboratory Sample taker', 'enabled'=>1, 'position'=>60, 'notnull'=>-1, 'visible'=>3, 'index'=>1, 'help'=>"Own lab techician",),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>1, 'position'=>70, 'notnull'=>1, 'visible'=>1, 'searchall'=>1, 'help'=>"Sample label",),
		'volume' => array('type'=>'real', 'label'=>'Volume [liter]', 'enabled'=>1, 'position'=>80, 'notnull'=>1, 'visible'=>3, 'help'=>"Total volume in liters of all containers and bottles",),
		'qty' => array('type'=>'integer', 'label'=>'Qty', 'enabled'=>1, 'position'=>90, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'isameasure'=>'1', 'help'=>"Amount of containers or bottles",),
		'date' => array('type'=>'datetime', 'label'=>'Sampling date and time', 'enabled'=>1, 'position'=>100, 'notnull'=>1, 'visible'=>1, 'help'=>"When was the sample taken",),
		'place' => array('type'=>'varchar(128)', 'label'=>'Sampling place', 'enabled'=>1, 'position'=>110, 'notnull'=>1, 'visible'=>3, 'help'=>"Location of water source",),
		'place_lon' => array('type'=>'real', 'label'=>'Sampling place GPS longitude', 'enabled'=>1, 'position'=>120, 'notnull'=>-1, 'visible'=>3, 'help'=>"X-coordinate (WGS84), e.g. '0.5959513'",),
		'place_lat' => array('type'=>'real', 'label'=>'Sampling place GPS latitude', 'enabled'=>1, 'position'=>130, 'notnull'=>-1, 'visible'=>3, 'help'=>"Y-coordinate (WGS84), e.g. '32.4569526'",),
		'date_arrival' => array('type'=>'datetime', 'label'=>'Arrival date and time', 'enabled'=>1, 'position'=>140, 'notnull'=>1, 'visible'=>1, 'help'=>"Date when the sample was received",),
		'fk_project' => array('type'=>'integer:Project:projet/class/project.class.php:1', 'label'=>'Project', 'enabled'=>1, 'position'=>150, 'notnull'=>-1, 'visible'=>-1, 'index'=>1, 'help'=>"If many samples belong to the same project",),
		'description' => array('type'=>'text', 'label'=>'Description', 'enabled'=>1, 'position'=>160, 'notnull'=>1, 'visible'=>3,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>1, 'position'=>170, 'notnull'=>0, 'visible'=>-1,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>1, 'position'=>180, 'notnull'=>0, 'visible'=>-1,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>1, 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>1, 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>1, 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>1, 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>1, 'position'=>1010, 'notnull'=>-1, 'visible'=>-1,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>1, 'position'=>1011, 'notnull'=>1, 'visible'=>1, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Draft', '1'=>'Validated', '9'=>'Canceled'),),
	);
	public $rowid;
	public $ref;
	public $fk_soc;
	public $fk_propal;
	public $fk_facture;
	public $fk_socpeople;
	public $fk_user;
	public $label;
	public $volume;
	public $qty;
	public $date;
	public $place;
	public $place_lon;
	public $place_lat;
	public $date_arrival;
	public $fk_project;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
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
	 * @var int    Field with ID of parent key if this field has a parent
	 */
	public $fk_element = 'fk_samples';

	/**
	 * @var int    Name of subtable class that manage subtable lines
	 */
	public $class_element_line = 'results';

	/**
	 * @var array	List of child tables. To test if we can delete object.
	 */
	protected $childtables=array();

	/**
	 * @var array	List of child tables. To know object to delete on cascade.
	 */
	protected $childtablesoncascade=array('lims_results');

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
				if (is_array($val['arrayofkeyval']))
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

	    // Reset some properties
	    unset($object->id);
	    unset($object->fk_user_creat);
	    unset($object->import_key);


	    // Clear fields
	    $object->ref = empty($this->fields['ref']['default']) ? "copy_of_".$object->ref : $this->fields['ref']['default'];
	    $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
	    $object->status = self::STATUS_DRAFT;
	    // ...
	    // Clear extrafields that are unique
	    if (is_array($object->array_options) && count($object->array_options) > 0)
	    {
	    	$extrafields->fetch_name_optionals_label($this->table_element);
	    	foreach ($object->array_options as $key => $option)
	    	{
	    		$shortkey = preg_replace('/options_/', '', $key);
	    		if (!empty($extrafields->attributes[$this->element]['unique'][$shortkey]))
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
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
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
			$sql .= $this->db->order($sortfield, $sortorder);
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

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->samples->create))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->samples->samples_advance->validate))))
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
		}
		else
		{
			$num = $this->ref;
		}
		$this->newref = $num;

		if (! empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (! empty($this->fields['date_validation'])) $sql .= ", date_validation = '".$this->db->idate($now)."',";
			if (! empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
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
		}
		else
		{
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

        $label = '<u>'.$langs->trans("Samples").'</u>';
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
        }
        else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

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
					}
					else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				}
				else {
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
	 *  Return label of the status
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
			//$langs->load("lims");
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
		}
		else
		{
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
	    $this->lines = array();

	    $objectline = new SamplesLine($this->db);
	    $result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_samples = '.$this->id));

	    if (is_numeric($result))
	    {
	        $this->error = $this->error;
	        $this->errors = $this->errors;
	        return $result;
	    }
	    else
	    {
	        $this->lines = $result;
	        return $this->lines;
	    }
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("lims@samples");

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
				}
				else
				{
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		}
		else
		{
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
			$modele = 'standard';

			if ($this->modelpdf) {
				$modele = $this->modelpdf;
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
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	//public function doScheduledJob($param1, $param2, ...)
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
}

/**
 * Class SamplesLine. You can also remove this and generate a CRUD class for lines objects.
 */
class SamplesLine //extends CommonInvoiceLine 
{
	// Inherited from CommonInvoiceLine:
	// public $qty;
	// public $subprice 			// @var float	Unit price before taxes
	// public $product_type = 0;	// @var int		Type of the product. 0 for product 1 for service
	// public $fk_product;			// @var int		Id of corresponding product
	// public $vat_src_code;		// @var string	VAT code
	// public $tva_tx;	 			// @var float	VAT %
	// public $localtax1_tx;		// @var float	Local tax 1 %
	// public $localtax2_tx;		// @var float	Local tax 2 %
	// public $remise_percent;		// @var float	Percent of discount
	// public $total_ht;			// @var float	Total amount before taxes
	// public $total_tva;				// @var float	Total VAT amount
	// public $total_localtax1;		// @var float	Total local tax 1 amount
	// public $total_localtax2;		// @var float	Total local tax 2 amount
	// public $total_ttc;			// @var float	Total amount with taxes
	// public $info_bits = 0;		// @var int	List of cumulative options:

	
	/////
	// @var string ID to identify managed object
	///
	public $element = 'results';
	
	/////
	 // @var string Name of table without prefix where object is stored
	 ///
	public $table_element = 'results';
	
	public $oldline;

	//! From llx_lims_results
	//! Id sample
	public $fk_samples;
	
	//! Id parent line  -- NOT USED FOR NOW
	// public $fk_parent_line; 
	
	public $label;
	//! Description ligne
	public $desc;

	public $rang = 0;

	public $fk_product;
    
	/*   COPIED class FactureLigne extends CommonInvoiceLine
	**************************************************************************


	public $origin;
	public $origin_id;

	public $fk_code_ventilation = 0;

	public $date_start;
	public $date_end;

	// From llx_product
	/////
	 // @deprecated
	 // @see $product_ref
	 ///
	public $ref; // Product ref (deprecated)
	public $product_ref; // Product ref
	/////
	 // @deprecated
	 // @see $product_label
	 ///
	public $libelle; // Product label (deprecated)
	public $product_label; // Product label
	public $product_desc; // Description produit

	public $skip_update_total; // Skip update price total for special lines

	/////
	 // @var int Situation advance percentage
	 ///
	public $situation_percent;

	/////
	 // @var int Previous situation line id reference
	 ///
	public $fk_prev_id;

	// Multicurrency
	public $fk_multicurrency;
	public $multicurrency_code;
	public $multicurrency_subprice;
	public $multicurrency_total_ht;
	public $multicurrency_total_tva;
	public $multicurrency_total_ttc;

	/////
	 //	Load invoice line from database
	 //
	 //	@param	int		$rowid      id of invoice line to get
	 //	@return	int					<0 if KO, >0 if OK
	 ///
    public function fetch($rowid)
	{
		$sql = 'SELECT fd.rowid, fd.fk_facture, fd.fk_parent_line, fd.fk_product, fd.product_type, fd.label as custom_label, fd.description, fd.price, fd.qty, fd.vat_src_code, fd.tva_tx,';
		$sql .= ' fd.localtax1_tx, fd. localtax2_tx, fd.remise, fd.remise_percent, fd.fk_remise_except, fd.subprice,';
		$sql .= ' fd.date_start as date_start, fd.date_end as date_end, fd.fk_product_fournisseur_price as fk_fournprice, fd.buy_price_ht as pa_ht,';
		$sql .= ' fd.info_bits, fd.special_code, fd.total_ht, fd.total_tva, fd.total_ttc, fd.total_localtax1, fd.total_localtax2, fd.rang,';
		$sql .= ' fd.fk_code_ventilation,';
		$sql .= ' fd.fk_unit, fd.fk_user_author, fd.fk_user_modif,';
		$sql .= ' fd.situation_percent, fd.fk_prev_id,';
		$sql .= ' fd.multicurrency_subprice,';
		$sql .= ' fd.multicurrency_total_ht,';
		$sql .= ' fd.multicurrency_total_tva,';
		$sql .= ' fd.multicurrency_total_ttc,';
		$sql .= ' p.ref as product_ref, p.label as product_libelle, p.description as product_desc';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'facturedet as fd';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'product as p ON fd.fk_product = p.rowid';
		$sql .= ' WHERE fd.rowid = '.$rowid;

		$result = $this->db->query($sql);
		if ($result)
		{
			$objp = $this->db->fetch_object($result);

			$this->rowid = $objp->rowid;
			$this->id = $objp->rowid;
			$this->fk_facture = $objp->fk_facture;
			$this->fk_parent_line = $objp->fk_parent_line;
			$this->label				= $objp->custom_label;
			$this->desc					= $objp->description;
			$this->qty = $objp->qty;
			$this->subprice = $objp->subprice;
			$this->vat_src_code = $objp->vat_src_code;
			$this->tva_tx = $objp->tva_tx;
			$this->localtax1_tx			= $objp->localtax1_tx;
			$this->localtax2_tx			= $objp->localtax2_tx;
			$this->remise_percent = $objp->remise_percent;
			$this->fk_remise_except = $objp->fk_remise_except;
			$this->fk_product			= $objp->fk_product;
			$this->product_type = $objp->product_type;
			$this->date_start			= $this->db->jdate($objp->date_start);
			$this->date_end				= $this->db->jdate($objp->date_end);
			$this->info_bits			= $objp->info_bits;
			$this->tva_npr = ($objp->info_bits & 1 == 1) ? 1 : 0;
			$this->special_code = $objp->special_code;
			$this->total_ht				= $objp->total_ht;
			$this->total_tva			= $objp->total_tva;
			$this->total_localtax1		= $objp->total_localtax1;
			$this->total_localtax2		= $objp->total_localtax2;
			$this->total_ttc			= $objp->total_ttc;
			$this->fk_code_ventilation = $objp->fk_code_ventilation;
			$this->rang					= $objp->rang;
			$this->fk_fournprice = $objp->fk_fournprice;
			$marginInfos				= getMarginInfos($objp->subprice, $objp->remise_percent, $objp->tva_tx, $objp->localtax1_tx, $objp->localtax2_tx, $this->fk_fournprice, $objp->pa_ht);
			$this->pa_ht				= $marginInfos[0];
			$this->marge_tx				= $marginInfos[1];
			$this->marque_tx			= $marginInfos[2];

			$this->ref = $objp->product_ref; // deprecated
			$this->product_ref = $objp->product_ref;
			$this->libelle				= $objp->product_libelle; // deprecated
			$this->product_label		= $objp->product_libelle;
			$this->product_desc			= $objp->product_desc;
			$this->fk_unit				= $objp->fk_unit;
			$this->fk_user_modif		= $objp->fk_user_modif;
			$this->fk_user_author = $objp->fk_user_author;

			$this->situation_percent    = $objp->situation_percent;
			$this->fk_prev_id           = $objp->fk_prev_id;

			$this->multicurrency_subprice = $objp->multicurrency_subprice;
			$this->multicurrency_total_ht = $objp->multicurrency_total_ht;
			$this->multicurrency_total_tva = $objp->multicurrency_total_tva;
			$this->multicurrency_total_ttc = $objp->multicurrency_total_ttc;

			$this->db->free($result);

			return 1;
		}
		else
		{
		    $this->error = $this->db->lasterror();
			return -1;
		}
	}

	/////
	 //	Insert line into database
	 //
	 //	@param      int		$notrigger		                 1 no triggers
	 //  @param      int     $noerrorifdiscountalreadylinked  1=Do not make error if lines is linked to a discount and discount already linked to another
	 //	@return		int						                 <0 if KO, >0 if OK
	 ///
    public function insert($notrigger = 0, $noerrorifdiscountalreadylinked = 0)
	{
		global $langs, $user, $conf;

		$error = 0;

        $pa_ht_isemptystring = (empty($this->pa_ht) && $this->pa_ht == ''); // If true, we can use a default value. If this->pa_ht = '0', we must use '0'.

        dol_syslog(get_class($this)."::insert rang=".$this->rang, LOG_DEBUG);

		// Clean parameters
		$this->desc = trim($this->desc);
		if (empty($this->tva_tx)) $this->tva_tx = 0;
		if (empty($this->localtax1_tx)) $this->localtax1_tx = 0;
		if (empty($this->localtax2_tx)) $this->localtax2_tx = 0;
		if (empty($this->localtax1_type)) $this->localtax1_type = 0;
		if (empty($this->localtax2_type)) $this->localtax2_type = 0;
		if (empty($this->total_localtax1)) $this->total_localtax1 = 0;
		if (empty($this->total_localtax2)) $this->total_localtax2 = 0;
		if (empty($this->rang)) $this->rang = 0;
		if (empty($this->remise_percent)) $this->remise_percent = 0;
		if (empty($this->info_bits)) $this->info_bits = 0;
		if (empty($this->subprice)) $this->subprice = 0;
		if (empty($this->special_code)) $this->special_code = 0;
		if (empty($this->fk_parent_line)) $this->fk_parent_line = 0;
		if (empty($this->fk_prev_id)) $this->fk_prev_id = 0;
		if (!isset($this->situation_percent) || $this->situation_percent > 100 || (string) $this->situation_percent == '') $this->situation_percent = 100;

		if (empty($this->pa_ht)) $this->pa_ht = 0;
		if (empty($this->multicurrency_subprice)) $this->multicurrency_subprice = 0;
		if (empty($this->multicurrency_total_ht)) $this->multicurrency_total_ht = 0;
		if (empty($this->multicurrency_total_tva)) $this->multicurrency_total_tva = 0;
		if (empty($this->multicurrency_total_ttc)) $this->multicurrency_total_ttc = 0;

		// if buy price not defined, define buyprice as configured in margin admin
		if ($this->pa_ht == 0 && $pa_ht_isemptystring)
		{
			if (($result = $this->defineBuyPrice($this->subprice, $this->remise_percent, $this->fk_product)) < 0)
			{
				return $result;
			}
			else
			{
				$this->pa_ht = $result;
			}
		}

		// Check parameters
		if ($this->product_type < 0)
		{
			$this->error = 'ErrorProductTypeMustBe0orMore';
			return -1;
		}
		if (!empty($this->fk_product))
		{
			// Check product exists
			$result = Product::isExistingObject('product', $this->fk_product);
			if ($result <= 0)
			{
				$this->error = 'ErrorProductIdDoesNotExists';
				dol_syslog(get_class($this)."::insert Error ".$this->error, LOG_ERR);
				return -1;
			}
		}

		$this->db->begin();

		// Insertion dans base de la ligne
		$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'facturedet';
		$sql .= ' (fk_facture, fk_parent_line, label, description, qty,';
		$sql .= ' vat_src_code, tva_tx, localtax1_tx, localtax2_tx, localtax1_type, localtax2_type,';
		$sql .= ' fk_product, product_type, remise_percent, subprice, fk_remise_except,';
		$sql .= ' date_start, date_end, fk_code_ventilation, ';
		$sql .= ' rang, special_code, fk_product_fournisseur_price, buy_price_ht,';
		$sql .= ' info_bits, total_ht, total_tva, total_ttc, total_localtax1, total_localtax2,';
		$sql .= ' situation_percent, fk_prev_id,';
		$sql .= ' fk_unit, fk_user_author, fk_user_modif,';
		$sql .= ' fk_multicurrency, multicurrency_code, multicurrency_subprice, multicurrency_total_ht, multicurrency_total_tva, multicurrency_total_ttc';
		$sql .= ')';
		$sql .= " VALUES (".$this->fk_facture.",";
		$sql .= " ".($this->fk_parent_line > 0 ? $this->fk_parent_line : "null").",";
		$sql .= " ".(!empty($this->label) ? "'".$this->db->escape($this->label)."'" : "null").",";
		$sql .= " '".$this->db->escape($this->desc)."',";
		$sql .= " ".price2num($this->qty).",";
        $sql .= " ".(empty($this->vat_src_code) ? "''" : "'".$this->db->escape($this->vat_src_code)."'").",";
		$sql .= " ".price2num($this->tva_tx).",";
		$sql .= " ".price2num($this->localtax1_tx).",";
		$sql .= " ".price2num($this->localtax2_tx).",";
		$sql .= " '".$this->db->escape($this->localtax1_type)."',";
		$sql .= " '".$this->db->escape($this->localtax2_type)."',";
		$sql .= ' '.(!empty($this->fk_product) ? $this->fk_product : "null").',';
		$sql .= " ".((int) $this->product_type).",";
		$sql .= " ".price2num($this->remise_percent).",";
		$sql .= " ".price2num($this->subprice).",";
		$sql .= ' '.(!empty($this->fk_remise_except) ? $this->fk_remise_except : "null").',';
		$sql .= " ".(!empty($this->date_start) ? "'".$this->db->idate($this->date_start)."'" : "null").",";
		$sql .= " ".(!empty($this->date_end) ? "'".$this->db->idate($this->date_end)."'" : "null").",";
		$sql .= ' '.$this->fk_code_ventilation.',';
		$sql .= ' '.$this->rang.',';
		$sql .= ' '.$this->special_code.',';
		$sql .= ' '.(!empty($this->fk_fournprice) ? $this->fk_fournprice : "null").',';
		$sql .= ' '.price2num($this->pa_ht).',';
		$sql .= " '".$this->db->escape($this->info_bits)."',";
		$sql .= " ".price2num($this->total_ht).",";
		$sql .= " ".price2num($this->total_tva).",";
		$sql .= " ".price2num($this->total_ttc).",";
		$sql .= " ".price2num($this->total_localtax1).",";
		$sql .= " ".price2num($this->total_localtax2);
		$sql .= ", ".$this->situation_percent;
		$sql .= ", ".(!empty($this->fk_prev_id) ? $this->fk_prev_id : "null");
		$sql .= ", ".(!$this->fk_unit ? 'NULL' : $this->fk_unit);
		$sql .= ", ".$user->id;
		$sql .= ", ".$user->id;
		$sql .= ", ".(int) $this->fk_multicurrency;
		$sql .= ", '".$this->db->escape($this->multicurrency_code)."'";
		$sql .= ", ".price2num($this->multicurrency_subprice);
		$sql .= ", ".price2num($this->multicurrency_total_ht);
		$sql .= ", ".price2num($this->multicurrency_total_tva);
		$sql .= ", ".price2num($this->multicurrency_total_ttc);
		$sql .= ')';

		dol_syslog(get_class($this)."::insert", LOG_DEBUG);
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

			// Si fk_remise_except defini, on lie la remise a la facture
			// ce qui la flague comme "consommee".
			if ($this->fk_remise_except)
			{
				$discount = new DiscountAbsolute($this->db);
				$result = $discount->fetch($this->fk_remise_except);
				if ($result >= 0)
				{
					// Check if discount was found
					if ($result > 0)
					{
					    // Check if discount not already affected to another invoice
						if ($discount->fk_facture_line > 0)
						{
						    if (empty($noerrorifdiscountalreadylinked))
						    {
    							$this->error = $langs->trans("ErrorDiscountAlreadyUsed", $discount->id);
    							dol_syslog(get_class($this)."::insert Error ".$this->error, LOG_ERR);
    							$this->db->rollback();
    							return -3;
						    }
						}
						else
						{
							$result = $discount->link_to_invoice($this->rowid, 0);
							if ($result < 0)
							{
								$this->error = $discount->error;
								dol_syslog(get_class($this)."::insert Error ".$this->error, LOG_ERR);
								$this->db->rollback();
								return -3;
							}
						}
					}
					else
					{
						$this->error = $langs->trans("ErrorADiscountThatHasBeenRemovedIsIncluded");
						dol_syslog(get_class($this)."::insert Error ".$this->error, LOG_ERR);
						$this->db->rollback();
						return -3;
					}
				}
				else
				{
					$this->error = $discount->error;
					dol_syslog(get_class($this)."::insert Error ".$this->error, LOG_ERR);
					$this->db->rollback();
					return -3;
				}
			}

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
		}
	}

	/////
	 //	Update line into database
	 //
	 //	@param		User	$user		User object
	 //	@param		int		$notrigger	Disable triggers
	 //	@return		int					<0 if KO, >0 if OK
	 ///
    public function update($user = '', $notrigger = 0)
	{
		global $user, $conf;

		$error = 0;

		$pa_ht_isemptystring = (empty($this->pa_ht) && $this->pa_ht == ''); // If true, we can use a default value. If this->pa_ht = '0', we must use '0'.

		// Clean parameters
		$this->desc = trim($this->desc);
		if (empty($this->tva_tx)) $this->tva_tx = 0;
		if (empty($this->localtax1_tx)) $this->localtax1_tx = 0;
		if (empty($this->localtax2_tx)) $this->localtax2_tx = 0;
		if (empty($this->localtax1_type)) $this->localtax1_type = 0;
		if (empty($this->localtax2_type)) $this->localtax2_type = 0;
		if (empty($this->total_localtax1)) $this->total_localtax1 = 0;
		if (empty($this->total_localtax2)) $this->total_localtax2 = 0;
		if (empty($this->remise_percent)) $this->remise_percent = 0;
		if (empty($this->info_bits)) $this->info_bits = 0;
		if (empty($this->special_code)) $this->special_code = 0;
		if (empty($this->product_type)) $this->product_type = 0;
		if (empty($this->fk_parent_line)) $this->fk_parent_line = 0;
		if (!isset($this->situation_percent) || $this->situation_percent > 100 || (string) $this->situation_percent == '') $this->situation_percent = 100;
		if (empty($this->pa_ht)) $this->pa_ht = 0;

		if (empty($this->multicurrency_subprice)) $this->multicurrency_subprice = 0;
		if (empty($this->multicurrency_total_ht)) $this->multicurrency_total_ht = 0;
		if (empty($this->multicurrency_total_tva)) $this->multicurrency_total_tva = 0;
		if (empty($this->multicurrency_total_ttc)) $this->multicurrency_total_ttc = 0;

		// Check parameters
		if ($this->product_type < 0) return -1;

		// if buy price not defined, define buyprice as configured in margin admin
		if ($this->pa_ht == 0 && $pa_ht_isemptystring)
		{
			if (($result = $this->defineBuyPrice($this->subprice, $this->remise_percent, $this->fk_product)) < 0)
			{
				return $result;
			}
			else
			{
				$this->pa_ht = $result;
			}
		}

		$this->db->begin();

        // Mise a jour ligne en base
        $sql = "UPDATE ".MAIN_DB_PREFIX."facturedet SET";
        $sql .= " description='".$this->db->escape($this->desc)."'";
        $sql .= ", label=".(!empty($this->label) ? "'".$this->db->escape($this->label)."'" : "null");
        $sql .= ", subprice=".price2num($this->subprice)."";
        $sql .= ", remise_percent=".price2num($this->remise_percent)."";
        if ($this->fk_remise_except) $sql .= ", fk_remise_except=".$this->fk_remise_except;
        else $sql .= ", fk_remise_except=null";
		$sql .= ", vat_src_code = '".(empty($this->vat_src_code) ? '' : $this->db->escape($this->vat_src_code))."'";
        $sql .= ", tva_tx=".price2num($this->tva_tx)."";
        $sql .= ", localtax1_tx=".price2num($this->localtax1_tx)."";
        $sql .= ", localtax2_tx=".price2num($this->localtax2_tx)."";
		$sql .= ", localtax1_type='".$this->db->escape($this->localtax1_type)."'";
		$sql .= ", localtax2_type='".$this->db->escape($this->localtax2_type)."'";
        $sql .= ", qty=".price2num($this->qty);
        $sql .= ", date_start=".(!empty($this->date_start) ? "'".$this->db->idate($this->date_start)."'" : "null");
        $sql .= ", date_end=".(!empty($this->date_end) ? "'".$this->db->idate($this->date_end)."'" : "null");
        $sql .= ", product_type=".$this->product_type;
        $sql .= ", info_bits='".$this->db->escape($this->info_bits)."'";
        $sql .= ", special_code='".$this->db->escape($this->special_code)."'";
        if (empty($this->skip_update_total))
        {
        	$sql .= ", total_ht=".price2num($this->total_ht);
        	$sql .= ", total_tva=".price2num($this->total_tva);
        	$sql .= ", total_ttc=".price2num($this->total_ttc);
        	$sql .= ", total_localtax1=".price2num($this->total_localtax1);
        	$sql .= ", total_localtax2=".price2num($this->total_localtax2);
        }
		$sql .= ", fk_product_fournisseur_price=".(!empty($this->fk_fournprice) ? "'".$this->db->escape($this->fk_fournprice)."'" : "null");
		$sql .= ", buy_price_ht='".price2num($this->pa_ht)."'";
		$sql .= ", fk_parent_line=".($this->fk_parent_line > 0 ? $this->fk_parent_line : "null");
		if (!empty($this->rang)) $sql .= ", rang=".$this->rang;
		$sql .= ", situation_percent=".$this->situation_percent;
		$sql .= ", fk_unit=".(!$this->fk_unit ? 'NULL' : $this->fk_unit);
		$sql .= ", fk_user_modif =".$user->id;

		// Multicurrency
		$sql .= ", multicurrency_subprice=".price2num($this->multicurrency_subprice)."";
        $sql .= ", multicurrency_total_ht=".price2num($this->multicurrency_total_ht)."";
        $sql .= ", multicurrency_total_tva=".price2num($this->multicurrency_total_tva)."";
        $sql .= ", multicurrency_total_ttc=".price2num($this->multicurrency_total_ttc)."";

		$sql .= " WHERE rowid = ".$this->rowid;

		dol_syslog(get_class($this)."::update", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
        	if (empty($conf->global->MAIN_EXTRAFIELDS_DISABLED)) // For avoid conflicts if trigger used
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

	/////
	 // 	Delete line in database
	 //  TODO Add param User $user and notrigger (see skeleton)
     //
	 //	@return	    int		           <0 if KO, >0 if OK
	 ///
    public function delete()
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
	}

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/////
     //	Update DB line fields total_xxx
	 //	Used by migration
	 //
	 //	@return		int		<0 if KO, >0 if OK
	 ///
    public function update_total()
	{
        // phpcs:enable
		$this->db->begin();
		dol_syslog(get_class($this)."::update_total", LOG_DEBUG);

		// Clean parameters
		if (empty($this->total_localtax1)) $this->total_localtax1 = 0;
		if (empty($this->total_localtax2)) $this->total_localtax2 = 0;

		// Mise a jour ligne en base
		$sql = "UPDATE ".MAIN_DB_PREFIX."facturedet SET";
		$sql .= " total_ht=".price2num($this->total_ht)."";
		$sql .= ",total_tva=".price2num($this->total_tva)."";
		$sql .= ",total_localtax1=".price2num($this->total_localtax1)."";
		$sql .= ",total_localtax2=".price2num($this->total_localtax2)."";
		$sql .= ",total_ttc=".price2num($this->total_ttc)."";
		$sql .= " WHERE rowid = ".$this->rowid;

		dol_syslog(get_class($this)."::update_total", LOG_DEBUG);

		$resql = $this->db->query($sql);
		if ($resql)
		{
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

    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	//
	 // Returns situation_percent of the previous line.
	 // Warning: If invoice is a replacement invoice, this->fk_prev_id is id of the replaced line.
	 //
	 // @param  int     $invoiceid      Invoice id
	 // @param  bool    $include_credit_note		Include credit note or not
	 // @return int                     >= 0
	 ///
	 
    public function get_prev_progress($invoiceid, $include_credit_note = true)
	{
        // phpcs:enable
		global $invoicecache;
		if (is_null($this->fk_prev_id) || empty($this->fk_prev_id) || $this->fk_prev_id == "") {
			return 0;
		} else {
		    // If invoice is not a situation invoice, this->fk_prev_id is used for something else
			if (!isset($invoicecache[$invoiceid])) {
				$invoicecache[$invoiceid] = new Facture($this->db);
				$invoicecache[$invoiceid]->fetch($invoiceid);
			}
			if ($invoicecache[$invoiceid]->type != Facture::TYPE_SITUATION) return 0;

			$sql = 'SELECT situation_percent FROM '.MAIN_DB_PREFIX.'facturedet WHERE rowid='.$this->fk_prev_id;
			$resql = $this->db->query($sql);
			if ($resql && $resql->num_rows > 0) {
				$res = $this->db->fetch_array($resql);

				$returnPercent = floatval($res['situation_percent']);

				if ($include_credit_note) {
				    $sql = 'SELECT fd.situation_percent FROM '.MAIN_DB_PREFIX.'facturedet fd';
				    $sql .= ' JOIN '.MAIN_DB_PREFIX.'facture f ON (f.rowid = fd.fk_facture) ';
				    $sql .= ' WHERE fd.fk_prev_id ='.$this->fk_prev_id;
				    $sql .= ' AND f.situation_cycle_ref = '.$tmpinvoice->situation_cycle_ref; // Prevent cycle outed
				    $sql .= ' AND f.type = '.Facture::TYPE_CREDIT_NOTE;

				    $res = $this->db->query($sql);
				    if ($res) {
				        while ($obj = $this->db->fetch_object($res)) {
				            $returnPercent = $returnPercent + floatval($obj->situation_percent);
				        }
				    }
				}

				return $returnPercent;
			} else {
				$this->error = $this->db->error();
				dol_syslog(get_class($this)."::select Error ".$this->error, LOG_ERR);
				$this->db->rollback();
				return -1;
			}
		}
	}


	*/


}