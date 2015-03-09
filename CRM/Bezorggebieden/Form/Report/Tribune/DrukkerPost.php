<?php

class CRM_Bezorggebieden_Form_Report_Tribune_DrukkerPost extends CRM_Report_Form
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
            'no_display' => true,
            'no_repeat' => TRUE,
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
        'fields' => array(),
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
        'fields' => array(),
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
    );
    parent::__construct();
  }

  function from()
  {
    $bezorggebied_contact = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $bezorggebied = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
    $this->_from = NULL;

    $this->_from = "
      FROM  civicrm_membership {$this->_aliases['civicrm_membership']}\n
      LEFT JOIN civicrm_membership_status {$this->_aliases['civicrm_membership_status']} ON {$this->_aliases['civicrm_membership_status']}.id = {$this->_aliases['civicrm_membership']}.status_id
      INNER JOIN civicrm_contact {$this->_aliases['civicrm_contact']} ON {$this->_aliases['civicrm_membership']}.contact_id = {$this->_aliases['civicrm_contact']}.id\n
      INNER JOIN civicrm_address {$this->_aliases['civicrm_address']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND {$this->_aliases['civicrm_address']}.is_primary = 1\n
      LEFT JOIN `" . $bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name') . "` ON `{$this->_aliases['civicrm_contact']}`.`id` = `" . $bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name') . "`.`entity_id`
      LEFT JOIN `" . $bezorggebied->getCustomGroup('table_name') . "` ON `{$bezorggebied->getCustomGroup('table_name')}`.`id` = `" . $bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name') . "`.`" . $bezorggebied_contact->getCustomFieldBezorggebied('column_name') . "` \n";
  }

  function where() {
    $bezorggebied_contact = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $bezorggebied = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
    parent::where();
    $this->_where .= " AND (
      ".$bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name').".".$bezorggebied_contact->getCustomFieldBezorggebied('column_name')." IS NULL
      OR ".$bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name').".".$bezorggebied_contact->getCustomFieldBezorggebied('column_name')." <= 0
      OR `{$bezorggebied->getCustomGroup('table_name')}`.`".$bezorggebied->getBezorgingPerField('column_name')."` = 'Post'
      )";
    $this->_where .= " AND ({$this->_aliases['civicrm_contact']}.do_not_mail = 0)";
  }

  function alterDisplay(&$rows) {
    foreach($rows as $rowNum => $row) {
      if (isset($row['civicrm_address_country_id'])) {
        if ($value = $row['civicrm_address_country_id']) {
          $rows[$rowNum]['civicrm_address_country_id'] = CRM_Core_PseudoConstant::country($value, FALSE);
        }
      }
    }
  }

}