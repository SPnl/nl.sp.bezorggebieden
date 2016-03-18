<?php

class CRM_Bezorggebieden_Utils_AfdelingTelling {

  const EXTRA_FACTOR = 50;

  const EXTRA_THRESHOLD = 5;

  const EXTRA_DEFAULT = 2;

  const DEFAULT_PER_PACKAGE = 50;

  const LARGE_PER_PACKAGE = 40;


  protected $aantal_leden = 0;

  protected $total_tribunes = 0;

  protected $extra_tribunes = 0;

  protected $default_packages = 0;

  protected $default_packges_los = 0;

  protected $large_packages = 0;

  protected $large_packges_los = 0;

  protected static $afdeling_info = array();

  protected static $afdeling_bezorggebieden = array();

  protected static $afdeling_pallets = array();

  protected function __construct($aantal_leden) {
    $this->aantal_leden = $aantal_leden;

    $extra = 0;
    if ($this->aantal_leden > 0) {
      $extra = (int) ceil($this->aantal_leden / CRM_Bezorggebieden_Utils_AfdelingTelling::EXTRA_FACTOR);
      if ($extra < CRM_Bezorggebieden_Utils_AfdelingTelling::EXTRA_THRESHOLD) {
        $extra = CRM_Bezorggebieden_Utils_AfdelingTelling::EXTRA_THRESHOLD;
      }
    }
    $extra = $extra + CRM_Bezorggebieden_Utils_AfdelingTelling::EXTRA_DEFAULT;
    $this->extra_tribunes = $extra;
    $this->total_tribunes = $this->aantal_leden + $this->extra_tribunes;
    if ($this->total_tribunes > 0) {
      $this->default_packages = (int) floor($this->total_tribunes / CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE);
      $this->default_packges_los = (int) ($this->total_tribunes % CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE);
      $this->large_packages = (int) floor($this->total_tribunes / CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE);
      $this->large_packges_los = (int) ($this->total_tribunes % CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE);
    }
  }

  public function getMemberCount() {
    return $this->aantal_leden;
  }

  public function getExtraTribunes() {
    return $this->extra_tribunes;
  }

  public function getTotalTribunes() {
    return $this->total_tribunes;
  }

  public function getDefaultPackages() {
    return $this->default_packages;
  }

  public function getDefaultPackagesLos() {
    return $this->default_packges_los;
  }

  public function getLargePackages() {
    return $this->large_packages;
  }

  public function getLargePackagesLos() {
    return $this->large_packges_los;
  }

  /**
   * @param $afdeling_id
   * @return CRM_Bezorggebieden_Utils_AfdelingTelling
   */
  public static function getAfdelingTelling($afdeling_id) {
    if (isset(self::$afdeling_info[$afdeling_id])) {
      return self::$afdeling_info[$afdeling_id];
    }

    $config = CRM_Bezorggebieden_Config_TribuneMembershipTypes::singleton();
    $bezorggebied_contact = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $bezorggebied = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
    $sql = "SELECT COUNT(DISTINCT c.id) as total
            FROM civicrm_contact c
            INNER JOIN `civicrm_membership` m on m.contact_id = c.id
            INNER JOIN `".$bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name')."` `b` ON `b`.`entity_id` = c.id
            INNER JOIN `".$bezorggebied->getCustomGroup('table_name')."` `gebied` ON gebied.id = b.`".$bezorggebied_contact->getCustomFieldBezorggebied('column_name')."`
            where
            gebied.entity_id = %1
            AND
            gebied.`".$bezorggebied->getBezorgingPerField('column_name')."` != 'Post'
            AND
            `c`.`do_not_mail` = 0
            AND
            m.status_id IN (".implode(", ",CRM_Member_BAO_MembershipStatus::getMembershipStatusCurrent()).")
            AND
            m.membership_type_id IN (".implode(", ", $config->getMembershipTypeIds()).")
            ";
    $params[1] = array($afdeling_id, 'Integer');
    $total = CRM_Core_DAO::singleValueQuery($sql, $params);

    self::$afdeling_info[$afdeling_id] = new CRM_Bezorggebieden_Utils_AfdelingTelling($total);
    return self::$afdeling_info[$afdeling_id];
  }

  public static function getAfdelingBezorggebieden($afdeling_id) {
    if (isset(self::$afdeling_bezorggebieden[$afdeling_id])) {
      return self::$afdeling_bezorggebieden[$afdeling_id];
    }


    $config = CRM_Bezorggebieden_Config_TribuneMembershipTypes::singleton();
    $bezorggebied = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
    $bezorggebied_contact = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();

    $sql = "SELECT b.*, count(distinct bc.entity_id) as total
            FROM  `".$bezorggebied->getCustomGroup('table_name')."` `b`
            INNER JOIN `".$bezorggebied_contact->getCustomGroupBezorggebiedContact('table_name')."` `bc` ON `b`.`id` = `bc`.`".$bezorggebied_contact->getCustomFieldBezorggebied('column_name')."`
            INNER JOIN `civicrm_membership` m on m.contact_id = bc.entity_id
            INNER JOIN civicrm_contact c ON m.contact_id = c.id
            WHERE
            `b`.`entity_id` = %1
            AND
            `b`.`".$bezorggebied->getBezorgingPerField('column_name')."` != 'Post'
            AND
            `c`.`do_not_mail` = 0
            AND
            m.status_id IN (".implode(", ",CRM_Member_BAO_MembershipStatus::getMembershipStatusCurrent()).")
            AND
            m.membership_type_id IN (".implode(", ", $config->getMembershipTypeIds()).")
            GROUP BY b.id";

    self::$afdeling_bezorggebieden[$afdeling_id] = array();

    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($afdeling_id, 'Integer')));
    $naam_field = $bezorggebied->getNaamField('column_name');
    $start_cijfer = $bezorggebied->getStartCijferRangeField('column_name');
    $start_letter = $bezorggebied->getStartLetterRangeField('column_name');
    $eind_cijfer = $bezorggebied->getEindCijferRangeField('column_name');
    $eind_letter = $bezorggebied->getEindLetterRangeField('column_name');
    $bezorger_per = $bezorggebied->getBezorgingPerField('column_name');
    while($dao->fetch()) {
      $afdeling_info = array(
        'naam' => $dao->$naam_field,
        'count' => $dao->total,
        'range' => $dao->$start_cijfer.' '.$dao->$start_letter.' - '.$dao->$eind_cijfer.' '.$dao->$eind_letter,
        'type' => $dao->$bezorger_per,
      );
      self::$afdeling_bezorggebieden[$afdeling_id][] = $afdeling_info;
    }

    return self::$afdeling_bezorggebieden[$afdeling_id];
  }

  public static function getPalletAfdelingen($afdeling_id) {
    if (isset(self::$afdeling_pallets[$afdeling_id])) {
      self::$afdeling_pallets[$afdeling_id];
    }

    $tribune_adres = CRM_Bezorggebieden_Config_TribuneAdres::singleton();
    $afdeling_pallets[$afdeling_id] = array();

    $sql = "SELECT pallet.contact_id
            FROM `civicrm_address` `master`
            INNER JOIN `civicrm_address` `pallet` ON `master`.id = `pallet`.`master_id`
            WHERE master.contact_id = %1 AND pallet.location_type_id = %2";
    $params[1] = array($afdeling_id, 'Integer');
    $params[2] = array($tribune_adres->tribune_adres_id, 'Integer');
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      self::$afdeling_pallets[$afdeling_id][$dao->contact_id] = self::getAfdelingTelling($dao->contact_id);
    }

    return self::$afdeling_pallets[$afdeling_id];
  }

}