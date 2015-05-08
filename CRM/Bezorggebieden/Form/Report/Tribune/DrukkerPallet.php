<?php

class CRM_Bezorggebieden_Form_Report_Tribune_DrukkerPallet extends CRM_Report_Form
{

  protected $_add2groupSupported = FALSE;

  function __construct()
  {

    $this->_exposeContactID = false;

    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
            'no_display' => false,
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

  function where() {
    parent::where();
    $config = CRM_Bezorggebieden_Config_TribuneAdres::singleton();
    $this->_where .= " AND {$this->_aliases['civicrm_address']}.location_type_id = '".$config->tribune_adres_id."'";
    $this->_where .= " AND {$this->_aliases['civicrm_pallet_address']}.location_type_id = '".$config->tribune_adres_id."'";
  }

  function modifyColumnHeaders() {
    $this->_columnHeaders['leden'] = array(
      'title' => 'Aantal leden',
    );
    $this->_columnHeaders['extra'] = array(
      'title' => 'Extra tribunes',
    );
    $this->_columnHeaders['total_tribunes'] = array(
      'title' => 'Totaal tribunes',
    );
    $this->_columnHeaders['pakken'] = array(
      'title' => 'Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')',
    );
    $this->_columnHeaders['pakken_los'] = array(
      'title' => 'Los (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')',
    );
    $this->_columnHeaders['pakken_zomer'] = array(
      'title' => 'Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')',
    );
    $this->_columnHeaders['pakken_zomer_los'] = array(
      'title' => 'Los (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')',
    );
  }

  function alterDisplay(&$rows)
  {
    $totaal = 0;
    $pakken = 0;
    $pakken_los = 0;
    $pakken_zomer = 0;
    $pakken_zomer_los = 0;
    $previous_pallet_afdeling = false;
    $newRows = array();
    $i = 0;
    $previousRow = false;
    foreach($rows as $rowNum => $row) {
      $rowI = $rowNum;
      if ($previous_pallet_afdeling === false) {
        $previous_pallet_afdeling = $row['civicrm__pallet_contact_pallet_id'];
      }

      if ($row['civicrm__pallet_contact_pallet_id'] != $previous_pallet_afdeling) {
        foreach($previousRow as $key => $v) {
          $newRows[$i][$key] = '';
        }

        $newRows[$i]['civicrm__pallet_contact_display_name'] = $previousRow['civicrm__pallet_contact_display_name'];
        $newRows[$i]['civicrm__pallet_contact_pallet_id'] = $previousRow['civicrm__pallet_contact_pallet_id'];
        $newRows[$i]['total_tribunes'] = $totaal;
        $newRows[$i]['pakken'] = $pakken;
        $newRows[$i]['pakken_los'] = $pakken_los;
        $newRows[$i]['pakken_zomer'] = $pakken_zomer;
        $newRows[$i]['pakken_zomer_los'] = $pakken_zomer_los;

        $totaal = 0;
        $pakken = 0;
        $pakken_los = 0;
        $pakken_zomer = 0;
        $pakken_zomer_los = 0;
        $i++;
      }


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

      if ($row['civicrm_contact_id']) {
        $afdeling_info = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($row['civicrm_contact_id']);
        $rows[$rowNum]['leden'] = $afdeling_info->getMemberCount();
        $rows[$rowNum]['extra'] = $afdeling_info->getExtraTribunes();
        $rows[$rowNum]['total_tribunes'] = $afdeling_info->getTotalTribunes();
        $rows[$rowNum]['pakken'] = $afdeling_info->getDefaultPackages();
        $rows[$rowNum]['pakken_los'] = $afdeling_info->getDefaultPackagesLos();
        $rows[$rowNum]['pakken_zomer'] = $afdeling_info->getLargePackages();
        $rows[$rowNum]['pakken_zomer_los'] = $afdeling_info->getLargePackagesLos();
      }

      $totaal += $rows[$rowNum]['total_tribunes'];
      $pakken += $rows[$rowNum]['pakken'];
      $pakken_los += $rows[$rowNum]['pakken_los'];
      $pakken_zomer += $rows[$rowNum]['pakken_zomer'];
      $pakken_zomer_los += $rows[$rowNum]['pakken_zomer_los'];


      $newRows[$i] = $rows[$rowNum];
      $i ++;

      $previous_pallet_afdeling = $row['civicrm__pallet_contact_pallet_id'];
      $previousRow = $rows[$rowNum];
    }

    if ($previousRow) {
      foreach($previousRow as $key => $v) {
        $newRows[$i][$key] = '';
      }
      $newRows[$i]['civicrm__pallet_contact_display_name'] = $previousRow['civicrm__pallet_contact_display_name'];
      $newRows[$i]['civicrm__pallet_contact_pallet_id'] = $previousRow['civicrm__pallet_contact_pallet_id'];
      $newRows[$i]['total_tribunes'] = $totaal;
      $newRows[$i]['pakken'] = $pakken;
      $newRows[$i]['pakken_los'] = $pakken_los;
      $newRows[$i]['pakken_zomer'] = $pakken_zomer;
      $newRows[$i]['pakken_zomer_los'] = $pakken_zomer_los;
    }

    $rows = $newRows;
  }
}