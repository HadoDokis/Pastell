<?php

/**
 * Script de mise � jour des donn�es, � ex�cuter lors de la mise � jour de la
 * version de l'application.
 * 
 * Ajout des droits sur le document acteadministratifbl aux diff�rents r�les
 * Ajout des connecteurs globaux.
 */
require_once( __DIR__ . "/../../web/init.php");
require_once( __DIR__ . "/../batch/BLBatch.class.php");

// Constantes
$libelleCollectiviteASupprimer='_A Supprimer';

class BLScriptUpdate extends BLBatch {

    function deleteConnecteur($id_ce, $name) {
        global $objectInstancier;
        $this->traceln('Suppression du connecteur ' . $name);
        // Contournement de l'exception si le .yml de d�finition n'existe plus
        try {
            $objectInstancier->ConnecteurControler->delete($id_ce);
        } catch (Exception $ex) {
            $objectInstancier->ConnecteurEntiteSQL->delete($id_ce);
            $donneesFormulaire = new DonneesFormulaire(WORKSPACE_PATH . "/$id_ce.yml", new Formulaire(array()));
            $donneesFormulaire->delete();
        }
    }

    function deleteConnecteurGlobal($name, $type) {
        global $objectInstancier;
        $id_ce = $objectInstancier->ConnecteurEntiteSQL->getGlobal($name);
        if ($id_ce) {
            if ($objectInstancier->FluxEntiteSQL->isUsed($id_ce)) {
                $this->traceln('Suppression de l\'association du connecteur global ' . $name . ' au flux global');
                $objectInstancier->FluxEntiteSQL->deleteConnecteur(0, null, $type);
            }
            $this->deleteConnecteur($id_ce, 'global ' . $name);
        } else {
            $this->traceln('Connecteur global ' . $name . ' d�j� supprim�');
        }
    }

    function createConnecteurGlobal($name, $type) {
        global $objectInstancier;
        $id_ce = $objectInstancier->ConnecteurEntiteSQL->getGlobal($name);
        if (!$id_ce) {
            $this->traceln('Cr�ation du connecteur global ' . $name);
            $id_ce = $objectInstancier->ConnecteurControler->nouveau(0, $name, $name . ' global');
        } else {
            $this->traceln('Connecteur global ' . $name . ' d�j� cr��');
        }
        if (!$objectInstancier->FluxEntiteSQL->isUsed($id_ce)) {
            $this->traceln('Association du connecteur global ' . $name . ' au flux global');
            $id_fe = $objectInstancier->FluxControler->editionModif(0, null, $type, $id_ce);
        }
    }

}

$blscript = new BLScriptUpdate();
/////////////////////////////////////////////////
// Ajout des droits acteadministratifbl aux r�les  
/////////////////////////////////////////////////
$blscript->traceln('Ajout des droits acteadministratifbl aux r�les : ');
$roleSQLClass = $objectInstancier->RoleSQL;
$sql = "SELECT count(*) FROM role_droit WHERE role=? AND droit=?";

$ajoutRole = false;
//R�le AdminEntite
if (!$roleSQLClass->queryOne($sql, "adminEntite", "acteadministratifbl:lecture")) {
    $blscript->traceln('  - adminEntite');
    $roleSQLClass->addDroit('adminEntite', 'acteadministratifbl:lecture');
    $roleSQLClass->addDroit('adminEntite', 'acteadministratifbl:edition');
    $ajoutRole = true;
}


//R�le adminDocument
if (!$roleSQLClass->queryOne($sql, "adminDocument", "acteadministratifbl:lecture")) {
    $blscript->traceln('  - adminDocument');
    $roleSQLClass->addDroit('adminDocument', 'acteadministratifbl:lecture');
    $roleSQLClass->addDroit('adminDocument', 'acteadministratifbl:edition');
    $ajoutRole = true;
}
//R�le apiDocument
if (!$roleSQLClass->queryOne($sql, "apiDocument", "acteadministratifbl:lecture")) {
    $blscript->traceln('  - apiDocument');
    $roleSQLClass->addDroit('apiDocument', 'acteadministratifbl:lecture');
    $roleSQLClass->addDroit('apiDocument', 'acteadministratifbl:edition');
    $ajoutRole = true;
}

if (!$ajoutRole) {
    $blscript->traceln("  Traitement d�j� effectu�");
}

////////////////////////////////////////////////////////////////
// Correction des donn�es des connecteurs TdT
//    - alimentation des attributs usage_helios et usage_acte
////////////////////////////////////////////////////////////////
$blscript->traceln();
$blscript->traceln("Correction des donn�es des connecteurs TdT : alimentation des attributs usage_helios et usage_acte");
$all_entite = $objectInstancier->EntiteSQL->getAll();
foreach ($all_entite as $entite) {
    $blscript->traceln("  Traitement de l'entit� : " . $entite['denomination']);
    $connecteur_acte_tdt = $objectInstancier->FLuxEntiteSQL->getConnecteur($entite['id_e'], 'acteadministratifbl', 'TdT');
    if ($connecteur_acte_tdt) {
        $donneesFormulaire_acte = $objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($connecteur_acte_tdt['id_ce']);
        $connecteur_acte_data = $donneesFormulaire_acte->getRawData();
        if (!isset($connecteur_acte_data['usage_acte'])) {
            $donneesFormulaire_acte->setData('usage_helios', 'false');
            $donneesFormulaire_acte->setData('usage_acte', 'true');
            $blscript->traceln("    Connecteur : " . $connecteur_acte_tdt['libelle'] . " modifi�.");
        } else {
            $blscript->traceln("    Connecteur : " . $connecteur_acte_tdt['libelle'] . " d�j� trait�.");
        }
    }

    $connecteur_helios_tdt = $objectInstancier->FLuxEntiteSQL->getConnecteur($entite['id_e'], 'pesbl', 'TdT');
    if ($connecteur_helios_tdt) {
        $donneesFormulaire_helios = $objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($connecteur_helios_tdt['id_ce']);
        $connecteur_helios_data = $donneesFormulaire_helios->getRawData();
        if (!isset($connecteur_helios_data['usage_helios'])) {
            $donneesFormulaire_helios->setData('usage_helios', 'true');
            $donneesFormulaire_helios->setData('usage_acte', 'false');
            $blscript->traceln("    Connecteur : " . $connecteur_helios_tdt['libelle'] . " modifi�.");
        } else {
            $blscript->traceln("    Connecteur : " . $connecteur_helios_tdt['libelle'] . " d�j� traite.");
        }
    }
}

// Supprimer connecteur global iparapheurbl
$blscript->deleteConnecteurGlobal('iparapheurbl', 'signature');

// Supprimer connecteur global s2lowbl
$blscript->deleteConnecteurGlobal('s2lowbl', 'tdt');

// Cr�er connecteur global pour connecteurs
$blscript->createConnecteurGlobal('connecteurbl', 'connecteur');

// Cr�er connecteur global pour flux
$blscript->createConnecteurGlobal('fluxbl', 'flux');

// Cr�er connecteur global pour type de service signature
$blscript->createConnecteurGlobal('signaturebl', 'signature');

// Cr�er connecteur global pour type de service TdT
$blscript->createConnecteurGlobal('tdtbl', 'tdt');

// m41014 (m�moriser la cause de la suspension d'un connecteur) : le format
// de l'attribut acces_suspendu change. 
$blscript->traceln('Mise � jour du format de l\'attribut acces_suspendu des connecteurs suspendus');
$listeConnecteurEntite = $objectInstancier->ConnecteurEntiteSQL->getAll(null);
$count = 0;
foreach ($listeConnecteurEntite as $connecteurEntite) {
    if ($connecteurEntite['id_e'] == 0) {
        continue;
    }
    $id_ce = $connecteurEntite['id_ce'];
    $id_e = $connecteurEntite['id_e'];
    $connecteur = $objectInstancier->ConnecteurFactory->getConnecteurById($id_ce);
    if ($connecteur instanceof ConnecteurSuspensionIntf) {
        $connecteurDonneesFormulaire = $objectInstancier->donneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);
        $acces_suspendu = $connecteurDonneesFormulaire->get(ConnecteurSuspensionControler::ATTR_SUSPENSION);
        if ($acces_suspendu === true) {
            $count++;
            $blscript->trace('    connecteur ' . $id_ce);
            $connecteurDonneesFormulaire->setData(ConnecteurSuspensionControler::ATTR_SUSPENSION, false);
            $objectInstancier->ConnecteurSuspensionControler->setSuspension($connecteur, new Exception('Ancien mode de suspension : l\'exception n\'�tait pas conserv�e'));
            $blscript->traceln(' reformat�');
        }
    }
}
$blscript->traceln('    ' . $count . ' connecteur(s) reformat�(s)');

// Mantis 0041623 - 0041624
// Suppression des collectivit�s mal provisionn�es et des utilisateurs associ�s
// Pr�requis : 
//   - aucun dossier ne doit exister sur l'entit�
//   - aucun connecteur ne doit exister sur l'entit�
//   - libell� de la collectivit� = $libelleCollectiviteASupprimer

$entiteSQLClass = $objectInstancier->EntiteSQL;
$sql = "SELECT id_e, denomination FROM entite WHERE denomination=?";
$all_colASupprimer = $entiteSQLClass->query($sql, $libelleCollectiviteASupprimer);
$blscript->traceln('Nombre d\'entit� � supprimer : ' . sizeof($all_colASupprimer));
if (sizeof($all_colASupprimer)>0) {
    $blscript->traceln('Parcours des entit�s : ');
    foreach ($all_colASupprimer as $colASupprimer) {
        $blscript->traceln('Entite : ' . $colASupprimer['denomination'] . '(' . $colASupprimer['id_e'] . ')');
        $all_utilASupprimer = $objectInstancier->UtilisateurListe->getAllUtilisateurSimple($colASupprimer['id_e']);
        foreach ($all_utilASupprimer as $utilASupprimer) {
            $objectInstancier->RoleUtilisateur->removeAllRole($utilASupprimer['id_u']);
            $objectInstancier->Utilisateur->desinscription($utilASupprimer['id_u']);
            $blscript->traceln('  - Utilisateur ' . $utilASupprimer['login'] . '(' . $utilASupprimer['id_u'] . ') supprim�');
        }
        $entiteSQLClass->removeEntite($colASupprimer['id_e']);
        $blscript->traceln('  - Entit� supprim�e');
    }
}


//Pour �viter de repasser le script, test sur l'ex�cution de la derni�re requ�te.
$colonne_exist = $sqlQuery->query("SHOW COLUMNS FROM journal LIKE 'document_type';");
$blscript->trace('Script de mont�e de version de la base de donn�es : ');
if (empty($colonne_exist)) {
    // Il faut vider la table action_auto_log pour pouvoir ajouter le nouvel index unique.
    $sqlQuery->query("TRUNCATE TABLE action_auto_log;");
    $sqlQuery->query("DROP INDEX id_e ON action_auto_log;");
    $sqlQuery->query("ALTER TABLE action_auto_log ADD first_try datetime NOT NULL;");
    $sqlQuery->query("ALTER TABLE action_auto_log ADD last_try datetime NOT NULL;");
    $sqlQuery->query("ALTER TABLE action_auto_log ADD nb_try int(11) NOT NULL;");
    $sqlQuery->query("ALTER TABLE action_auto_log ADD last_message varchar(255) NOT NULL;");
    $sqlQuery->query("ALTER TABLE action_auto_log DROP date;");
    $sqlQuery->query("ALTER TABLE action_auto_log DROP message;");    
    $sqlQuery->query("CREATE UNIQUE INDEX id_ei ON action_auto_log (id_e,id_d,first_try);");
    $sqlQuery->query("CREATE TABLE extension (id_e int(11) NOT NULL AUTO_INCREMENT, nom varchar(128) NOT NULL, path text NOT NULL, PRIMARY KEY (id_e))  ENGINE=MyISAM;");
    $sqlQuery->query("ALTER TABLE journal CHANGE message message varchar(1024) NOT NULL;");
    $sqlQuery->query("ALTER TABLE journal ADD document_type varchar(128) NOT NULL;");
    $blscript->traceln('OK');        
} else {
    $blscript->traceln('DEJA EFFECTUE');
}

// Cryptage du mot de passe : il faut absolument que ce script ne soit pass� qu'une seule fois !!!
// Comment v�rifier ?
// le cryptage d'une valeur n'est pas constant. On ne peut pas v�rifier simplement la valeur du mot de passe.
// - Si on veut v�rifier en passant pas le mot de passe cypter, il faut faire un test de connexion et donc passer le mot de passe en clair.
// --> pas terrible : mot de passe est dans le script. Ca ne fonctionne plus si on change de mot de passe.
// - V�rifier si le mot de passe stock� en base ne correspond pas au mot de passe connu (commence par, longueur, fini par....)
// --> pas terrible : le mot de passe n'est pas dans le script. Il faut �tre sur des crit�res. Ca ne fonctionne plus si on change de mot de passe.
// - V�rifier qu'aucun mot de passe stock� en base commence par "$", caract�re correspondant au crypt utilis�.
// --> C'est le mieux. Si un mot de passe utilisateur commence par "$", le script ne passe pas. 

$nbre_password_crypt = $sqlQuery->queryOne("SELECT count(id_u) FROM utilisateur WHERE password like ?", '$%');
if ($nbre_password_crypt==0) {
    require_once (dirname(__FILE__) . "/../../installation/crypt-password.php");
    $blscript->traceln('Cryptage des mots de passe des utilisateurs : OK');
} else {
    $blscript->traceln('Cryptage des mots de passe des utilisateurs : DEJA EFFECTUE');
}

// Changement de workspace
// Si aucun fichier � d�placer, le script ne fait rien
require_once (dirname(__FILE__) . "/../../installation/old-workspace-to-new-workspace.php");
$blscript->traceln('Changement de structure du workspace : REJOUE');

// Alimentation du champs document-type dans la table journal
require_once (dirname(__FILE__) . "/../../installation/fix-journal-document-type.php");
$blscript->traceln('Alimentation de la colonne document-type dans la table journal : REJOUE');
