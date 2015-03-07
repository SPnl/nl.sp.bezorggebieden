<?php

class CRM_Bezorggebieden_Config_Bezorggebied {

  private static $singleton;

  private $cgBezorggebied;

  private $naam;

  private $start_cijfer_range;

  private $eind_cijfer_range;

  private $start_letter_range;

  private $eind_letter_range;

  private $bezorging_per;

  private function __construct() {
    $this->cgBezorggebied = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Bezorggebieden'));
    $this->naam = civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorggebied_naam', 'custom_group_id' => $this->cgBezorggebied['id']));
    $this->start_cijfer_range = civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_cijfer_range', 'custom_group_id' => $this->cgBezorggebied['id']));
    $this->eind_cijfer_range = civicrm_api3('CustomField', 'getsingle', array('name' => 'Eind_cijfer_range', 'custom_group_id' => $this->cgBezorggebied['id']));
    $this->start_letter_range= civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_letter_range', 'custom_group_id' => $this->cgBezorggebied['id']));
    $this->eind_letter_range = civicrm_api3('CustomField', 'getsingle', array('name' => 'Eind_letter_range', 'custom_group_id' => $this->cgBezorggebied['id']));
    $this->bezorging_per = civicrm_api3('CustomField', 'getsingle', array('name' => 'Bezorging_per', 'custom_group_id' => $this->cgBezorggebied['id']));
  }

  /**
   * @return CRM_Bezorggebieden_Config_Bezorggebied
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Bezorggebieden_Config_Bezorggebied();
    }
    return self::$singleton;
  }

  public function getCustomGroup($key='id') {
    return $this->cgBezorggebied[$key];
  }

  public function getNaamField($key) {
    return $this->naam[$key];
  }

  public function getStartCijferRangeField($key) {
    return $this->start_cijfer_range[$key];
  }

  public function getEindCijferRangeField($key) {
    return $this->eind_cijfer_range[$key];
  }

  public function getStartLetterRangeField($key) {
    return $this->start_letter_range[$key];
  }

  public function getEindLetterRangeField($key) {
    return $this->eind_letter_range[$key];
  }

  public function getBezorgingPerField($key) {
    return $this->bezorging_per[$key];
  }

}