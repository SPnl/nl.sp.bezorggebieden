<?php

class CRM_Bezorggebieden_Tokens_Afdeling {

  public static function tokens(&$tokens) {
    $tokens['bezorggebieden']['bezorggebieden.afdelings_berzorggebieden'] = 'Bezorggebieden';
    $tokens['bezorggebieden']['bezorggebieden.palletafdelingen'] = 'Palletafdelingen';
  }

  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null)
  {
    if (!empty($tokens['bezorggebieden'])) {
      if (in_array('afdelings_berzorggebieden', $tokens['bezorggebieden']) || array_key_exists('afdelings_berzorggebieden', $tokens['bezorggebieden'])) {
        $this->bezorggebieden($values, $cids, $job, $tokens, $context);
      }
      if (in_array('palletafdelingen', $tokens['bezorggebieden']) || array_key_exists('palletafdelingen', $tokens['bezorggebieden'])) {
        $this->palletafdelingen($values, $cids, $job, $tokens, $context);
      }
    }
  }

  /**
   *
   * @return CRM_Bezorggebieden_Tokens_Afdeling()
   */
  public static function singleton() {
    if (!self::$singelton) {
      self::$singelton = new CRM_Bezorggebieden_Tokens_Afdeling();
    }
    return self::$singelton;
  }

  protected function palletafdelingen(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $value = '<table><tr><th>Afdeling</th><th>Aantal leden</th><th>Extra tribunes</th><th>Totaal tribunes</th><th>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')</th><th>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')</th></tr>';
      $afdeling_naam = CRM_Contact_BAO_Contact::displayName($cid);
      $value .= '<tr><td>'.$afdeling_naam.'</td><td>'.$afdeling->getMemberCount().'</td><td>'.$afdeling->getExtraTribunes().'</td><td>'.$afdeling->getTotalTribunes().'</td><td>'.$afdeling->getDefaultPackages().'</td><td>'.$afdeling->getLargePackages().'</td></tr>';
      foreach($pallets as $pallet_id => $pallet) {
        $afdeling_naam = CRM_Contact_BAO_Contact::displayName($pallet_id);
        $value .= '<tr><td>'.$afdeling_naam.'</td><td>'.$pallet->getMemberCount().'</td><td>'.$pallet->getExtraTribunes().'</td><td>'.$pallet->getTotalTribunes().'</td><td>'.$pallet->getDefaultPackages().'</td><td>'.$pallet->getLargePackages().'</td></tr>';
      }
      $value .= '</table>';

      $values[$cid]['bezorggebieden.palletafdelingen'] = $value;
    }
  }

  protected function bezorggebieden(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $bezorggebieden = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingBezorggebieden($cid);
      $value = '';
      foreach($bezorggebieden as $b) {
        $value .= '<tr><td>'.$b['naam'].'</td><td>'.$b['range'].'</td><td>'.$b['type'].'</td><td>'.$b['count'].'</td></tr>';
      }
      if (strlen($value)) {
        $value .= '<table><tr><th>Bezorggebied</th><th>Range</th><th>Type</th><th>Aantal tribunes</th></tr>'.$value.'</table>';
      } else {
        $value = 'Geen bezorggebieden';
      }
      $values[$cid]['bezorggebieden.afdelings_berzorggebieden'] = $value;
    }
  }


}