<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Bezorggebieden_Form_Report_Tribune_Bezorglijst',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Bezorglijst Tribune',
      'description' => 'Bezorglijst Tribune per afdeling en gezorggebied (nl.sp.bezorggebieden)',
      'class_name' => 'CRM_Bezorggebieden_Form_Report_Tribune_Bezorglijst',
      'report_url' => 'nl.sp.bezorggebieden/bezorglijst',
      'component' => '',
    ),
  ),
);
