<?php

class CRM_Bezorggebieden_Selector {

  protected $where;

  public function __construct() {
    $this->load();
  }

  public function load() {
    if (isset($_SESSION['CRM_Bezorggebieden_Selector'])) {
      $this->where = $_SESSION['CRM_Bezorggebieden_Selector'];
    }
  }

  public function store() {
    $_SESSION['CRM_Bezorggebieden_Selector'] = $this->where;
  }

  public function setData($membership_types, $status_ids) {
    $this->where = "WHERE 1 ";
    if (is_array($membership_types) && count($membership_types)) {
      $this->where .= " AND `membership_type_id` IN (".implode(",", $membership_types).")";
    }
    if (is_array($status_ids) && count($status_ids)) {
      $this->where .= " AND `status_id` IN (".implode(",", $status_ids).")";
    }
  }

  public function getWhere() {
    return $this->where;
  }

}