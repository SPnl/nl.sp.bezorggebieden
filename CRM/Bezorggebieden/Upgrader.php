<?php

/**
 * Collection of upgrade steps
 */
class CRM_Bezorggebieden_Upgrader extends CRM_Bezorggebieden_Upgrader_Base {

  public function upgrade_1001() {
    $this->executeCustomDataFile('xml/contact_bezorggebied.xml');
    return true;
  }

  public function install() {
    $this->executeCustomDataFile('xml/contact_bezorggebied.xml');
  }

  public function upgrade_1002() {
    $count = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_contact where contact_type = "Individual"');
    for ($startId = 0; $startId <= $count; $startId += 250) {
      $title = ts('Correct addresses (%1 / %2)', array(
        1 => $startId,
        2 => $count,
      ));
      $this->addTask($title, 'update_address');
    }

    civicrm_api3('BezorggebiedContact', 'update', array('force' => 1));

    return true;
  }

  public function upgrade_1003() {
    $count = CRM_Core_DAO::singleValueQuery('SELECT count(*) FROM civicrm_contact where contact_type = "Individual"');
    for ($startId = 0; $startId <= $count; $startId += 4000) {
      $title = ts('Update bezorggebied (%1 / %2)', array(
        1 => $startId,
        2 => $count,
      ));
      $this->addTask($title, 'update_address');
    }

    civicrm_api3('BezorggebiedContact', 'update', array('force' => 1));

    return true;
  }

  public static function update_address() {
    civicrm_api3('BezorggebiedContact', 'update', array());

    return true;
  }

}
