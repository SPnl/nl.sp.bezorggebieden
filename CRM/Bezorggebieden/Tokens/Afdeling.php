<?php

class CRM_Bezorggebieden_Tokens_Afdeling {

  private static $singelton;

  public static function tokens(&$tokens) {
    $tokens['bezorggebieden']['bezorggebieden.afdelings_berzorggebieden'] = 'Bezorggebieden';
    $tokens['bezorggebieden']['bezorggebieden.palletafdelingen'] = 'Palletafdelingen';

    $tokens['bezorggebieden']['bezorggebieden.totaal_tribunes'] = 'Totaal tribunes';
    $tokens['bezorggebieden']['bezorggebieden.totaal_pakken'] = 'Pakken';
    $tokens['bezorggebieden']['bezorggebieden.totaal_pakken_large'] = 'Pakken ('.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.' stuks)';
    $tokens['bezorggebieden']['bezorggebieden.losse_tribunes'] = 'Losse tribunes';
    $tokens['bezorggebieden']['bezorggebieden.losse_tribunes_large'] = 'Losse tribunes (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')';
    $tokens['bezorggebieden']['bezorggebieden.totaal_adresstickers'] = 'Aantal adresstickers';
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

  public function tokenValues(&$values, $cids, $job = null, $tokens = array(), $context = null)
  {
    if (!empty($tokens['bezorggebieden'])) {
      if (in_array('afdelings_berzorggebieden', $tokens['bezorggebieden']) || array_key_exists('afdelings_berzorggebieden', $tokens['bezorggebieden'])) {
        $this->bezorggebieden($values, $cids, $job, $tokens, $context);
      }
      if (in_array('palletafdelingen', $tokens['bezorggebieden']) || array_key_exists('palletafdelingen', $tokens['bezorggebieden'])) {
        $this->palletafdelingen($values, $cids, $job, $tokens, $context);
      }
      if (in_array('totaal_tribunes', $tokens['bezorggebieden']) || array_key_exists('totaal_tribunes', $tokens['bezorggebieden'])) {
        $this->totaal_tribunes($values, $cids, $job, $tokens, $context);
      }
      if (in_array('totaal_pakken', $tokens['bezorggebieden']) || array_key_exists('totaal_pakken', $tokens['bezorggebieden'])) {
        $this->totaal_pakken($values, $cids, $job, $tokens, $context);
      }
      if (in_array('totaal_pakken_large', $tokens['bezorggebieden']) || array_key_exists('totaal_pakken_large', $tokens['bezorggebieden'])) {
        $this->totaal_pakken_large($values, $cids, $job, $tokens, $context);
      }
      if (in_array('losse_tribunes', $tokens['bezorggebieden']) || array_key_exists('losse_tribunes', $tokens['bezorggebieden'])) {
        $this->losse_tribunes($values, $cids, $job, $tokens, $context);
      }
      if (in_array('losse_tribunes_large', $tokens['bezorggebieden']) || array_key_exists('losse_tribunes_large', $tokens['bezorggebieden'])) {
        $this->losse_tribunes_large($values, $cids, $job, $tokens, $context);
      }
      if (in_array('totaal_adresstickers', $tokens['bezorggebieden']) || array_key_exists('totaal_adresstickers', $tokens['bezorggebieden'])) {
        $this->totaal_adresstickers($values, $cids, $job, $tokens, $context);
      }
    }
  }

  protected function totaal_adresstickers(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $totaal = $afdeling->getMemberCount();
      foreach($pallets as $pallet) {
        $totaal = $totaal + $pallet->getMemberCount();
      }
      $values[$cid]['bezorggebieden.totaal_adresstickers'] = $totaal;
    }
  }

  protected function losse_tribunes(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $totaal = $afdeling->getDefaultPackagesLos();
      foreach($pallets as $pallet) {
        $totaal = $totaal + $pallet->getDefaultPackagesLos();
      }
      $values[$cid]['bezorggebieden.losse_tribunes'] = $totaal;
    }
  }

  protected function losse_tribunes_large(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $totaal = $afdeling->getLargePackagesLos();
      foreach($pallets as $pallet) {
        $totaal = $totaal + $pallet->getLargePackagesLos();
      }
      $values[$cid]['bezorggebieden.losse_tribunes_large'] = $totaal;
    }
  }

  protected function totaal_pakken_large(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $totaal = $afdeling->getLargePackages();
      foreach($pallets as $pallet) {
        $totaal = $totaal + $pallet->getLargePackages();
      }
      $values[$cid]['bezorggebieden.totaal_pakken_large'] = $totaal;
    }
  }

  protected function totaal_pakken(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $totaal = $afdeling->getDefaultPackages();
      foreach($pallets as $pallet) {
        $totaal = $totaal + $pallet->getDefaultPackages();
      }
      $values[$cid]['bezorggebieden.totaal_pakken'] = $totaal;
    }
  }

  protected function totaal_tribunes(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $totaal = $afdeling->getTotalTribunes();
      foreach($pallets as $pallet) {
        $totaal = $totaal + $pallet->getTotalTribunes();
      }
      $values[$cid]['bezorggebieden.totaal_tribunes'] = $totaal;
    }
  }

  protected function palletafdelingen(&$values, $cids, $job = null, $tokens = array(), $context = null) {
    foreach($cids as $cid) {
      $pallets = CRM_Bezorggebieden_Utils_AfdelingTelling::getPalletAfdelingen($cid);
      $afdeling = CRM_Bezorggebieden_Utils_AfdelingTelling::getAfdelingTelling($cid);
      $value = '<table><tr><td><strong>Afdeling</strong></td><td><strong>Aantal leden</strong></td><td><strong>Extra tribunes</strong></td><td><strong>Totaal tribunes</strong></td><td><strong>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')</strong></td><td><strong>Los (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::DEFAULT_PER_PACKAGE.')</strong></td><td><strong>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')</strong></td><td><strong>Pakken (per '.CRM_Bezorggebieden_Utils_AfdelingTelling::LARGE_PER_PACKAGE.')</strong></td></tr>';
      $afdeling_naam = CRM_Contact_BAO_Contact::displayName($cid);
      $value .= '<tr><td>'.$afdeling_naam.'</td><td style="text-align: right">'.$afdeling->getMemberCount().'</td><td style="text-align: right">'.$afdeling->getExtraTribunes().'</td><td style="text-align: right">'.$afdeling->getTotalTribunes().'</td><td style="text-align: right">'.$afdeling->getDefaultPackages().'</td><td style="text-align: right">'.$afdeling->getDefaultPackagesLos().'</td><td style="text-align: right">'.$afdeling->getLargePackages().'</td><td style="text-align: right">'.$afdeling->getLargePackagesLos().'</td></tr>';
      foreach($pallets as $pallet_id => $pallet) {
        $afdeling_naam = CRM_Contact_BAO_Contact::displayName($pallet_id);
        $value .= '<tr><td>'.$afdeling_naam.'</td><td style="text-align: right">'.$pallet->getMemberCount().'</td><td style="text-align: right">'.$pallet->getExtraTribunes().'</td><td style="text-align: right">'.$pallet->getTotalTribunes().'</td><td style="text-align: right">'.$pallet->getDefaultPackages().'</td><td style="text-align: right">'.$pallet->getDefaultPackagesLos().'</td><td style="text-align: right">'.$pallet->getLargePackages().'</td><td style="text-align: right">'.$pallet->getLargePackagesLos().'</td></tr>';
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