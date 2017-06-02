<?php

class CRM_Bezorggebieden_Form_Report_Tribune_Bezorglijst extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_csvSupported = FALSE;
  protected $_add2groupSupported = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE;

  function __construct() {
    $this->fetchCustom();

    $this->_exposeContactID = false;

    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'required' => TRUE,
            'default' => TRUE,
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
      'civicrm_membership' => array(
        'dao' => 'CRM_Member_DAO_Membership',
        'fields' => array(
        ),
        'filters' => array(
          'join_date' => array(
            'operatorType' => CRM_Report_Form::OP_DATE,
            'pseudofield' => true,
          ),
          'tid' => array(
            'name' => 'membership_type_id',
            'title' => ts('Membership Types'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Member_PseudoConstant::membershipType(),
          ),
        ),
        'grouping' => 'member-fields',
      ),
      'civicrm_membership_status' => array(
        'dao' => 'CRM_Member_DAO_MembershipStatus',
        'alias' => 'mem_status',
        'fields' => array(
        ),
        'filters' => array(
          'sid' => array(
            'name' => 'id',
            'title' => ts('Status'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label'),
          ),
        ),
        'grouping' => 'member-fields',
      ),
      'afdeling' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'alias' => 'afdeling',
        'fields' => array(
          'afdeling' => array(
            'required' => true,
            'title' => 'Afdeling',
            'default' => true,
            'name' => 'display_name',
          ),
          'afdeling_id' => array(
            'required' => true,
            'title' => 'Afdeling ID',
            'default' => true,
            'name' => 'id',
            'no_display' => true,
          )
        ),
        'grouping' => 'afdeling-fields',
      ),
      'bezorg_gebied' => array (
        'alias' => 'cbzg',
        'fields' => array(
          'deliver_area_name' => array(
            'required' => true,
            'title' => 'Bezorggebied',
            'name' => $this->_custom_fields->name['column_name'],
          ),
          'deliver_per_post' => array(
            'required' => true,
            'title' => 'Per post',
            'name' => $this->_custom_fields->per_post['column_name'],
          ),
        )
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  protected function fetchCustom() {
    $cfsp = CRM_Spgeneric_CustomField::singleton();
    $this->_custom_fields = new stdClass;
    $this->_custom_fields->group = $cfsp->getGroupByName('Bezorggebieden');
    $this->_custom_fields->name = $cfsp->getField('Bezorggebieden', 'Bezorggebied_naam');
    $this->_custom_fields->start_cijfer_range = $cfsp->getField('Bezorggebieden', 'Start_cijfer_range');
    $this->_custom_fields->eind_cijfer_range = $cfsp->getField('Bezorggebieden', 'Eind_cijfer_range');
    $this->_custom_fields->start_letter_range = $cfsp->getField('Bezorggebieden', 'Start_letter_range');
    $this->_custom_fields->eind_letter_range = $cfsp->getField('Bezorggebieden', 'End_letter_range');
    $this->_custom_fields->per_post = $cfsp->getField('Bezorggebieden', 'Bezorging_per');
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Bezorglijst TRIBUNE'));
    parent::preProcess();
  }

  function from() {
    $bezorggebied_config = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $this->_from = NULL;

    $this->_from = "
         FROM  civicrm_membership {$this->_aliases['civicrm_membership']}\n
           LEFT JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']} ON {$this->_aliases['civicrm_membership_status']}.id = {$this->_aliases['civicrm_membership']}.status_id
         INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']} ON {$this->_aliases['civicrm_membership']}.contact_id = {$this->_aliases['civicrm_contact']}.id\n 
         {$this->_aclFrom}
         
         LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
            ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id 
            AND {$this->_aliases['civicrm_address']}.is_primary = 1\n
         LEFT JOIN `".$bezorggebied_config->getCustomGroupBezorggebiedContact('table_name')."` ON `{$this->_aliases['civicrm_contact']}`.`id` = `".$bezorggebied_config->getCustomGroupBezorggebiedContact('table_name')."`.`entity_id`
         LEFT JOIN `".$this->_custom_fields->group['table_name']."` `{$this->_aliases['bezorg_gebied']}` ON `{$this->_aliases['bezorg_gebied']}`.`id` = `".$bezorggebied_config->getCustomGroupBezorggebiedContact('table_name')."`.`".$bezorggebied_config->getCustomFieldBezorggebied('column_name')."` \n
          LEFT JOIN `civicrm_contact` {$this->_aliases['afdeling']} ON {$this->_aliases['bezorg_gebied']}.entity_id = {$this->_aliases['afdeling']}.id\n
            ";
  }

  function where() {
    parent::where();

    $bezorggebied_config = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $this->_where .= " AND (
      ".$bezorggebied_config->getCustomGroupBezorggebiedContact('table_name').".".$bezorggebied_config->getCustomFieldBezorggebied('column_name')." IS NOT NULL
      AND ".$bezorggebied_config->getCustomGroupBezorggebiedContact('table_name').".".$bezorggebied_config->getCustomFieldBezorggebied('column_name')." > 0
      AND `{$this->_aliases['bezorg_gebied']}`.`{$this->_custom_fields->per_post['column_name']}` = 'Bezorger'
      )
      AND {$this->_aliases['civicrm_membership']}.membership_type_id in (4,5,13)
      ";
    $this->_where .= " AND ({$this->_aliases['civicrm_contact']}.do_not_mail = 0)";
  }

  function groupBy() {
    $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_contact']}.id";
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY 
        `{$this->_aliases['afdeling']}`.`sort_name`, 
        `{$this->_aliases['bezorg_gebied']}`.`{$this->_custom_fields->per_post['column_name']}`,
        `{$this->_aliases['bezorg_gebied']}`.`{$this->_custom_fields->name['column_name']}`,
        `{$this->_aliases['civicrm_address']}`.`postal_code` ,        
        `{$this->_aliases['civicrm_contact']}`.`sort_name`";
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    $sql = $this->buildQuery(TRUE);
//echo $sql; exit;
    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    $per_post_options = CRM_Core_BAO_OptionValue::getOptionValuesAssocArray($this->_custom_fields->per_post['option_group_id']);
    // custom code to alter rows
    $entryFound = FALSE;
    $checkList = array();
    foreach ($rows as $rowNum => $row) {
      if (array_key_exists('civicrm_address_state_province_id', $row)) {
        if ($value = $row['civicrm_address_state_province_id']) {
          $rows[$rowNum]['civicrm_address_state_province_id'] = CRM_Core_PseudoConstant::stateProvince($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_address_country_id', $row)) {
        if ($value = $row['civicrm_address_country_id']) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
        $entryFound = TRUE;
      }

      if (array_key_exists('civicrm_contact_display_name', $row) &&
        $rows[$rowNum]['civicrm_contact_display_name'] &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_display_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
      }

      if (array_key_exists('bezorg_gebied_deliver_per_post', $row) &&
        isset($per_post_options[$row['bezorg_gebied_deliver_per_post']])
      ) {
        $rows[$rowNum]['bezorg_gebied_deliver_per_post'] = $per_post_options[$row['bezorg_gebied_deliver_per_post']];
        $entryFound = TRUE;
      }

      if (!$entryFound) {
        break;
      }
    }
  }

}
