<?php

class CRM_Bezorggebieden_Config_TribuneMembershipTypes {

  private static $singleton;

  private $membership_type_ids = array();

  private function __construct() {
    $membership_types = array(
      'Abonnee Blad-Tribune Betaald',
      'Abonnee Blad-Tribune Proef',
      'Abonnee Blad-Tribune Gratis',
    );
    $sql = "SELECT id from civicrm_membership_type where name = %1";
    foreach($membership_types as $type) {
      $params = array(
        1 => array($type, 'String'),
      );
      $this->membership_type_ids[] = CRM_Core_DAO::singleValueQuery($sql, $params);
    }
  }

  /**
   * @return CRM_Bezorggebieden_Config_TribuneMembershipTypes
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Bezorggebieden_Config_TribuneMembershipTypes();
    }
    return self::$singleton;
  }

  public function getMembershipTypeIds() {
    return $this->membership_type_ids;
  }

}