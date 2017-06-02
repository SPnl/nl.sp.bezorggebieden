<?php


class CRM_Bezorggebieden_Form_Update extends CRM_Core_Form
{

  protected $_membershipType;

  protected $_membershipStatus;

  function buildQuickForm()
  {
    foreach (CRM_Member_PseudoConstant::membershipType() as $id => $Name) {
      $this->_membershipType = $this->addElement('checkbox', "member_membership_type_id[$id]", NULL, $Name);
    }

    foreach (CRM_Member_PseudoConstant::membershipStatus(NULL, NULL, 'label') as $sId => $sName) {
      $this->_membershipStatus = $this->addElement('checkbox', "member_status_id[$sId]", NULL, $sName);
    }

    // add buttons
    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Update bezorggebieden'),
        'isDefault' => TRUE,
      ),
    ));
  }

  function setDefaultValues()
  {
    $config = CRM_Bezorggebieden_Config_TribuneMembershipTypes::singleton();
    foreach($config->getMembershipTypeIds() as $mid) {
      $defaults['member_membership_type_id'][$mid] = true;
    }
    foreach(CRM_Member_BAO_MembershipStatus::getMembershipStatusCurrent() as $status_id) {
      $defaults['member_status_id'][$status_id] = true;
    }
    return $defaults;
  }

  function postProcess() {
    $formValues = $this->exportValues();

    $selector = new CRM_Bezorggebieden_Selector();
    $original_where = $selector->getWhere();
    if (!isset($formValues['member_membership_type_id'])) {
      $formValues['member_membership_type_id'] = array();
    }
    if (!isset($formValues['member_status_id'])) {
      $formValues['member_status_id'] = array();
    }
    $selector->setData(array_keys($formValues['member_membership_type_id']), array_keys($formValues['member_status_id']));
    $selector->store();
    $where = $selector->getWhere();

    $count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM civicrm_membership ".$where);
    $this->assign('found', $count);

    if ($where == $original_where && isset($_POST['continue']) && !empty($_POST['continue'])) {
      $queue = self::getQueue(true);
      //add tasks to queue
      for($i=0; $i<$count; $i = $i + 200) {
        $task = new CRM_Queue_Task(
          array('CRM_Bezorggebieden_Form_Update', 'Update'), //call back method
          array($i, 200, $where),
          'Updated '.$i.' contacts'
        );
        $queue->createItem($task);
      }

      $runner = new CRM_Queue_Runner(array(
        'title' => ts('Bezorggebieden update'), //title fo the queue
        'queue' => $queue, //the queue object
        'errorMode'=> CRM_Queue_Runner::ERROR_ABORT, //abort upon error and keep task in queue
        'onEnd' => array('CRM_Bezorggebieden_Form_Update', 'onEnd'), //method which is called as soon as the queue is finished
        'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'), //go to page after all tasks are finished
      ));

      $runner->runAllViaWeb(); // does not return
    }
  }

  public static function Update(CRM_Queue_TaskContext $ctx, $offset, $limit, $where) {
    $config = CRM_Geostelsel_Config::singleton();

    $sql = "SELECT `civicrm_membership`.`contact_id` as id,
               `g`.`".$config->getAfdelingsField('column_name')."` as `afdeling_id`
            FROM `civicrm_membership`
            LEFT JOIN `".$config->getGeostelselCustomGroup('table_name')."` g ON `g`.`entity_id` = `civicrm_membership`.`contact_id`
            ".$where."
            ORDER BY `civicrm_membership`.`contact_id` ASC
            LIMIT %1, %2";

    $params[1] = array($offset, 'Integer');
    $params[2] = array($limit, 'Integer');

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      CRM_Bezorggebieden_Handler_AutoBezorggebiedLink::updateContact($dao->id, $dao->afdeling_id);
    }

    return true;
  }

  private static function getQueue($reset) {
    return CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => 'BezorggebiedUpdateAddress',
      'reset' => $reset, //do not flush queue upon creation
    ));
  }

  /**
   * Handle the final step of the queue
   */
  public static function onEnd(CRM_Queue_TaskContext $ctx) {
    //set a status message for the user
    CRM_Core_Session::setStatus('All bezorggebieden are updated', 'Bezorggebieden', 'success');
  }
}
