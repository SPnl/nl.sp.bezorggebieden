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

}
