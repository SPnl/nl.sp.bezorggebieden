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
function bezorggebieden_civicrm_validateForm( $formName, &$fields, &$files, &$form, &$errors ) {

	// Fetch the custom group properties
	$customGroupObject		= civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Bezorggebieden'));

	// Check if the posted form is the deliver area form
	if($formName == "CRM_Contact_Form_CustomData" && in_array($customGroupObject['id'], array_keys($fields['hidden_custom_group_count']))) {
		
		// Fetch all the custom fields properties
		$nameObject 			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorggebied_naam', 'custom_group_id' => $customGroupObject['id']));
		$startIntObject 		= civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_cijfer_range', 'custom_group_id' => $customGroupObject['id']));
		$endIntObject 			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Eind_cijfer_range', 'custom_group_id' => $customGroupObject['id']));
		$startCharObject 		= civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_letter_range', 'custom_group_id' => $customGroupObject['id']));
		$endCharObject 			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Eind_letter_range', 'custom_group_id' => $customGroupObject['id']));
		$bezorgerObject			= civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorging_per', 'custom_group_id' => $customGroupObject['id']));
		

		// Create an array with the filter field names
		$filterFieldNames =  array(	
			"custom_".$nameObject['id'], 
			"custom_".$startIntObject['id'],  
			"custom_".$endIntObject['id'],  
			"custom_".$startCharObject['id'],  
			"custom_".$endCharObject['id'],
			"custom_".$bezorgerObject['id'],
		);
	
		// Loop trough all the posted data
		foreach($fields as $field => $value){
						
			// Check if we find any instances of our given fields
			if(str_ireplace($filterFieldNames, '', $field) != $field && !stristr($field, "id")) {
				
				// Now that we have a match determine in what group it belongs
				$groupNumber = substr($field, (strrpos($field, "_")+1));
				
				// Determine fieldname
				$fieldName = substr($field, 0, strrpos($field, "_"));
				
				// Check if field is used for storing integers it's length is equal to 4 characters
				if((($fieldName == $filterFieldNames[1] AND strlen($value) != 4) OR ($fieldName == $filterFieldNames[2] AND strlen($value) != 4)) OR (($fieldName == $filterFieldNames[3] AND strlen($value) != 2) OR ($fieldName == $filterFieldNames[4] AND strlen($value) != 2))) {
					// Input is not valid
					$errors[$field] = "Opgegeven waarde niet valide.";
				}
				
				// Store group data and original fieldname
				$mainGroupData[$groupNumber][$fieldName] = $value; 
				$mainGroupData[$groupNumber]['originalFieldNames'][] = $field;				
			}
			
		}
		
		// Set range array
		$ranges = array();
		
		// Determine all possible ranges within parameters
		foreach($mainGroupData as $groupNumber => $groupData) {
			if($groupData["custom_".$startIntObject['id']] == $groupData["custom_".$endIntObject['id']]) {
				if($groupData["custom_".$startIntObject['id']] <= $groupData["custom_".$endCharObject['id']]) {
					$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$startIntObject['id']];
					$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$startCharObject['id']];
					$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$endIntObject['id']];
					$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$endCharObject['id']];
					$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$bezorgerObject['id']];
				}	else {
					$errors[$groupData['originalFieldNames'][2]] = "waarde is groter dan de einde van de range.";
					$errors[$groupData['originalFieldNames'][4]] = "waarde is kleiner dan de start van de range.";
					$errors[$groupData['originalFieldNames'][1]] = "waarde is kleiner dan de start van de range.";
					$errors[$groupData['originalFieldNames'][3]] = "waarde is kleiner dan de start van de range.";
				}
				$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData['originalFieldNames'][2];
				$ranges[$groupData["custom_".$startIntObject['id']].$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData['originalFieldNames'][4];
			} else {
				$rangeDifference = $groupData["custom_".$endIntObject['id']] - $groupData["custom_".$startIntObject['id']];
				for($i = 0; $i <= $rangeDifference; $i++) {
					$currentRange = $groupData["custom_".$startIntObject['id']] + $i;
					if($currentRange == $groupData["custom_".$startIntObject['id']]) {
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $currentRange;
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$startCharObject['id']];
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $currentRange;
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = "ZZ";
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$bezorgerObject['id']];
					} else if($currentRange == $groupData["custom_".$endIntObject['id']]) {						
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $currentRange;
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = "AA";
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $currentRange;
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$endCharObject['id']];
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$bezorgerObject['id']];
					} else {
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $currentRange;
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = "AA";
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $currentRange;
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = "ZZ";
						$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData["custom_".$bezorgerObject['id']];
					}
					$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData['originalFieldNames'][2];
					$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData['originalFieldNames'][4];
					$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData['originalFieldNames'][1];
					$ranges[$currentRange.$groupData["custom_".$startCharObject['id']].$groupData["custom_".$endCharObject['id']]][] = $groupData['originalFieldNames'][3];
				}
			}
		}
		
		// Put all group numbers in an array
		$groupNumbers = implode(",", array_keys($mainGroupData));
		
		// Now that we have all group data stored, let's compare them to existing data
		foreach($ranges as $rangeKey => $rangeData) {
			foreach($ranges as $subRangeKey => $subRangeData) {
				if($rangeKey == $subRangeKey) continue;
				if(
					(
						($rangeData[0] >= $subRangeData[0] AND $rangeData[0] <= $subRangeData[2])
							AND
						(($rangeData[1] >= $subRangeData[1] AND $rangeData[1] <= $subRangeData[3]) || ($rangeData[3] >= $subRangeData[1] AND $rangeData[1] <= $subRangeData[3]))
					)
					AND ($rangeData[4] == $subRangeData[4])
				) {
					$errors[$rangeData[5]] = "waarde komt in een ander range al voor (".$subRangeData[0].$subRangeData[1].$subRangeData[3].")";
					$errors[$rangeData[6]] = "waarde komt in een ander range al voor (".$subRangeData[0].$subRangeData[1].$subRangeData[3].")";
				}
			}
			// DB comparison
			$query = "
				SELECT * 
				FROM `".$customGroupObject['table_name']."` 
				LEFT JOIN `civicrm_contact` ON `".$customGroupObject['table_name']."`.`entity_id` = `civicrm_contact`.`id`
				WHERE
				(
					('".$rangeData[0]."' BETWEEN `".$startIntObject['column_name']."` AND `".$endIntObject['column_name']."`)
					AND
					(
						('".$rangeData[1]."' BETWEEN `".$startCharObject['column_name']."` AND `".$endCharObject['column_name']."`)
							OR
						('".$rangeData[3]."' BETWEEN `".$startCharObject['column_name']."` AND `".$endCharObject['column_name']."`)
					)
				) 
				AND (`".$bezorgerObject['column_name']."` = '".$rangeData[4]."')
				AND `".$customGroupObject['table_name']."`.`id` NOT IN (".$groupNumbers.");
			";
			
			// Execute query
			$dbData = CRM_Core_DAO::executeQuery($query);
			
			// Check if we can find any records matching the parameters of this dataset 
			if($dbData->fetch()) {

				// Store errors
				$errors[$rangeData[5]] = "Opgegeven postcode range combinatie van letters en cijfers komt al voor bij een ander bezorggebied (".$dbData->display_name." - ".$dbData->bezorggebied_naam_9.").";
				$errors[$rangeData[6]] = "Opgegeven postcode range combinatie van letters en cijfers komt al voor bij een ander bezorggebied (".$dbData->display_name." - ".$dbData->bezorggebied_naam_9.").";
			}
		}
		
		// If we didn't find any errors, return true statement so CIVI can save the records
		if(!isset($errors)) return true;
		
	}
	
}

function bezorggebieden_civicrm_custom( $op, $groupID, $entityID, &$params ) {
  $config = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
  if ($config->getCustomGroup('id') == $groupID) {
    //force update of contacts
    CRM_Core_BAO_Setting::setItem(1, 'nl.sp.bezorggebied', 'job.update.update');
  }
}

function bezorggebieden_civicrm_customFieldOptions($fieldID, &$options, $detailedFormat = false ) {
  CRM_Bezorggebieden_Hooks_CustomFieldOptions::options($fieldID, $options, $detailedFormat);
}

function bezorggebieden_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  CRM_Bezorggebieden_Hooks_Post::post($op, $objectName, $objectName, $objectRef);
}

function bezorggebieden_civicrm_tokens(&$tokens) {
  CRM_Bezorggebieden_Tokens_Afdeling::tokens($tokens);
}

function bezorggebieden_civicrm_tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null)
{
  $afdeling_tokens = CRM_Bezorggebieden_Tokens_Afdeling::singleton();
  $afdeling_tokens->tokenValues($values, $cids, $job, $tokens, $context);
}
