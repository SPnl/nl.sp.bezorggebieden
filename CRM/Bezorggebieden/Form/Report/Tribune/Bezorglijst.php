<?php

class CRM_Bezorggebieden_Form_Report_Tribune_Bezorglijst extends CRM_Report_Form {

  protected $_addressField = FALSE;
  
  protected $_add2groupSupported = FALSE;

  protected $_summary = NULL;

  protected $_customGroupExtends = array();
  protected $_customGroupGroupBy = FALSE; 
  
  function __construct() {
    $this->fetchCustom();

    $this->_exposeContactID = false;
    
    $this->_columns = array(
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
      ),
      'bezorg_gebied' => array (
        'alias' => 'cbzg',
        'fields' => array(
          'deliver_area_name' => array(
            'required' => true,
            'title' => 'Bezorggebied',
            'name' => $this->_custom_fields->name['column_name'],
          ),
        ),
      ),
    
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'id' => array(
            'required' => TRUE,
            'default' => true,
            'default' => TRUE,
            'no_display' => false,
          ),
          'display_name' => array(
            'title' => ts('Contact Name'),
            'required' => TRUE,
            'default' => TRUE,
          ),
          
        ),
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
        ),
      ),
      'civicrm_country' => array(
        'dao' => 'CRM_Core_DAO_Country',
        'alias' => 'country',
        'fields' => array(
          'name' => array(
            'title' => 'Land',
            'required' => true,
            'dbAlias' => "if(iso_code = 'NL', 'Nederland', country_civireport.name)",
          ),
        ),
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
    $this->assign('reportTitle', ts('Bezorglijst Tribune'));
    parent::preProcess();
  }

  function from() {
    $bezorggebied_config = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $this->_from = NULL;

    $this->_from = "
         FROM
         civicrm_contact {$this->_aliases['civicrm_contact']}
         INNER JOIN
         civicrm_membership {$this->_aliases['civicrm_membership']}
         ON {$this->_aliases['civicrm_membership']}.contact_id = {$this->_aliases['civicrm_contact']}.id\n 
           LEFT JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']} ON {$this->_aliases['civicrm_membership_status']}.id = {$this->_aliases['civicrm_membership']}.status_id
         
         {$this->_aclFrom}
         
         LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
            ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id 
            AND {$this->_aliases['civicrm_address']}.is_primary = 1\n
         LEFT JOIN civicrm_country {$this->_aliases['civicrm_country']}
            ON {$this->_aliases['civicrm_address']}.country_id = {$this->_aliases['civicrm_country']}.id
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
      )";
    $this->_where .= " AND ({$this->_aliases['civicrm_contact']}.do_not_mail = 0 AND {$this->_aliases['civicrm_contact']}.is_deceased = 0 AND {$this->_aliases['civicrm_contact']}.is_deleted = 0)";
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY 
        `{$this->_aliases['afdeling']}`.`sort_name`, 
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
}
