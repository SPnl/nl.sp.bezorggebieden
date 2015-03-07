<?php

class CRM_Bezorggebieden_Config_TribuneAdres {

  private static $singleton;

  public $tribune_adres_id;

  function __construct() {
    $this->tribune_adres_id = CRM_Core_DAO::singleValueQuery("SELECT id from `civicrm_location_type` where `name` = 'Tribuneadres'");
  }

  /**
   * @return CRM_Bezorggebieden_Config_TribuneAdres
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_Bezorggebieden_Config_TribuneAdres();
    }
    return self::$singleton;
  }

}