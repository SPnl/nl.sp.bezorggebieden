<?php

class CRM_Bezorggebieden_Form_Report_Tribune_Bezorging extends CRM_Report_Form {

	protected $_addressField = FALSE;

	protected $_emailField = FALSE;

	protected $_summary = NULL;

	protected $_customGroupGroupBy = FALSE;

	protected $_add2groupSupported = FALSE;

	function __construct() {

		// Disable filters
		$this -> _groupFilter = FALSE;
		$this -> _tagFilter = FALSE;
		parent::__construct();

	}

	function preProcess() {

		// Set report tile
		$this -> assign('reportTitle', ts('Tribune Bezorging'));
		parent::preProcess();

	}

	function postProcess() {

		// Fetch custom group
		$deliverAreaGroup = civicrm_api3('CustomGroup', 'getsingle', array("name" => "Bezorggebieden"));

		// Check for custom fields and tables
		$cfStartNumberRange = civicrm_api3('CustomField', 'getsingle', array("name" => "Start_cijfer_range", "custom_group_id" => $deliverAreaGroup['id']));
		$cfEndNumberRange = civicrm_api3('CustomField', 'getsingle', array("name" => "Eind_cijfer_range", "custom_group_id" => $deliverAreaGroup['id']));
		$cfStartLetterRange = civicrm_api3('CustomField', 'getsingle', array("name" => "Start_letter_range", "custom_group_id" => $deliverAreaGroup['id']));
		$cfEndLetterRange = civicrm_api3('CustomField', 'getsingle', array("name" => "Eind_letter_range", "custom_group_id" => $deliverAreaGroup['id']));
		$cfDeliverer = civicrm_api3('CustomField', 'getsingle', array("name" => "Bezorger", "custom_group_id" => $deliverAreaGroup['id']));
		
		// Fetch membership type id
		$msType = civicrm_api3('MembershipType', 'getsingle', array("name" => "Abonnee Tribune"));
		
		// Start post process
		$this -> beginPostProcess();

		// Prepare the query
		$sql = 
		"
			SELECT
			`cc`.`id` as `contact_id`,
			`cc`.`display_name`,  
			`ca`.`street_address`,
			`ca`.`postal_code`,
			`ca`.`city`,
			`cb`.`id` as `deliverer_id`,
			`cb`.`display_name` as `deliverer_name`, 
			`ca`.`street_address` as `deliverer_street_adress`,
			`ca`.`postal_code` as `deliverer_postal_code`,
			`ca`.`city` as `deliverer_city`,
			`cdp`.`display_name` as `department`,
			`cbzg`.*
			
			FROM `civicrm_membership` as `cm`
			
			LEFT JOIN `civicrm_contact` as `cc` ON `cm`.`contact_id` = `cc`.`id`
			
			LEFT JOIN `civicrm_address` as `ca` ON `ca`.`contact_id` = `cc`.`id` AND `ca`.`is_primary` = 1
			
			LEFT JOIN `" . $deliverAreaGroup['table_name'] . "` as `cbzg` ON 
			( 
				(SUBSTR(REPLACE(`ca`.`postal_code`, ' ', ''), 1, 4) BETWEEN `cbzg`.`" . $cfStartNumberRange['column_name'] . "` AND `cbzg`.`" . $cfEndNumberRange['column_name'] . "`)
					AND
				(SUBSTR(REPLACE(`ca`.`postal_code`, ' ', ''), -2) BETWEEN `cbzg`.`" . $cfStartLetterRange['column_name'] . "` AND `cbzg`.`" . $cfEndLetterRange['column_name'] . "`)
			)
			
			LEFT JOIN `civicrm_contact` as `cb` ON `cb`.`id` = `cbzg`.`" . $cfDeliverer['column_name'] . "`
			
			LEFT JOIN `civicrm_address` as `cba` ON `cba`.`contact_id` = `cc`.`id` AND `cba`.`is_primary` = 1
			
			LEFT JOIN `civicrm_contact` as `cdp` ON `cdp`.`id` = `cbzg`.`entity_id`
			
			WHERE (`cm`.`status_id` IN (1, 2)) AND (`cm`.`membership_type_id` = '".$msType['id']."') 
			
			ORDER BY `department` ASC, `ca`.`city` ASC, `deliverer_name` ASC, `ca`.`postal_code` ASC;
		";

		// Set column headers
		$this -> _columnHeaders = array('contact_id' => array("title" => 'id', "no_display" => true), 'display_name' => array("title" => 'Tribune lid'), 'street_address' => array("title" => 'Adres'), 'postal_code' => array("title" => 'Postcode'), 'city' => array("title" => 'Woonplaats'), 'department' => array("title" => 'Afdeling'), 'deliverer_name' => array("title" => 'Bezorger'), 'deliverer_id' => array("title" => 'deliver_id', "no_display" => true));

		// Define row array
		$rows = array();

		// Let's build the rows!
		$this -> buildRows($sql, $rows);
		
		// Format for display
		$this -> formatDisplay($rows);

		// Assign the template with our rows
		$this -> doTemplateAssignment($rows);

		// Civi handles the rest and closes the process
		$this -> endPostProcess($rows);

	}

	function alterDisplay(&$rows) {
		
		// Loop parameters
		$currentDepartment = "";

		// Loop counter
		$counter = 0;

		// Number of magazines per group
		$magezinesPerGroup = 0;

		// Read trough all rows and devide them in to groups
		foreach ($rows as $row) {

			// Check if we need to close the previous group
			if ($counter > 0 && $currentDepartment != $row['department']) {

				// Add number of magazines rule to the bottom
				$newRowArray[$counter]['display_name'] = "<i>Aantal magazines: " . $magezinesPerGroup . "</i>";

				// Increment row counter
				$counter++;

				// Reset number of magazines per group
				$magezinesPerGroup = 0;
			}

			// Conditions to set header rows
			if (empty($currentDepartment) || $currentDepartment != $row['department']) {

				// Check if we have a department, if not, show that these are sent by mail
				if (empty($row['department']) && $counter == 0) {

					// Place row header
					$newRowArray[$counter]['display_name'] = "<strong>Per Post</strong>";

				} else if (!empty($row['department'])) {

					// Place row header
					$newRowArray[$counter]['display_name'] = "<strong>Afdeling: " . $row['department'] . "</strong>";

				}

				// Increment counter
				$counter++;

			}

			// Place current row inside new row array
			$newRowArray[$counter] = $row;
			$magezinesPerGroup++;

			// Generate url for contact details
			$url = CRM_Utils_System::url("civicrm/contact/view", 'reset=1&cid=' . $row['contact_id'], $this -> _absoluteUrl);
			$newRowArray[$counter]['display_name_link'] = $url;

			// Generate url for deliverer details
			$url = CRM_Utils_System::url("civicrm/contact/view", 'reset=1&cid=' . $row['deliverer_id'], $this -> _absoluteUrl);
			$newRowArray[$counter]['deliverer_name_link'] = $url;

			// Set current department as department
			$currentDepartment = (empty($row['department'])) ? "Per Post" : $row['department'];

			// Increment counter
			$counter++;

		}

		// Last number of tribunes
		$newRowArray[$counter]['display_name'] = "<i>Aantal magazines: " . $magezinesPerGroup . "</i>";

		// Return new row array
		$rows = $newRowArray;
		

	}

	function countStat(&$statistics, $count) {
		// Hide row counter
	}

}