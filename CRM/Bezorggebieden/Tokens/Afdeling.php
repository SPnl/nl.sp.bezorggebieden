<?php

class CRM_Bezorggebieden_Tokens_Afdeling {

  private static $singelton;

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
      $value = '<table><tr><td><strong>Afdeling</strong></td><td><strong>Aantal leden</strong></td><td><strong>Extra tribunes</strong></td><td><strong>Totaal tribunes</strong></td><td><strong>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')</strong></td><td><strong>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')</strong></td></tr>';
      $afdeling_naam = CRM_Contact_BAO_Contact::displayName($cid);
      $value .= '<tr><td>'.$afdeling_naam.'</td><td style="text-align: right">'.$afdeling->getMemberCount().'</td><td style="text-align: right">'.$afdeling->getExtraTribunes().'</td><td style="text-align: right">'.$afdeling->getTotalTribunes().'</td><td style="text-align: right">'.$afdeling->getDefaultPackages().'</td><td style="text-align: right">'.$afdeling->getLargePackages().'</td></tr>';
      foreach($pallets as $pallet_id => $pallet) {
        $afdeling_naam = CRM_Contact_BAO_Contact::displayName($pallet_id);
        $value .= '<tr><td>'.$afdeling_naam.'</td><td style="text-align: right">'.$pallet->getMemberCount().'</td><td style="text-align: right">'.$pallet->getExtraTribunes().'</td><td style="text-align: right">'.$pallet->getTotalTribunes().'</td><td style="text-align: right">'.$pallet->getDefaultPackages().'</td><td style="text-align: right">'.$pallet->getLargePackages().'</td></tr>';
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
        $value .= '<tr><td>'.$b['naam'].'</td><td>'.$b['range'].'</td><td>'.$b['type'].'</td><td style="text-align: right">'.$b['count'].'</td></tr>';
      }
      if (strlen($value)) {
        $value = '<table><tr><td><strong>Bezorggebied</strong></td><td><strong>Range</strong></td><td><strong>Type</strong></td><td><strong>Aantal tribunes</strong></td></tr>'.$value.'</table>';
      } else {
        $value = 'Geen bezorggebieden';
      }
      $values[$cid]['bezorggebieden.afdelings_berzorggebieden'] = $value;
    }
  }


}