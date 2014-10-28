<?php

require_once(PASTELL_PATH . '/bl-core-extension/module/BLFlux.class.php');

/**
 * Classe de traitements g�n�riques sur flux DSN ( D�claration sociale nominative ), destin�s aux usages Berger-Levrault.
 */

class BLDsnFlux extends BLFlux {
    // Attributs sp�cifiques � ce flux, accessibles par les actions de ce flux 
    // et par les connecteurs
    const ATTR_DOCUMENT_DSN = 'document_dsn';
    const ATTR_IDENTIFIANT_DSN = 'identifiant_dsn';
    const ATTR_LISTE_RETOUR = 'liste_retour_dsn';
    const ATTR_FICHIER_RETOUR = 'fichier_retour_dsn';
    
    const ATTR_DECLARANT_NOM = 'declarant_nom';
    const ATTR_DECLARANT_PRENOM = 'declarant_prenom';
    const ATTR_SIRET = 'siret';
    const ATTR_REGIME = 'regime';
    
}

