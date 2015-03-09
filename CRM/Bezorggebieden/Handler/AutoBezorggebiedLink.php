<?php

class CRM_Bezorggebieden_Handler_AutoBezorggebiedLink {

  public static function updateContact($contact_id, $afdeling_id=null) {
    $value = 0; //per post

    //determine bezorggebied for contact

    try {
      $address = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_address` WHERE `contact_id` = %1 AND is_primary = 1", array(
        1 => array($contact_id, 'Integer'),
      ));

      if (!$address->fetch()) {
        $value = 0;
      } else {
        $value = self::determinBezorggebied($address->postal_code, $address->country_id, $afdeling_id);
      }
    } catch (Exception $e) {
      return $value;
    }

    self::saveBezorggebied($contact_id, $value);
  }

  public static function updateContactByAddressData($contact_id, $address_id, $postal_code, $country_id, $afdeling_id=null) {
    $value = 0; //per post
    //determine bezorggebied for contact
    if ($address_id) {
      $value = self::determinBezorggebied($postal_code, $country_id, $afdeling_id);
    }
    self::saveBezorggebied($contact_id, $value);
  }

  private static function saveBezorggebied($contact_id, $value) {
    $config = CRM_Bezorggebieden_Config_BezorggebiedContact::singleton();
    $field = 'custom_'.$config->getCustomFieldBezorggebied('id');
    civicrm_api3('Contact', 'create', array(
      'id' => $contact_id,
      $field => $value,
    ));
  }

  private static function determinBezorggebied($postal_code, $country_id, $afdeling_id=null) {
    $value = 0;
    try {
      //determine if country is netherlands
      if (empty($country_id)) {
        return $value;
      }
      if ($country_id != 1152) {
        return $value;
      }
      list($postcode_4pp, $postcode_2pp) = self::validatePostcode($postal_code);
    } catch (Exception $e) {
      return $value;
    }

    //address is a valid address
    //find the postcode range
    $config = CRM_Bezorggebieden_Config_Bezorggebied::singleton();
    $start_cijfer = $config->getStartCijferRangeField('column_name');
    $eind_cijfer = $config->getEindCijferRangeField('column_name');
    $start_letter = $config->getStartLetterRangeField('column_name');
    $eind_letter = $config->getEindLetterRangeField('column_name');

    $params[1] = array($postcode_4pp, 'Integer');
    $sql = "SELECT `b`.*, (`b`.`".$eind_cijfer."` - `b`.`".$start_cijfer."`) AS `verschil`";
    if (!empty($afdeling_id)) {
      $sql .= ", IF (`b`.`entity_id` = %2, 1, 0) AS `afdeling_match`";
      $params[2] = array($afdeling_id, 'Integer');
    }
    $sql .= "
            FROM `".$config->getCustomGroup('table_name')."` `b`
            WHERE `b`.`".$config->getStartCijferRangeField('column_name')."` <= %1
            AND `b`.`".$config->getEindCijferRangeField('column_name')."` >= %1
            ORDER BY ";
    if (!empty($afdeling_id)) {
      $sql .= " `afdeling_match`,";
    }
    $sql .= " `verschil`, `b`.`".$start_cijfer."`, `b`.`".$eind_cijfer."`";
    
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      //check if this postcode range is really the right postcode range
      if (self::checkPostcodeRange($postcode_4pp, $postcode_2pp, $dao->$start_cijfer, $dao->$eind_cijfer, $dao->$start_letter, $dao->$eind_letter)) {
        return $dao->id;
      }
    }
    return $value;
  }

  private static function checkPostcodeRange($cijfer, $letter, $start_cijfer, $eind_cijfer, $start_letter, $eind_letter) {
    if ($cijfer > $start_cijfer && $cijfer < $eind_cijfer) {
      return true;
    }
    if ($start_cijfer == $eind_cijfer) {
      //compare letter between start letter and eind letter
      if (strcasecmp($letter, $start_letter) >= 0 && strcasecmp($letter, $eind_letter) <= 0) {
        return true;
      }
    } elseif ($cijfer == $start_cijfer && strcasecmp($letter, $start_letter) >= 0 && strcasecmp($letter, 'ZZ') <= 0) {
      return true;
    }  elseif ($cijfer == $eind_cijfer && strcasecmp($letter, 'AA') >= 0 && strcasecmp($letter, $eind_letter) <= 0) {
      return true;
    }
    return false;
  }

  private static function validatePostcode($postcode)
  {
    $postcode = preg_replace('/[^\da-z]/i', '', $postcode);
    $postcode_4pp = substr($postcode, 0, 4); //select the four digist
    $postcode_2pp = substr($postcode, 4, 2); //select the 2 letters
    if (!strlen($postcode_4pp) == 4 || !is_numeric($postcode_4pp)) {
      throw new Exception('Not a valid postcode');
    }
    if (!strlen($postcode_2pp) == 2) {
      throw new Exception('Not a valid postcode');
    }
    return array($postcode_4pp, $postcode_2pp);
  }
}