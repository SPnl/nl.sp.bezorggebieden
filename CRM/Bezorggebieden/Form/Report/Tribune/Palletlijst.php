<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.4                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

class CRM_Bezorggebieden_Form_Report_Tribune_Palletlijst extends CRM_Report_Form {
	
	function __construct() {
		$this->_columns = array(
			'civicrm_contact' => array(
				'dao' => 'CRM_Contact_DAO_Contact',
				'fields' => array(
					'organization_name' => array('title' => ts('Contact Name'), 'required' => TRUE, 'no_repeat' => TRUE),
					'first_name' => array('title' => ts('First Name')),
					'last_name' => array('title' => ts('Last Name')),
					'id' => array('no_display' => TRUE, 'required' => TRUE),
					'contact_type' => array('title' => ts('Contact Type')),
					'contact_sub_type' => array('title' => ts('Contact SubType'))
				),
				'filters' => array(
					'sort_name' => array('title' => ts('Contact Name')), 'source' => array('title' => ts('Contact Source'), 'type' => CRM_Utils_Type::T_STRING),
					'id' => array('title' => ts('Contact ID'), 'no_display' => TRUE),
				),
				'grouping' => 'contact-fields'
			),
			'civicrm_address' => array(
				'dao' => 'CRM_Core_DAO_Address',
				'grouping' => 'contact-fields',
				'fields' => array(
					'id' => array('no_display' => TRUE, 'required' => TRUE),
					'street_address' => array('default' => TRUE), 
					'city' => array('default' => TRUE), 
					'postal_code' => NULL
				)
			)
		);
		$this->_tagFilter = TRUE;
		parent::__construct();
	}

	function preProcess() {
		parent::preProcess();
	}
	
	function select() {
		parent::select();
		$this->_select = substr($this->_select, 0, -1).", ";
		$this->_select .= "
			IFNULL((
				SELECT 1 
				FROM `civicrm_address` as `casub`
				WHERE `casub`.`master_id` = address_civireport.id
				LIMIT 1
			), 0) as `pallet`
		";
	}
	
	function from() {
		$this->_from = NULL;
		$this->_from = "
			FROM civicrm_contact {$this->_aliases['civicrm_contact']} 
			RIGHT JOIN civicrm_address {$this->_aliases['civicrm_address']} ON (
				{$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id
					AND
				{$this->_aliases['civicrm_address']}.location_type_id = 7
			)
		";
	}
	
	function where() {
		parent::where();
		$this->_where .= "AND address_civireport.master_id IS NULL";
	}
	
	function orderBy() {
		$this->_orderBy = "ORDER BY pallet DESC, civicrm_address_city ASC";
	}
	
	function modifyColumnHeaders() {
		parent::modifyColumnHeaders();
		$this->_columnHeaders["aantal_tribunes"] = array("title" => "Aantal Tribunes", "type" => 2);
		$this->_columnHeaders["pallet"] = array("title" => "Pallet afdeling", "type" => 2);
	}
	
	function postProcess() {
		$this->beginPostProcess();
		$sql = $this->buildQuery(TRUE);
		$sql = str_ireplace("SELECT SQL_CALC_FOUND_ROWS 1", "SELECT 1", $sql);
		$this->buildRows($sql, $rows);
		$this->formatDisplay($rows);
		$this->doTemplateAssignment($rows);
		$this->endPostProcess($rows);
	}
	

	function alterDisplay(&$rows) {
		$totalAmountOfTribunes = 0;
		foreach($rows as $rowKey => $rowValue) {
			$departmentTribunes = $this->countTribunes($rowValue['civicrm_address_id']);
			$totalAmountOfTribunes = $totalAmountOfTribunes + $departmentTribunes;
			$rows[$rowKey]['aantal_tribunes'] = $departmentTribunes;
		}
		$drukkerTribunesQuery = CRM_Core_DAO::executeQuery("
			SELECT COUNT(*) as `aantal` 
			FROM `civicrm_membership`
			WHERE `civicrm_membership`.`status_id` IN (1,2) AND `civicrm_membership`.`membership_type_id` IN (4,5,13)
		");
		$drukkerTribunes = ($drukkerTribunesQuery->fetch()) ? $drukkerTribunesQuery->aantal : 0;
		$drukkerTribunes = $drukkerTribunes - $totalAmountOfTribunes;
		$drukkerRow = array(
			"civicrm_contact_organization_name" => "Prevision",
			"aantal_tribunes" => $drukkerTribunes,
			"pallet" => "2"
		);
		array_unshift($rows, $drukkerRow);
	}
	
	function countTribunes($address_id) {
		$ranges = array();
		$bezorggebied = CRM_Core_DAO::executeQuery("
			SELECT * 
			FROM `civicrm_value_bezorggebieden_6` 
			WHERE `entity_id` IN (
				SELECT  `contact_id` 
				FROM  `civicrm_address` 
				WHERE  `id` = ".$address_id."
				OR  `master_id` = ".$address_id."
			) AND `bezorging_per_128` IN (
				'Bezorger','Afdeling'
			)
		");
		while($bezorggebied->fetch()) {
			if($bezorggebied->start_cijfer_range_10 == $bezorggebied->eind_cijfer_range_12) {
				if($bezorggebied->start_cijfer_range_10 <= $bezorggebied->eind_letter_range_13) {
					$ranges[$bezorggebied->start_cijfer_range_10.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $bezorggebied->start_cijfer_range_10;
					$ranges[$bezorggebied->start_cijfer_range_10.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $bezorggebied->start_letter_range_11;
					$ranges[$bezorggebied->start_cijfer_range_10.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $bezorggebied->eind_cijfer_range_12;
					$ranges[$bezorggebied->start_cijfer_range_10.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $bezorggebied->eind_letter_range_13;
				}
			} else {
				$rangeDifference = $bezorggebied->eind_cijfer_range_12 - $bezorggebied->start_cijfer_range_10;
				for($i = 0; $i <= $rangeDifference; $i++) {
					$currentRange = $bezorggebied->start_cijfer_range_10 + $i;
					if($currentRange == $bezorggebied->start_cijfer_range_10) {
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $currentRange;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $bezorggebied->start_letter_range_11;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $currentRange;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = "ZZ";
					} else if($currentRange == $groupData["custom_".$endIntObject['id']]) {						
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $currentRange;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = "AA";
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $currentRange;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $bezorggebied->eind_letter_range_13;
					} else {
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $currentRange;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = "AA";
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = $currentRange;
						$ranges[$currentRange.$bezorggebied->start_letter_range_11.$bezorggebied->eind_letter_range_13][] = "ZZ";
					}
				}
			}
		}
		if(count($ranges) > 0) {
			$queryString = "
			SELECT COUNT(*) as `aantal` 
			FROM `civicrm_membership`
			LEFT JOIN `civicrm_address` ON `civicrm_membership`.`contact_id` = `civicrm_address`.`contact_id` AND `civicrm_address`.`is_primary` = 1
			LEFT JOIN `civicrm_contact` ON `civicrm_membership`.`contact_id` = `civicrm_contact`.`id`
			WHERE `civicrm_membership`.`status_id` IN (1,2) AND `civicrm_membership`.`membership_type_id` IN (4,5,13)
			AND `civicrm_contact`.`do_not_mail` = 0
			AND (
			";
			foreach($ranges as $rangeData){
				$queryString .= "(";
					$queryString .= "(SUBSTR(`civicrm_address`.`postal_code`, 1, 4) = '".$rangeData[0]."')";
					$queryString .= "AND";
					$queryString .= "(SUBSTR(`civicrm_address`.`postal_code`, 6, 2) BETWEEN '".$rangeData[1]."' AND '".$rangeData[3]."')";
				$queryString .= ") OR ";
			}
			$queryString = substr($queryString, 0, -4).");";
			$aantal = CRM_Core_DAO::executeQuery($queryString);
			if($aantal->fetch()){
				$calcAantal = $aantal->aantal + 2;
				$extraTribunes = (ceil($calcAantal / 50) > 5) ? ceil($calcAantal / 50) : 5;
				return $calcAantal + $extraTribunes;
			} else {
				return "0";
			}
		} else {
			return "0";
		}
	}
	
}