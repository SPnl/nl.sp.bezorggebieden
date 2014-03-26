<?php

require_once 'bezorggebieden.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function bezorggebieden_civicrm_config(&$config) {
  _bezorggebieden_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function bezorggebieden_civicrm_xmlMenu(&$files) {
  _bezorggebieden_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function bezorggebieden_civicrm_install() {
  return _bezorggebieden_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function bezorggebieden_civicrm_uninstall() {
  return _bezorggebieden_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function bezorggebieden_civicrm_enable() {
  return _bezorggebieden_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function bezorggebieden_civicrm_disable() {
  return _bezorggebieden_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function bezorggebieden_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _bezorggebieden_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function bezorggebieden_civicrm_managed(&$entities) {
  return _bezorggebieden_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function bezorggebieden_civicrm_caseTypes(&$caseTypes) {
  _bezorggebieden_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function bezorggebieden_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _bezorggebieden_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * In this hook we validate the delivering area ranges, so there's no overlapping in the records.
 *
 */
function bezorggebieden_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ) {;
	
	// Check if the posted form is the deliver area form
	if($formName == "CRM_Contact_Form_CustomData") {
		
		// Fetch the custom group properties
		$customGroupObject		= civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Bezorggebieden'));
		
		// Fetch all the custom fields properties
		$nameObject 			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorggebied_naam', 'custom_group_id' => $customGroupObject['id']));
		$startIntObject 		= civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_cijfer_range', 'custom_group_id' => $customGroupObject['id']));
		$endIntObject 			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Eind_cijfer_range', 'custom_group_id' => $customGroupObject['id']));
		$startCharObject 		= civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_letter_range', 'custom_group_id' => $customGroupObject['id']));
		$endCharObject 			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Eind_letter_range', 'custom_group_id' => $customGroupObject['id']));
		$deliverPersonObject 	= civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorger', 'custom_group_id' => $customGroupObject['id']));
		
		// Create an array with the filter field names
		$filterFieldNames =  array(	
									"custom_".$nameObject['id'], 
									"custom_".$startIntObject['id'],  
									"custom_".$endIntObject['id'],  
									"custom_".$startCharObject['id'],  
									"custom_".$endCharObject['id'],  
									"custom_".$deliverPersonObject['id'] 
																		 );
		
			
		// Loop trough all the posted data
		foreach($fields as $field => $value){
			
			// Check if we find any instances of our given fields
			if(str_ireplace($filterFieldNames, '', $field) != $field) {
				
				// Skip the fields that contain identifiers
				if(!stristr($field, "id")) {
								
				
					// Now that we have a match determine in what group it belongs
					$groupNumber = substr($field, -1);
					
					// Check whether the field is new or existing
					if(stristr(substr($field, -2), "-")) {
					
						// Field is new, so minus 3 characters
						$fieldName = substr($field, 0, -3);
					
					} else {
					
						// Existing field, so minus 2 characters
						$fieldName = substr($field, 0, -2);
					
					}
					
					// Check if field is used for storing integers it's length is equal to 4 characters
					if
					(
						(
							($fieldName == $filterFieldNames[1] AND strlen($value) != 4)
								OR
							($fieldName == $filterFieldNames[2] AND strlen($value) != 4)
						)
							OR
						(
							($fieldName == $filterFieldNames[3] AND strlen($value) != 2)
								OR
							($fieldName == $filterFieldNames[4] AND strlen($value) != 2)
						)
					) {
						// Input is not valid
						$errors[$field] = "Opgegeven waarde niet valide.";
					}
					
					// Store group data and original fieldname
					$mainGroupData[$groupNumber][$fieldName] = $value; 
					$mainGroupData[$groupNumber]['originalFieldNames'][] = $field; 					
					
				}

			}
		
		}
		
		if(is_null($errors)) break;
		
		// Put all group numbers in an array
		$groupNumbers = implode(",", array_keys($mainGroupData));
		
		// Now that we have all group data stored, let's compare them to existing data
		foreach($mainGroupData as $groupNumber => $groupData) {
			
			// Create the query
			$query = "
				SELECT * FROM `".$customGroupObject['table_name']."` WHERE
				(
					( 
						('".$groupData['custom_'.$startIntObject['id']]."' BETWEEN `".$startIntObject['column_name']."` AND `".$endIntObject['column_name']."`)
							AND
						('".$groupData['custom_'.$startCharObject['id']]."' BETWEEN `".$startCharObject['column_name']."` AND `".$endCharObject['column_name']."`)
					)
					OR
					(
						('".$groupData['custom_'.$endIntObject['id']]."' BETWEEN `".$startIntObject['column_name']."` AND `".$endIntObject['column_name']."`)
							AND
						('".$groupData['custom_'.$endCharObject['id']]."' BETWEEN `".$startCharObject['column_name']."` AND `".$endCharObject['column_name']."`)
					)
					OR
					(
						(`".$startIntObject['column_name']."` BETWEEN '".$groupData['custom_'.$startIntObject['id']]."' AND '".$groupData['custom_'.$endIntObject['id']]."')
							AND
						(`".$endIntObject['column_name']."` BETWEEN '".$groupData['custom_'.$startCharObject['id']]."' AND '".$groupData['custom_'.$endCharObject['id']]."')
					)
					OR
					(
						(`".$endIntObject['column_name']."` BETWEEN '".$groupData['custom_'.$startIntObject['id']]."' AND '".$groupData['custom_'.$endIntObject['id']]."')
							AND
						(`".$endCharObject['column_name']."` BETWEEN '".$groupData['custom_'.$startCharObject['id']]."' AND '".$groupData['custom_'.$endCharObject['id']]."')
					)
				)
				AND
				(
					`".$customGroupObject['table_name']."`.`id` NOT IN (".$groupNumbers.")
				)
			";

			
			// Execute query
			$dbAdapter = CRM_Core_DAO::executeQuery($query);
			
			// Check if we can find any records matching the parameters of this dataset 
			if($dbAdapter->fetch()) {
							
				// Store errors
				$errors[$groupData['originalFieldNames'][0]] = "Opgegeven postcode range combinatie van letters en cijfers komt al voor bij een ander bezorggebied.";
									
				// Break foreach loop
				break;
				
			} else {
			
				// Loop trough all posted data except the current one
				foreach($mainGroupData as $extraGroupKey => $extraGroupData) {
					
					// Check if loop entry is equal to current group number
					if($extraGroupKey == $groupNumber) {
						
						// They are equal, so skip
						continue;
						
					} else {
						
						// Check if the current start number is smaller then current end number
						if($groupData['custom_'.$startIntObject['id']] > $groupData['custom_'.$endIntObject['id']]) {
							
							// We found a match on the new data
							$errors[$groupData['originalFieldNames'][1]] = "is groter dan opgegeven eind cijfer range";
							$errors[$groupData['originalFieldNames'][3]] = "is kleiner dan opgegeven start cijfer range";
											
							// Break the loop, we found a match
							break;
							
						}
						
						// Check if the current start character is smaller then current end character
						if($groupData['custom_'.$startCharObject['id']] > $groupData['custom_'.$endCharObject['id']]) {
							
							// We found a match on the new data
							$errors[$groupData['originalFieldNames'][2]] = "is groter dan opgegeven eind letter range";
							$errors[$groupData['originalFieldNames'][4]] = "is kleiner dan opgegeven start letter range";
											
							// Break the loop, we found a match
							break;
							
						} 
						
						// Check if the current start and end number range are in between or equal to the other check number range 							
						if(($groupData['custom_'.$startIntObject['id']] >= $extraGroupData['custom_'.$startIntObject['id']] AND $groupData['custom_'.$startIntObject['id']] <= $extraGroupData['custom_'.$endIntObject['id']]) OR ($groupData['custom_'.$endIntObject['id']] >= $extraGroupData['custom_'.$startIntObject['id']] AND $groupData['custom_'.$endIntObject['id']] <= $extraGroupData['custom_'.$endIntObject['id']])) {
									
							// Check if the current start and end character range are in between or equal to the other check character range	
							if
							(
								(
									($groupData['custom_'.$startCharObject['id']] >= $extraGroupData['custom_'.$startCharObject['id']] AND $groupData['custom_'.$startCharObject['id']] <= $extraGroupData['custom_'.$endCharObject['id']])
									 
									OR
									 
									($groupData['custom_'.$endCharObject['id']] >= $extraGroupData['custom_'.$startCharObject['id']] AND $groupData['custom_'.$endCharObject['id']] <= $extraGroupData['custom_'.$endCharObject['id']])
								)
								
								OR
								
								(
									($extraGroupData['custom_'.$startCharObject['id']] >= $groupData['custom_'.$startCharObject['id']] AND $extraGroupData['custom_'.$startCharObject['id']] <= $groupData['custom_'.$endCharObject['id']]) 
										
									OR 
										
									($groupData['custom_'.$endCharObject['id']] >= $groupData['custom_'.$startCharObject['id']] AND $groupData['custom_'.$endCharObject['id']] <= $groupData['custom_'.$endCharObject['id']])
								)
							)
							{
										
								// We found a match on the new data
								$errors[$groupData['originalFieldNames'][0]] = "Opgegeven postcode range combinatie van letters en cijfers komt al voor bij een ander bezorggebied.";
												
								// Break the loop, we found a match
								break;
								
							}

						} 
	
					}
				
				}
			
			} 
			
		}
		
		// If we didn't find any errors, return true statement so CIVI can save the records
		if(!isset($errors)) return true;
		
	}
	
}