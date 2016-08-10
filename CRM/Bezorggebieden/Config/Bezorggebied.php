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
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->cgBezorggebied = $cfsp->getGroupByName('Bezorggebieden');
    $this->naam = $cfsp->getField('Bezorggebieden', 'Bezorggebied_naam');
    $this->start_cijfer_range = $cfsp->getField('Bezorggebieden', 'Start_cijfer_range');
    $this->eind_cijfer_range =  $cfsp->getField('Bezorggebieden', 'Eind_cijfer_range');
    $this->start_letter_range=  $cfsp->getField('Bezorggebieden', 'Start_letter_range');
    $this->eind_letter_range = $cfsp->getField('Bezorggebieden', 'Eind_letter_range');
    $this->bezorging_per = $cfsp->getField('Bezorggebieden', 'Bezorging_per');
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