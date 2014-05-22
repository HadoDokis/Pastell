#! /usr/bin/php
<?php
require_once( __DIR__ . "/../../web/init.php");
require_once( __DIR__ . "/../batch/BLBatch.class.php");

$blScript = new BLBatch();

// Suppression du droit system:modification sur le role adminEntite.
$blScript->trace('Suppression du droit \'system:edition\' du r�le \'adminEntite\' : ');
$param = array("adminEntite", "system:edition");
$droitExist = $sqlQuery->queryOne("SELECT * FROM role_droit WHERE role=? and droit=?", $param);
if ($droitExist) {
    $sqlQuery->queryOne("DELETE FROM role_droit WHERE role=? and droit=?", $param);
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}

// Mise en place de l'extension BL : Connecteur fasttdtheliosbl
$blScript->trace('Mise en place extension BL Connecteur fasttdtheliosbl : ');
$requeteExtension = "SELECT id_e FROM extension WHERE path = ?";
$ext_fasttdtheliosblbl = "/var/www/pastell/extensionbl/fasttdtheliosbl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_fasttdtheliosblbl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_fasttdtheliosblbl);
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}

// Mise en place de l'extension BL : Connecteur srciparabl
$blScript->trace('Mise en place extension BL srciparabl : ');
$requeteExtension = "SELECT id_e FROM extension WHERE path = ?";
$ext_srciparabl = "/var/www/pastell/extensionbl/srciparabl/";
if (!$sqlQuery->queryOne($requeteExtension, $ext_srciparabl)) {
    $sqlQuery->queryOne("INSERT INTO extension (path) VALUES(?)", $ext_srciparabl);
    $blScript->traceln('OK');
} else {
    $blScript->traceln('DEJA FAIT');
}