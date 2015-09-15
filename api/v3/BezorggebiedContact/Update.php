<?php

/**
 * BezorggebiedContact.Update API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_bezorggebied_contact_update_spec(&$spec)
{

}

/**
 * BezorggebiedContact.Update API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_bezorggebied_contact_update($params)
{

  $config = CRM_Geostelsel_Config::singleton();
  $bezorggebied_config = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
  $updated = 0;
  $offset = CRM_Core_BAO_Setting::getItem('nl.sp.bezorggebied', 'job.update.offset', NULL, 0);
  $update = CRM_Core_BAO_Setting::getItem('nl.sp.bezorggebied', 'job.update.update', NULL, 1);

  $limit = 1000;
  if (isset($params['limit']) && is_numeric($params['limit'])) {
    $limit = $params['limit'];
  }

  $run = false;
  if (isset($params['force']) && $params['force']) {
    $run = true;
    $offset = 0;
  } elseif ($update) {
    $run = true;
    $offset = 0;
  } elseif ($offset > 0) {
    $run = true;
  }
  $oldOffset = $offset;

  if ($run) {

    $sql = "SELECT `civicrm_contact`.`id`,
               `g`.`".$config->getAfdelingsField('column_name')."` as `afdeling_id`
            FROM `civicrm_contact`
            LEFT JOIN `".$config->getGeostelselCustomGroup('table_name')."` g ON `g`.`entity_id` = `civicrm_contact`.`id`
            WHERE `contact_type` = 'Individual'
            ORDER BY `civicrm_contact`.`id` ASC
            LIMIT %1, %2";

    $params[1] = array($offset, 'Integer');
    $params[2] = array($limit, 'Integer');

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      CRM_Bezorggebieden_Handler_AutoBezorggebiedLink::updateContact($dao->id, $dao->afdeling_id);
      $updated++;
    }

    if ($updated > 0) {
      CRM_Core_BAO_Setting::setItem($offset + $updated, 'nl.sp.bezorggebied', 'job.update.offset');
      $newOffset = $offset + $updated;
      CRM_Core_BAO_Setting::setItem(0, 'nl.sp.bezorggebied', 'job.update.update');
    } else {
      $newOffset = 0;
      CRM_Core_BAO_Setting::setItem(0, 'nl.sp.bezorggebied', 'job.update.offset');
      CRM_Core_BAO_Setting::setItem(0, 'nl.sp.bezorggebied', 'job.update.update');
    }
  }
  $returnValues[]['message'] = 'Updated '.$updated.' contacts, old offset: '.$oldOffset.' new offset: '.$newOffset;

  return civicrm_api3_create_success($returnValues, $params, 'BezorggebiedContact', 'update');
}

