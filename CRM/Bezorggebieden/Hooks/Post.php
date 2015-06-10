<?php

class CRM_Bezorggebieden_Hooks_Post {

  private static $handledObjects = array();

  public static function post( $op, $objectName, $objectId, &$objectRef ) {
    if ($objectName !== 'Address') {
      return;
    }
    if (isset(self::$handledObjects['Address']) && isset(self::$handledObjects['Address'][$objectId])) {
      return;
    }
    if ($op != 'create' && $op != 'edit') {
      return;
    }
    if (!$objectRef->contact_id) {
      //address does not belong to a contact but probably to an event
      //so don't update the contact with bezorggebied information
      return;
    }
    self::$handledObjects['Address'][$objectId] = true;
    $config = CRM_Geostelsel_Config::singleton();
    $sql = "SELECT `g`.`".$config->getAfdelingsField('column_name')."` as `afdeling_id`
                                        FROM `".$config->getGeostelselCustomGroup('table_name')."` g
                                        WHERE `g`.`entity_id` = %1";
    $params[1] = array($objectRef->contact_id, 'Integer');
    $afdeling_id = CRM_Core_DAO::singleValueQuery($sql, $params);

    CRM_Bezorggebieden_Handler_AutoBezorggebiedLink::updateContact($objectRef->contact_id, $afdeling_id);
  }

}