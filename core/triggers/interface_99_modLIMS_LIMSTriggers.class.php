$msg_text<?php
/* Copyright (C) 2020 David Bensel <david.bensel@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modMyModule_MyModuleTriggers.class.php
 * \ingroup mymodule
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modMyModule_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';
require_once DOL_DOCUMENT_ROOT.'/comm/action/class/actioncomm.class.php';

/**
 *  Class of triggers for LIMS module
 */
class InterfaceLIMSTriggers extends DolibarrTriggers
{
	/**
	 * @var DoliDB Database handler
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "LIMS triggers";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'lims@lims';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($conf->lims->enabled)) return 0; // If module is not enabled, we do nothing

		// Data and type of action are stored into $object and $action
		if (!empty($action)) {
			require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
			$product = new Product($this->db);
			dol_include_once('/lims/class/methods.class.php', 'Methods');
			$methods = new Methods($this->db);
			dol_include_once('/lims/class/results.class.php', 'Results');
			$results = new Results($this->db);

			// ECMFILES_trigger will send object of class EcmFiles
			if ($object->element == 'ecmfiles') {
				$arr = explode('_', $object->src_object_type, 2);
				$originalmodule = $arr[0];  // should always be 'lims'
				$originalclass = $arr[1];
				if ($originalmodule != 'lims') {
					dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". orignalmodule=".$originalmodule.": Call not from LIMS, exit runTrigger");
					return 0;
				}
				// we only expect lims-classes
				dol_include_once('/'.$originalmodule.'/class/'.$originalclass.'.class.php');
				$original_object = new $originalclass($object->db);
				$original_object->fetch($object->src_object_id);
				$product->fetch($original_object->fk_product);
			}
			else {
				if (!empty($object->fk_product)) $product->fetch($object->fk_product);
				if (!empty($object->fk_method)) $method->fetch($object->fk_method);
				if (!empty($object->fk_result)) $result->fetch($object->fk_result);				
			}

			if ($object->element == 'equipment' || $originalclass == 'equipment') {
				$msg_label = $langs->transnoentitiesnoconv("EQlabelEquipment")." ";
				$msg_text = $msg_label." ($product->label) ";
			}
			if ($object->element == 'samples' || $originalclass == 'samples') {
				$msg_label = $langs->transnoentitiesnoconv("Sample")." ";
				$msg_text = $msg_label." (".$object->ref."v".$object->version.") ";
			}
		}
		else {
			dol_syslog("Trigger '".$this->name."' launched by ".__FILE__.": Action empty, exit runTrigger");
			return 0; // exit if action is empty
		}

		if ($object->module != 'lims' && $originalmodule != 'lims') { // exit if trigger is called by other module
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". object->module=".$object->module." originalmodule=".$originalmodule." : Call not from LIMS, exit runTrigger");
			return 0;
		}

		switch ($action) {
			// Users
			//case 'USER_CREATE':
			//case 'USER_MODIFY':
			//case 'USER_NEW_PASSWORD':
			//case 'USER_ENABLEDISABLE':
			//case 'USER_DELETE':
			//case 'USER_SETINGROUP':
			//case 'USER_REMOVEFROMGROUP':

			// Actions
			//case 'ACTION_MODIFY':
			//case 'ACTION_CREATE':
			//case 'ACTION_DELETE':

			// Groups
			//case 'USERGROUP_CREATE':
			//case 'USERGROUP_MODIFY':
			//case 'USERGROUP_DELETE':

			// Companies
			//case 'COMPANY_CREATE':
			//case 'COMPANY_MODIFY':
			//case 'COMPANY_DELETE':

			// Contacts
			//case 'CONTACT_CREATE':
			//case 'CONTACT_MODIFY':
			//case 'CONTACT_DELETE':
			//case 'CONTACT_ENABLEDISABLE':

			// Products
			//case 'PRODUCT_CREATE':
			//case 'PRODUCT_MODIFY':
			//case 'PRODUCT_DELETE':
			//case 'PRODUCT_PRICE_MODIFY':
			//case 'PRODUCT_SET_MULTILANGS':
			//case 'PRODUCT_DEL_MULTILANGS':

			//Stock mouvement
			//case 'STOCK_MOVEMENT':

			//MYECMDIR
			//case 'MYECMDIR_CREATE':
			//case 'MYECMDIR_MODIFY':
			//case 'MYECMDIR_DELETE':

			// Customer orders
			//case 'ORDER_CREATE':
			//case 'ORDER_MODIFY':
			//case 'ORDER_VALIDATE':
			//case 'ORDER_DELETE':
			//case 'ORDER_CANCEL':
			//case 'ORDER_SENTBYMAIL':
			//case 'ORDER_CLASSIFY_BILLED':
			//case 'ORDER_SETDRAFT':
			//case 'LINEORDER_INSERT':
			//case 'LINEORDER_UPDATE':
			//case 'LINEORDER_DELETE':

			// Supplier orders
			//case 'ORDER_SUPPLIER_CREATE':
			//case 'ORDER_SUPPLIER_MODIFY':
			//case 'ORDER_SUPPLIER_VALIDATE':
			//case 'ORDER_SUPPLIER_DELETE':
			//case 'ORDER_SUPPLIER_APPROVE':
			//case 'ORDER_SUPPLIER_REFUSE':
			//case 'ORDER_SUPPLIER_CANCEL':
			//case 'ORDER_SUPPLIER_SENTBYMAIL':
			//case 'ORDER_SUPPLIER_DISPATCH':
			//case 'LINEORDER_SUPPLIER_DISPATCH':
			//case 'LINEORDER_SUPPLIER_CREATE':
			//case 'LINEORDER_SUPPLIER_UPDATE':
			//case 'LINEORDER_SUPPLIER_DELETE':

			// Proposals
			//case 'PROPAL_CREATE':
			//case 'PROPAL_MODIFY':
			//case 'PROPAL_VALIDATE':
			//case 'PROPAL_SENTBYMAIL':
			//case 'PROPAL_CLOSE_SIGNED':
			//case 'PROPAL_CLOSE_REFUSED':
			//case 'PROPAL_DELETE':
			//case 'LINEPROPAL_INSERT':
			//case 'LINEPROPAL_UPDATE':
			//case 'LINEPROPAL_DELETE':

			// SupplierProposal
			//case 'SUPPLIER_PROPOSAL_CREATE':
			//case 'SUPPLIER_PROPOSAL_MODIFY':
			//case 'SUPPLIER_PROPOSAL_VALIDATE':
			//case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
			//case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
			//case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
			//case 'SUPPLIER_PROPOSAL_DELETE':
			//case 'LINESUPPLIER_PROPOSAL_INSERT':
			//case 'LINESUPPLIER_PROPOSAL_UPDATE':
			//case 'LINESUPPLIER_PROPOSAL_DELETE':

			// Contracts
			//case 'CONTRACT_CREATE':
			//case 'CONTRACT_MODIFY':
			//case 'CONTRACT_ACTIVATE':
			//case 'CONTRACT_CANCEL':
			//case 'CONTRACT_CLOSE':
			//case 'CONTRACT_DELETE':
			//case 'LINECONTRACT_INSERT':
			//case 'LINECONTRACT_UPDATE':
			//case 'LINECONTRACT_DELETE':

			// Bills
			//case 'BILL_CREATE':
			//case 'BILL_MODIFY':
			//case 'BILL_VALIDATE':
			//case 'BILL_UNVALIDATE':
			//case 'BILL_SENTBYMAIL':
			//case 'BILL_CANCEL':
			//case 'BILL_DELETE':
			//case 'BILL_PAYED':
			//case 'LINEBILL_INSERT':
			//case 'LINEBILL_UPDATE':
			//case 'LINEBILL_DELETE':

			//Supplier Bill
			//case 'BILL_SUPPLIER_CREATE':
			//case 'BILL_SUPPLIER_UPDATE':
			//case 'BILL_SUPPLIER_DELETE':
			//case 'BILL_SUPPLIER_PAYED':
			//case 'BILL_SUPPLIER_UNPAYED':
			//case 'BILL_SUPPLIER_VALIDATE':
			//case 'BILL_SUPPLIER_UNVALIDATE':
			//case 'LINEBILL_SUPPLIER_CREATE':
			//case 'LINEBILL_SUPPLIER_UPDATE':
			//case 'LINEBILL_SUPPLIER_DELETE':

			// Payments
			//case 'PAYMENT_CUSTOMER_CREATE':
			//case 'PAYMENT_SUPPLIER_CREATE':
			//case 'PAYMENT_ADD_TO_BANK':
			//case 'PAYMENT_DELETE':

			// Online
			//case 'PAYMENT_PAYBOX_OK':
			//case 'PAYMENT_PAYPAL_OK':
			//case 'PAYMENT_STRIPE_OK':

			// Donation
			//case 'DON_CREATE':
			//case 'DON_UPDATE':
			//case 'DON_DELETE':

			// Interventions
			//case 'FICHINTER_CREATE':
			//case 'FICHINTER_MODIFY':
			//case 'FICHINTER_VALIDATE':
			//case 'FICHINTER_DELETE':
			//case 'LINEFICHINTER_CREATE':
			//case 'LINEFICHINTER_UPDATE':
			//case 'LINEFICHINTER_DELETE':

			// Members
			//case 'MEMBER_CREATE':
			//case 'MEMBER_VALIDATE':
			//case 'MEMBER_SUBSCRIPTION':
			//case 'MEMBER_MODIFY':
			//case 'MEMBER_NEW_PASSWORD':
			//case 'MEMBER_RESILIATE':
			//case 'MEMBER_DELETE':

			// Categories
			//case 'CATEGORY_CREATE':
			//case 'CATEGORY_MODIFY':
			//case 'CATEGORY_DELETE':
			//case 'CATEGORY_SET_MULTILANGS':

			// Projects
			//case 'PROJECT_CREATE':
			//case 'PROJECT_MODIFY':
			//case 'PROJECT_DELETE':

			// Project tasks
			//case 'TASK_CREATE':
			//case 'TASK_MODIFY':
			//case 'TASK_DELETE':

			// Task time spent
			//case 'TASK_TIMESPENT_CREATE':
			//case 'TASK_TIMESPENT_MODIFY':
			//case 'TASK_TIMESPENT_DELETE':
			//case 'PROJECT_ADD_CONTACT':
			//case 'PROJECT_DELETE_CONTACT':
			//case 'PROJECT_DELETE_RESOURCE':

			// Shipping
			//case 'SHIPPING_CREATE':
			//case 'SHIPPING_MODIFY':
			//case 'SHIPPING_VALIDATE':
			//case 'SHIPPING_SENTBYMAIL':
			//case 'SHIPPING_BILLED':
			//case 'SHIPPING_CLOSED':
			//case 'SHIPPING_REOPEN':
			//case 'SHIPPING_DELETE':
			
			case 'SAMPLES_CREATE':
			case 'EQUIPMENT_CREATE':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("created"); // (note, long text)
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("created"); // (label, short text)
				break;
			
			case 'SAMPLES_VALIDATE':
			case 'EQUIPMENT_VALIDATE':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("validated"); // (note, long text)
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("validated"); // (label, short text)
				break;

			case 'SAMPLES_UNVALIDATE':
			case 'EQUIPMENT_INVALIDATE':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("invalidated"); // (note, long text)
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("invalidated"); // (label, short text)
				break;
			
			case 'SAMPLES_MODIFY':
			case 'EQUIPMENT_MODIFY':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("modified"); // (note, long text)
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("modified"); // (label, short text)
				break;

			case 'EQUIPMENT_RENEW':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("renewed");
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("EQbuttonMaintainRenew");
				break;

			case 'EQUIPMENT_REVOKE':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("revoked");
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("EQbuttonMaintainRevoke");
				break;

			case 'ECMFILES_CREATE':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("PDFcreated");
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("PDFcreated");
				break;

			case 'ECMFILES_MODIFY':
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
				$actionnote = $msg_text.$langs->transnoentitiesnoconv("PDFmodified");
				$actionlabel = $msg_label.$langs->transnoentitiesnoconv("PDFmodified");
				break;

			default:
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id." Action not defined, exit runTrigger");
				return 0;
		}

		$actioncomm = new ActionComm($this->db);

		$actioncomm->type_code = 'AC_OTH_AUTO'; // Type of event ('AC_OTH', 'AC_OTH_AUTO', 'AC_XXX'...)
		$actioncomm->code = 'AC_'.$action;
		$actioncomm->label = $actionlabel;
		$actioncomm->note_private = $actionnote;
		$actioncomm->fk_project = 0;
		$actioncomm->datep = dol_now();	// Date action start
		$actioncomm->datef = dol_now();	// Date action end
		$actioncomm->percentage = -1; // Not applicable
		$actioncomm->socid = $object->thirdparty->id;
		$actioncomm->contact_id = 0;
		$actioncomm->authorid = $user->id; // User creating action
		$actioncomm->userownerid = $user->id; // Owner of action
		// Fields when action is en email (content should be added into note)
		/*
		$actioncomm->email_msgid = $cmail->msgid;
		$actioncomm->email_from = $from;
		$actioncomm->email_sender = '';
		$actioncomm->email_to = $to;
		$actioncomm->email_tocc = $sendtocc;
		$actioncomm->email_tobcc = $sendtobcc;
		$actioncomm->email_subject = $subject;
		$actioncomm->errors_to = '';
		*/
		// ECMFILE trigger sends object of class EcmFiles => elementtype = 'ecmfiles@' instead of 'equipment@lims'
		if ($object->element == 'ecmfiles'){
			$actioncomm->fk_element = $object->src_object_id;
			$actioncomm->elementtype = $originalclass.'@'.$originalmodule;
		}
		else{
			$actioncomm->fk_element = $object->id;	// (ID of object to link action event to)
			$actioncomm->elementtype = $object->element.'@'.$object->module;
		}
		//$actioncomm->extraparams = $extraparams;

		$actioncomm->create($user, true); // don't call trigger to avoid recursive loop

		return 0;
	}
}
