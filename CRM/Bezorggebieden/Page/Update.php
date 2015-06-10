<?php

require_once 'CRM/Core/Page.php';

class CRM_Bezorggebieden_Page_Update extends CRM_Core_Page {


  function preProcess() {

  }

  function run() {
    $queue = self::getQueue(true);
    //add tasks to queue
    $count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM civicrm_contact");
    for($i=0; $i<$count; $i = $i + 1000) {
      $task = new CRM_Queue_Task(
        array('CRM_Bezorggebieden_Page_Update', 'Update'), //call back method
        array($i, 1000),
        'Updated '.$i.' contacts'
      );
      $queue->createItem($task);
    }

    $runner = new CRM_Queue_Runner(array(
      'title' => ts('Bezorggebieden update'), //title fo the queue
      'queue' => $queue, //the queue object
      'errorMode'=> CRM_Queue_Runner::ERROR_CONTINUE, //abort upon error and keep task in queue
      'onEnd' => array('CRM_Bezorggebieden_Page_Update', 'onEnd'), //method which is called as soon as the queue is finished
      'onEndUrl' => CRM_Utils_System::url('civicrm', 'reset=1'), //go to page after all tasks are finished
    ));

    $runner->runAllViaWeb(); // does not return

    parent::run();
  }

  public static function Update(CRM_Queue_TaskContext $ctx, $offset, $limit) {
    $config = CRM_Geostelsel_Config::singleton();
    $dao = CRM_Core_DAO::executeQuery("SELECT `civicrm_contact`.`id`, `g`.`".$config->getAfdelingsField('column_name')."` as `afdeling_id`, civicrm_address.id as address_id, civicrm_address.postal_code, civicrm_address.country_id
                                        FROM `civicrm_contact`
                                        LEFT JOIN `civicrm_address` ON `civicrm_address`.contact_id = civicrm_contact.id
                                        LEFT JOIN `".$config->getGeostelselCustomGroup('table_name')."` g ON `g`.`entity_id` = `civicrm_contact`.`id`
                                        WHERE `contact_type` = 'Individual'
                                        ORDER BY `civicrm_address`.`postal_code`
                                        LIMIT " . $offset . ", ".$limit);
    while ($dao->fetch()) {
      CRM_Bezorggebieden_Handler_AutoBezorggebiedLink::updateContactByAddressData($dao->id, $dao->address_id, $dao->postal_code, $dao->country_id, $dao->afdeling_id);
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