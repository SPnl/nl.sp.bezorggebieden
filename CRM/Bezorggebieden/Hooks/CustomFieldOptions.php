<?php

class CRM_Bezorggebieden_Hooks_CustomFieldOptions {

  public static function options($fieldID, &$options, $detailedFormat = false ) {
    $config = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    if ($fieldID != $config->getCustomFieldBezorggebied('id')) {
      return;
    }

    $detailedOptions = self::getAllBezorgGebieden();

    if (!$detailedFormat ) {
      foreach ($detailedOptions AS $key => $choice) {
        $options[$choice['value']] = $choice['label'];
      }
    } else {
      $options += $detailedOptions;
    }
  }

  private static function getAllBezorgGebieden() {
    $return = array();

    $config = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
    $naam_field = $config->getNaamField('column_name');
    $sql = "SELECT `b`.*, `c`.`display_name`, `v`.`label` as `berzorging_per`
            FROM `".$config->getCustomGroup('table_name')."` `b`
            INNER JOIN `civicrm_contact` `c` ON `b`.`entity_id` = `c`.`id`
            INNER JOIN `civicrm_option_value` `v` ON `b`.`".$config->getBezorgingPerField('column_name')."` = `v`.`value` AND `v`.`option_group_id` = '".$config->getBezorgingPerField('option_group_id')."'
            ORDER BY `c`.`display_name`, `b`.`".$naam_field."`";

    $dao = CRM_Core_DAO::executeQuery($sql);
    while($dao->fetch()) {
      $option = array();
      $option['value'] = $dao->id;
      $option['label'] = $dao->berzorging_per .' ('.$dao->display_name . ': '.$dao->$naam_field.')';
      $option['id'] = $dao->id;

      $return[$dao->id] = $option;
    }

    return $return;
  }

}