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

    self::$handledObjects['Address'][$objectId] = true;
    CRM_Bezorggebieden_Handler_AutoBezorggebiedLink::updateContact($objectRef->contact_id);
  }

}