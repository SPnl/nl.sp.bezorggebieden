<?php

class CRM_Bezorggebieden_Config_BezorggebiedContact {

  private static $singleton;

  private $cgBezorggebiedContact;

  private $cfBezorggebiedContact;

  private $bezorggebiedLocationType;

  private function __construct() {
    $this->cgBezorggebiedContact = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'bezorggebied_contact'));
    $this->cfBezorggebiedContact = civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorggebied', 'custom_group_id' => $this->cfBezorggebiedContact['id']));

    $this->bezorggebiedLocationType = civicrm_api3('LocationType', 'getsingle', array('name' => 'Thuis'));
  }

  /**
   * @return CRM_Bezorggebieden_Config_BezorggebiedContact
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Bezorggebieden_Config_BezorggebiedContact();
    }
    return self::$singleton;
  }

  public function getBezorggebiedLocationType($key) {
    return $this->bezorggebiedLocationType[$key];
  }

  public function getCustomGroupBezorggebiedContact($key) {
    return $this->cgBezorggebiedContact[$key];
  }

  public function getCustomFieldBezorggebied($key) {
    return $this->cfBezorggebiedContact[$key];
  }

}