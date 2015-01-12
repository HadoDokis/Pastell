<?php

require_once( __DIR__ . "/../../web/init.php");
require_once( __DIR__ . "/../batch/BLBatch.class.php");
require_once(__DIR__ . "/BLCreationUtilisateur.class.php");
require_once(__DIR__ . "/BLCreationEntite.class.php");


class BLBatchInit extends BLBatch {
    function createConnecteurGlobal($name, $type) {
        global $objectInstancier;
        $id_ce = $objectInstancier->ConnecteurEntiteSQL->getGlobal($name);
        if (!$id_ce) {
            $this->traceln('Cr�ation du connecteur global ' . $name . ' : OK');
            $id_ce = $objectInstancier->ConnecteurEntiteSQL->addConnecteur(0,$name,$type, $name . ' global');
        } else {
            $this->traceln('Cr�ation du connecteur global ' . $name . ' : DEJA FAIT');
        }
        if (!$objectInstancier->FluxEntiteSQL->isUsed($id_ce)) {
            //$this->traceln('Association du connecteur global ' . $name . ' au flux global');
            $id_fe = $objectInstancier->FluxControler->editionModif(0, null, $type, $id_ce);
        }
    }
    
    function createConnecteur($idConnecteur, $libelle, $id_e) {
        global $objectInstancier;
        $this->trace('  - Cr�ation du connecteur ' . $idConnecteur . ' : ');
        $liste_connecteur = $objectInstancier->ConnecteurEntiteSQL->getAllbyId($idConnecteur);
        $id_ce=0;
        foreach($liste_connecteur as $connecteur) {
            if ($connecteur['id_e']==$id_e) {
                $id_ce = $connecteur['id_ce'];
                break;
            }
        }
        if (!$id_ce) {                       
            $id_ce = $objectInstancier->ConnecteurControler->nouveau($id_e, $idConnecteur, $libelle);
            $this->traceln('OK ');            
        } else {
            $this->traceln('DEJA FAIT');
        }
    }
}

$blScript = new BLBatchInit();
$todoList = array();

$blScript->traceln('----------------------');
$blScript->traceln('- Cr�ation des r�les -');
$blScript->traceln('----------------------');
$roleSQLClass = $objectInstancier->RoleSQL;
//R�le AdminEntite
$blScript->trace('  Cr�ation du r�le adminEntite : ');
$role = $roleSQLClass->getInfo('adminEntite');
if (!$role) {
    $roleSQLClass->edit('adminEntite', 'Administrateur d\'entit�');
    $roleSQLClass->addDroit('adminEntite','entite:edition');
    $roleSQLClass->addDroit('adminEntite','entite:lecture');
    $roleSQLClass->addDroit('adminEntite','utilisateur:edition');
    $roleSQLClass->addDroit('adminEntite','utilisateur:lecture');
    $roleSQLClass->addDroit('adminEntite','role:lecture');
    $roleSQLClass->addDroit('adminEntite','journal:lecture');
    $roleSQLClass->addDroit('adminEntite','system:lecture');
    $roleSQLClass->addDroit('adminEntite','pesbl:lecture');
    $roleSQLClass->addDroit('adminEntite','pesbl:edition');
    $roleSQLClass->addDroit('adminEntite','documentinternebl:lecture');
    $roleSQLClass->addDroit('adminEntite','documentinternebl:edition');
    $roleSQLClass->addDroit('adminEntite', 'acteadministratifbl:lecture');
    $roleSQLClass->addDroit('adminEntite', 'acteadministratifbl:edition');
    $roleSQLClass->addDroit('adminEntite', 'dsnbl:lecture');
    $roleSQLClass->addDroit('adminEntite', 'dsnbl:edition');    
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}
//R�le adminDocument
$blScript->trace('  Cr�ation du r�le adminDocument : ');
$role = $roleSQLClass->getInfo('adminDocument');
if (!$role) {
    $roleSQLClass->edit('adminDocument','Administrateur de document');
    $roleSQLClass->addDroit('adminDocument','entite:lecture');
    $roleSQLClass->addDroit('adminDocument','utilisateur:lecture');
    $roleSQLClass->addDroit('adminDocument','role:lecture');
    $roleSQLClass->addDroit('adminDocument','journal:lecture');
    $roleSQLClass->addDroit('adminDocument','pesbl:lecture');
    $roleSQLClass->addDroit('adminDocument','pesbl:edition');
    $roleSQLClass->addDroit('adminDocument','documentinternebl:lecture');
    $roleSQLClass->addDroit('adminDocument','documentinternebl:edition');
    $roleSQLClass->addDroit('adminDocument', 'acteadministratifbl:lecture');
    $roleSQLClass->addDroit('adminDocument', 'acteadministratifbl:edition');
    $roleSQLClass->addDroit('adminDocument', 'dsnbl:lecture');
    $roleSQLClass->addDroit('adminDocument', 'dsnbl:edition');    
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}

//R�le apiDocument
$blScript->trace('  Cr�ation du r�le apiDocument : ');
$role = $roleSQLClass->getInfo('apiDocument');
if (!$role) {    
    $roleSQLClass->edit('apiDocument','Op�rateur API sur document');
    $roleSQLClass->addDroit('apiDocument','entite:lecture');
    $roleSQLClass->addDroit('apiDocument','journal:lecture');
    $roleSQLClass->addDroit('apiDocument','pesbl:lecture');
    $roleSQLClass->addDroit('apiDocument','pesbl:edition');
    $roleSQLClass->addDroit('apiDocument','documentinternebl:lecture');
    $roleSQLClass->addDroit('apiDocument','documentinternebl:edition');
    $roleSQLClass->addDroit('apiDocument', 'acteadministratifbl:lecture');
    $roleSQLClass->addDroit('apiDocument', 'acteadministratifbl:edition');
    $roleSQLClass->addDroit('apiDocument', 'dsnbl:lecture');
    $roleSQLClass->addDroit('apiDocument', 'dsnbl:edition');
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}
//R�le apiStat
$blScript->trace('  Cr�ation du r�le apiStat : ');
$role = $roleSQLClass->getInfo('apiStat');
if (!$role) { 
    $roleSQLClass->edit('apiStat','Op�rateur API pour export des stats');
    $roleSQLClass->addDroit('apiStat','entite:lecture');
    $roleSQLClass->addDroit('apiStat','journal:lecture');
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}

$blScript->traceln('----------------');
$blScript->traceln('- Extension BL -');
$blScript->traceln('----------------');
$table_extension_exist = $sqlQuery->query("SHOW TABLES LIKE 'extension';");
if (empty($table_extension_exist)) {
    $sqlQuery->query("CREATE TABLE extension (id_e int(11) NOT NULL AUTO_INCREMENT, nom varchar(128) NOT NULL, path text NOT NULL, PRIMARY KEY (id_e))  ENGINE=MyISAM;");    
}
// Mise en place des extensions BL
$blScript->trace('Mise en place extension BL : ');
$prov_extension = false;
$requeteExtension = "SELECT id_e FROM extension WHERE path = ?";
$ext_fluxbl = "/var/www/pastell/extensionbl/fluxbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_fluxbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_fluxbl);
    $prov_extension=true;
}

$ext_iparapheurbl = "/var/www/pastell/extensionbl/iparapheurbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_iparapheurbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_iparapheurbl);
    $prov_extension=true;
}

$ext_s2lowbl = "/var/www/pastell/extensionbl/s2lowbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_s2lowbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_s2lowbl);
    $prov_extension=true;
}

$ext_srcibl = "/var/www/pastell/extensionbl/srcibl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_srcibl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_srcibl);
    $prov_extension=true;
}

$ext_srciparabl = "/var/www/pastell/extensionbl/srciparabl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_srciparabl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_srciparabl);
    $prov_extension=true;
}

$ext_stelabl = "/var/www/pastell/extensionbl/stelabl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_stelabl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_stelabl);
    $prov_extension=true;
}

$ext_globalbl = "/var/www/pastell/extensionbl/globalbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_globalbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_globalbl);
    $prov_extension=true;
}

$ext_xflucobl = "/var/www/pastell/extensionbl/xflucobl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_xflucobl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_xflucobl);
    $prov_extension=true;
}

$ext_fasttdtheliosbl = "/var/www/pastell/extensionbl/fasttdtheliosbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_fasttdtheliosbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_fasttdtheliosbl);
    $prov_extension=true;
}
$ext_ganeshparabl = "/var/www/pastell/extensionbl/ganeshparabl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_ganeshparabl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_ganeshparabl);
    $prov_extension=true;
}
$ext_ganeshtdtheliosbl = "/var/www/pastell/extensionbl/ganeshtdtheliosbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_ganeshtdtheliosbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_ganeshtdtheliosbl);
    $prov_extension=true;
}

$ext_ganeshtdtactesbl = "/var/www/pastell/extensionbl/ganeshtdtactesbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_ganeshtdtactesbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_ganeshtdtactesbl);
    $prov_extension=true;
}

$ext_netedsnbl = "/var/www/pastell/extensionbl/netedsnbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_netedsnbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_netedsnbl);
    $prov_extension=true;
}

$ext_insaebl = "/var/www/pastell/extensionbl/insaebl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_insaebl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_insaebl);
    $prov_extension=true;
}

if ($prov_extension) {
    $blScript->traceln('OK');    
} else {
    $blScript->traceln('DEJA FAIT');    
}

$blScript->traceln('------------------------------------');
$blScript->traceln('- Cr�ation des connecteurs globaux -');
$blScript->traceln('------------------------------------');

/* m42366 : ne plus utiliser l'horodateur interne, qui retourne de temps en temps des "Error during serial number generation"
// Connecteur horodateur interne
$blScript->trace('Cr�ation du connecteur global horodateur interne : ');
global $objectInstancier;
$connecteur_horodateur = $objectInstancier->ConnecteurEntiteSQL->getDisponible(0, "horodateur");
if (!$connecteur_horodateur) {
    $id_ce = $objectInstancier->ConnecteurControler->nouveau(0, 'horodateur-interne', 'horodateur interne global');
    $id_fe = $objectInstancier->FluxControler->editionModif(0, '', 'horodateur', $id_ce);

    $data['id_e'] = 0;
    $data['id_ce'] = $id_ce;
    $data['signer_key_password'] = '';

     $_FILES['signer_certificate']['name']='autorite-cert.pem';
     $_FILES['signer_certificate']['type']= 'application/octet-stream';
     $_FILES['signer_certificate']['tmp_name']= '/var/www/pastell/data-exemple/timestamp-cert.pem';
     $_FILES['signer_certificate']['error']= 0;
     $_FILES['signer_certificate']['size']= 3462;

     $_FILES['signer_key']['name']='signer_key.pem';
     $_FILES['signer_key']['type']= 'application/octet-stream';
     $_FILES['signer_key']['tmp_name']= '/var/www/pastell/data-exemple/timestamp-key.pem';
     $_FILES['signer_key']['error']= 0;
     $_FILES['signer_key']['size']= 887;

     $_FILES['ca_certificate']['name']='ca_certificate.pem';
     $_FILES['ca_certificate']['type']= 'application/octet-stream';
     $_FILES['ca_certificate']['tmp_name']= '/var/www/pastell/data-exemple/autorite-cert.pem';
     $_FILES['ca_certificate']['error']= 0;
     $_FILES['ca_certificate']['size']= 863;



    $fileUploader = new FileUploader();

    unset($data['id_e']);
    unset($data['id_ce']);

    $donneesFormulaire = $objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce);

    $donneesFormulaire->setTabDataVerif($data);
    if ($fileUploader) {  
        $donneesFormulaire->saveAllFile($fileUploader);
        // La fonction saveAllFile utilise move_uploaded_file. Comme les fichiers ne sont pas upload�s, la copie ne se fait pas.
        // --> Copie manuelle des 3 fichiers.
        copy($fileUploader->getFilePath('signer_certificate'),$donneesFormulaire->getFilePath('signer_certificate',0));   
        copy($fileUploader->getFilePath('signer_key'),$donneesFormulaire->getFilePath('signer_key',0));   
        copy($fileUploader->getFilePath('ca_certificate'),$donneesFormulaire->getFilePath('ca_certificate',0)); 
    } 

    foreach($donneesFormulaire->getOnChangeAction() as $action) {	
        $resultAction = $objectInstancier->ActionExecutorFactory->executeOnConnecteur($id_ce,$this->objectInstancier->Authentification->getId(),true,$action);
    }
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}
*/
// Cr�er connecteur global pour connecteurs
$blScript->createConnecteurGlobal('connecteurbl', 'adm_connecteur');
// Cr�er connecteur global pour flux
$blScript->createConnecteurGlobal('fluxbl', 'adm_flux');
// Cr�er connecteur global pour type de service signature
$blScript->createConnecteurGlobal('signaturebl', 'adm_signature');
// Cr�er connecteur global pour type de service TdT
$blScript->createConnecteurGlobal('tdtbl', 'adm_tdt');
// Cr�er connecteur global pour netedsnbl

$creationDSNGlobal = $blScript->read('Souhaitez-vous cr�er/param�trer le connecteur global Net-Entreprises pour la DSN ? (O/N)');
if (strtolower($creationDSNGlobal)=='o') {
    $blScript->trace('Creation du connecteur global netedsnbl : ');
    $id_ce_dsn = $objectInstancier->ConnecteurEntiteSQL->getGlobal('netedsnbl');
    if (!$id_ce_dsn) {
        $id_ce_dsn = $objectInstancier->ConnecteurControler->nouveau(0, 'netedsnbl', 'netedsnbl global');
        $id_fe = $objectInstancier->FluxControler->editionModif(0, null, 'dsn', $id_ce_dsn);
        $blScript->traceln('OK');
    } else {        
        $blScript->traceln('DEJA FAIT');
    }         
    $blScript->traceln('Parametrage du compte concentrateur sur le connecteur global :');
    $data['siret'] = $blScript->read('Siret du declarant');
    $data['nom'] = $blScript->read('Nom du declarant');
    $data['prenom'] = $blScript->read('Prenom du declarant');
    $data['motdepasse']= $blScript->read('Mot de passe du declarant');
    $data['service'] = 98;
    $data['range_max'] = 259200;    
    $donneesFormulaire = $objectInstancier->DonneesFormulaireFactory->getConnecteurEntiteFormulaire($id_ce_dsn);
    $donneesFormulaire->setTabDataVerif($data);
    $blScript->traceln('Param�trage du compte concentrateur sur le connecteur global : TERMINE');
}

$blScript->traceln('-----------------------------');
$blScript->traceln('- Cr�ation des utilisateurs -');
$blScript->traceln('-----------------------------');

$user_admin = $objectInstancier->RoleUtilisateur->getAllUtilisateur(0, 'admin');
if (!$user_admin) {
    $blScript->traceln('Cr�ation de l\'administrateur du site (ROLE : admin)');
    $user_admibles = new BLCreationUtilisateur($blScript);
    $user_admibles->creerAdmin();
    $login_admibles = $user_admibles->getLogin();
}

$creationAdmin2 = $blScript->read('Souhaitez-vous cr�er un compte administrateur de site suppl�mentaire (ROLE : admin)? (O/N)');
if (strtolower($creationAdmin2)=='o') {
    $user_admin = new BLCreationUtilisateur($blScript);
    $user_admin->creerAdmin();
    $login_admin = $user_admin->getLogin();    
}

//Utilisateur adminComptes
$user_adminCompte = $objectInstancier->Utilisateur->getIdFromLogin('admincomptes');
if (!$user_adminCompte) {
    $creation_admincomptes = $blScript->read('Souhaitez-vous cr�er un utilisateur pour administrer les comptes (LOGIN : admincomptes) ? (O/N)');
    if (strtolower($creation_admincomptes)=='o') {
        $userAdminCompte = new BLCreationUtilisateur($blScript);
        $userAdminCompte->setId_e(0);
        $userAdminCompte->setLogin('admincomptes');
        $userAdminCompte->setNom('admincomptes');
        $userAdminCompte->setPrenom('admincomptes');
        $userAdminCompte->setRole('adminEntite');
        $userAdminCompte->creerUtilisateur();
    }
}

//Utilisateur blready
$user_blready = $objectInstancier->Utilisateur->getIdFromLogin('blready');
if (!$user_blready) {
    $creation_blready = $blScript->read('Souhaitez-vous cr�er un utilisateur pour le provisioning des comptes (LOGIN : blready) ? (O/N)');
    if (strtolower($creation_blready)=='o') {
        $user_blready = new BLCreationUtilisateur($blScript);
        $user_blready->setId_e(0);
        $user_blready->setLogin('blready');
        $user_blready->setNom('blready');
        $user_blready->setPrenom('blready');
        $user_blready->setRole('adminEntite');
        $user_blready->creerUtilisateur();
    }
}
//Utilisateur blstat
$user_blstat = $objectInstancier->Utilisateur->getIdFromLogin('blstat');
if (!$user_blstat) {
    $creation_blstat = $blScript->read('Souhaitez-vous cr�er un utilisateur d�dier � l\'exploitation du journal (LOGIN : blstat) ? (O/N)');
    if (strtolower($creation_blstat)=='o') {
        $user_blstat = new BLCreationUtilisateur($blScript);
        $user_blstat->setId_e(0);
        $user_blstat->setLogin('blstat');
        $user_blstat->setNom('blstat');
        $user_blstat->setPrenom('blstat');
        $user_blstat->setRole('apiStat');
        $user_blstat->creerUtilisateur();
    }
}

/////////////////////////////////////////////////
// Cr�ation des entit�s "entreprise"
/////////////////////////////////////////////////
$blScript->traceln('-------------------------------------');
$blScript->traceln('- Cr�ation des entites \'entreprise\' -');
$blScript->traceln('-------------------------------------');
$creationEntite = $blScript->read('Souhaitez-vous cr�er une entit� \'entreprise\' ? (O/N)');
if (strtolower($creationEntite) == 'o') {
    while(strtolower($creationEntite) == 'o') {
        $blScript->traceln('-->Entit�');
        $entiteEntreprise = new BLCreationEntite($blScript);
        $id_e_entreprise = $entiteEntreprise->creerEntite();
        $denominationEntite = $entiteEntreprise->getDenomination();
        $blScript->traceln("Cr�ation de la collectivit� 'entreprise' $denominationEntite : OK");
        $blScript->traceln('-->Utilisateur admin de l\'entit� (ROLE : adminEntite sur l\'entit� entreprise cr��e');
        //Cr�ation de l'utilisateur admin sur l'entit� entreprise.        
        $userAdminEntreprise = new BLCreationUtilisateur($blScript);
        $userAdminEntreprise->setId_e($id_e_entreprise);
        $userAdminEntreprise->setLogin('admin@' . $denominationEntite);
        $userAdminEntreprise->setNom('admin');
        $userAdminEntreprise->setPrenom($denominationEntite);
        $userAdminEntreprise->setRole('adminEntite');
        $userAdminEntreprise->setEmail('a_completer@busbl.fr');
        $userAdminEntreprise->creerUtilisateur();
        //Creation des connecteurs associ�s au entite entreprise
        $blScript->traceln('Ajout des connecteurs d\'administration sur l\'entit� entreprise : ');               
        $blScript->createConnecteur('connecteurbl', 'Administration Connecteur BL', $id_e_entreprise);
        $blScript->createConnecteur('fluxbl', 'Administration Flux BL', $id_e_entreprise);
        $blScript->createConnecteur('signaturebl', 'Administration Signature BL', $id_e_entreprise);
        $blScript->createConnecteur('tdtbl', 'Administration TdT BL', $id_e_entreprise);
        // Pas d'association des connecteurs avec des flux        
        $blScript->traceln("");
        $creationEntite = $blScript->read('Souhaitez-vous cr�er une nouvelle entit� \'entreprise\' ? (O/N)');
    }
}

// Cr�er connecteur global pour type saeversant
$name = 'saeversantbl';
$blScript->createConnecteurGlobal($name, 'saeversant');
$todoList[] = 'Compl�ter le param�trage du connecteur global ' . $name . ', par IHM';

// Cr�er connecteur global pour type sae
$name = 'insaebl';
$blScript->createConnecteurGlobal($name, 'sae');
$todoList[] = 'Compl�ter le param�trage du connecteur global ' . $name . ', par IHM';

if ($todoList) {
    $blScript->traceln();
    $blScript->traceln('====> Param�trages compl�mentaires � effectuer :');
    foreach ($todoList as $todo) {
        $blScript->traceln('- ' . $todo);
    }
}
