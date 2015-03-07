<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 =>
    array (
      'name' => 'Cron:BezorggebiedContact.update',
      'entity' => 'Job',
      'params' =>
        array (
          'version' => 3,
          'name' => 'Call BezorggebiedContact.update API',
          'description' => 'Deze job zorgt ervoor dat de bezorggebieden bij een contact gevuld worden op basis van postcode',
          'run_frequency' => 'Always',
          'api_entity' => 'BezorggebiedContact',
          'api_action' => 'update',
          'parameters' => '',
          'is_active' => '1',
        ),
    ),
);