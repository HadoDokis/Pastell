<?php

require_once(PASTELL_PATH . "/connecteur-type/TdtConnecteur.class.php");

/**
 * Classe m�re pour connecteurs de type TdT, destin�s aux usages Berger-Levrault.
 */
abstract class BLTdtConnecteur extends TdtConnecteur {

    const KEY_INFO_FILESIZE = 'filesize';
    const ATTR_FICHIER_PRINCIPAL = 'document_principal';
    const ATTR_VISUEL_PDF = 'visuel_pdf';
    const ATTR_PJ = 'piece_jointe';

    // Attributs de flux, sp�cifiques � ce type de connecteur;
    // accessibles uniquement par les connecteurs de ce type.
    const ATTR_TDT_TRANSACTION_ID = 'tdt_transaction_id';
    const ATTR_TRANSACTION_ANNULATION_ID = "tdt_transaction_annulation_id";

    public function isUsageActe() {
        $connecteur_data = $this->getConnecteurConfig()->getRawData();
        if (!isset($connecteur_data['usage_acte']) || $connecteur_data['usage_acte']) {
            return true;
        }
        return false;
    }

}
