<?php

require_once( __DIR__ . "/../../web/init.php");
require_once(__DIR__ . "/../../pastell-core/MailTo.class.php");
require_once(__DIR__ . "/BLBatch.class.php");

$blbatch = new BLBatch();

$destinataire = $blbatch->read('Destinataires (s�par�s par \',\' si plusieurs, BLESDEV par d�faut)', 'blesdev@berger-levrault.fr');
$objet = $blbatch->read('Sujet du mail', '[BUS BL] Mail envoy� depuis ' . FQDN);
$contenu = $blbatch->read('Contenu du message', 'Message par d�faut.');

$mailto = new MailTo($objectInstancier);
$mailto->mail($destinataire, $objet, $contenu, '');
