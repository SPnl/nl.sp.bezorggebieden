<?php

class CRM_Bezorggebieden_Config_BezorggebiedContact {

  private static $singleton;

  private $cgBezorggebiedContact;

  private $cfBezorggebiedContact;

  private $bezorggebiedLocationType;

  private function __construct() {
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->cgBezorggebiedContact = $cfsp->getGroupByName('bezorggebied_contact');
    $this->cfBezorggebiedContact = $cfsp->getField('bezorggebied_contact', 'Bezorggebied');

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