<?php

require_once(PASTELL_PATH . "/pastell-core/Connecteur.class.php");

/**
 * Classe m�re pour connecteurs de type Signature, destin�s aux usages Berger-Levrault.
 */
abstract class BLSignatureConnecteur extends Connecteur {

    // Attributs de flux, sp�cifiques � ce type de connecteur; 
    // accessibles uniquement par les connecteurs de ce type.
    const ATTR_SIGNATURE_CACHECIRCUITS = 'cache-circuits';
    const ATTR_SIGNATURE_DOSSIERID = 'parapheur_dossierid';
    const ATTR_SIGNATURE_DELETED = 'parapheur_deleted';
    
    public abstract function getListTypes();
    public abstract function getListCircuits($type);
    public abstract function getCircuitDetail($type, $circuit);
    public abstract function sendDocument();
    public abstract function sendHeliosDocument();
    public abstract function sendActeDocument();
    public abstract function docHistorique();
    public abstract function searchHistoriqueLogValidation($histoAll);
    public abstract function searchHistoriqueLogRejet($histoAll);
    public abstract function getSignature();
}
