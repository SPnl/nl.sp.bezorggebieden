<?php

class CRM_Bezorggebieden_Form_Report_Tribune_DrukkerPallet extends CRM_Report_Form
{

  function __construct()
  {
    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
            'no_display' => true,
          ),
          'display_name' => array(
            'title' => ts('Contact Name'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_address' => array(
        'dao' => 'CRM_Core_DAO_Address',
        'fields' => array(
        ),
        'filters' => array(
          'location_type_id' => array(
            'title' => ts('Location type'),
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->locationTypes(),
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm__pallet_contact' => array(
        'alias' => 'civicrm__pallet_contact',
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'pallet_id' => array(
            'required' => TRUE,
            'no_display' => TRUE,
            'name' => 'id',
          ),
          'pallet_display_name' => array(
            'title' => ts('Pallet afdeling'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
            'name' => 'display_name',
          ),
        ),
        'order_bys' => array(
          'display_name' => array(
            'title' => ts('Pallet afdeling'),
            'section' => true,
          )
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_pallet_address' => array(
        'alias' => 'civicrm_pallet_address',
        'dao' => 'CRM_Core_DAO_Address',
        'fields' => array(
          'street_address' => array(
            'required' => true,
          ),
          'postal_code' => array(
            'required' => true,
          ),
          'city' => array(
            'required' => true,
          ),
          'country_id' => array('title' => ts('Country'), 'required' => true),
        ),
        'grouping' => 'contact-fields',
      ),
    );
    parent::__construct();
  }

  function locationTypes() {
    $options = array();
    $sql = "SELECT * FROM `civicrm_location_type` ORDER by `display_name`";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while($dao->fetch()) {
      $options[$dao->id] = $dao->display_name;
    }
    return $options;
  }

  function from() {
    $this->_from = "
      FROM civicrm_contact {$this->_aliases['civicrm_contact']}\n
      INNER JOIN civicrm_address {$this->_aliases['civicrm_address']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id
      LEFT JOIN civicrm_address {$this->_aliases['civicrm_pallet_address']}  ON (
        ({$this->_aliases['civicrm_address']}.master_id IS NOT NULL AND {$this->_aliases['civicrm_pallet_address']}.id = {$this->_aliases['civicrm_address']}.master_id)
        XOR
        ({$this->_aliases['civicrm_address']}.master_id IS NULL AND {$this->_aliases['civicrm_address']}.id = {$this->_aliases['civicrm_pallet_address']}.id)
      )
      LEFT JOIN civicrm_contact {$this->_aliases['civicrm__pallet_contact']} ON {$this->_aliases['civicrm_pallet_address']}.contact_id = {$this->_aliases['civicrm__pallet_contact']}.id";
  }

  function buildQuery($applyLimit=true) {
    $sql = parent::buildQuery($applyLimit);
    //echo $sql; exit();
    return $sql;
  }

  function modifyColumnHeaders() {
    $this->_columnHeaders['leden'] = array(
      'title' => 'Aantal leden',
    );
    $this->_columnHeaders['extra'] = array(
      'title' => 'Extra tribunes',
    );
    $this->_columnHeaders['total'] = array(
      'title' => 'Totaal tribunes',
    );
    $this->_columnHeaders['pakken'] = array(
      'title' => 'Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')',
    );
    $this->_columnHeaders['pakken_zomer'] = array(
      'title' => 'Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')',
    );
  }

  function alterDisplay(&$rows)
  {
    foreach($rows as $rowNum => $row) {
      if (isset($row['civicrm_address_country_id'])) {
        if ($value = $row['civicrm_address_country_id']) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
      }
      if (isset($row['civicrm_pallet_address_country_id'])) {
        if ($value = $row['civicrm_pallet_address_country_id']) {
          $rows[$rowNum]['civicrm_pallet_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
      }

      $afdeling_info = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($row['civicrm_contact_id']);
      $rows[$rowNum]['leden'] = $afdeling_info->getMemberCount();
      $rows[$rowNum]['extra'] = $afdeling_info->getExtraTribunes();
      $rows[$rowNum]['total'] = $afdeling_info->getTotalTribunes();
      $rows[$rowNum]['pakken'] = $afdeling_info->getDefaultPackages();
      $rows[$rowNum]['pakken_zomer'] = $afdeling_info->getLargePackages();
    }
  }
}