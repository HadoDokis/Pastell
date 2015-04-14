<?php

require_once( __DIR__ . "/../../web/init.php");
require_once( __DIR__ . "/ActionAutoControler.class.php");
require_once(__DIR__ . "/../../pastell-core/MailTo.class.php");

$now = time();
$msg="";
$notifier = false;

$list_file_attente = ActionAutoControler::getAllFileAttente();
foreach($list_file_attente as $file_attente_courante) {
    $action_auto_controler = new ActionAutoControler($objectInstancier, $file_attente_courante['file'], $file_attente_courante['duree_attente']);
    $duree_attente = $action_auto_controler->getDureeAttente();
    $duree_depasse = $action_auto_controler->isFileAttenteWarning();
    $duree = $action_auto_controler->getDureeLastMTime();
    if ($duree === false) {
        $notifier = true;
        $nature_notification ="CRITICAL";
        $msg = $msg . $action_auto_controler->getFileAttenteName() . " : Le fichier de controle n'existe pas le serveur.\r\n";
    } else if ($duree > $duree_attente) {
        $date_fichier =$action_auto_controler->getLastMtime();                
        $heure=floor($duree/3600);
        $minute=floor($duree%3600/60);
        $seconde=floor($duree%60);
        $duree_lisible = $heure . 'h ' . $minute . 'm ' . $seconde .'s';                        
        $msg = $msg . "   - " . $action_auto_controler->getFileAttenteName() . " : La derni�re �criture date de $date_fichier, ce qui correspond � $duree_lisible d'interruption.\r\n";
        $notifier = true;        
        if ($duree > ($duree_attente * 2)) {
            $nature_notification = "CRITICAL";
        } else {
            $nature_notification = "WARNING";
        }
    }     
}

if ($notifier) {
    $sujet = "[$nature_notification] " . FQDN . " :\r\nBusBL - Arr�t des scripts des actions automatiques";
    $contenu = "Le script des actions automatiques semblent ne plus s'ex�cuter sur le serveur " . FQDN . " : \r\n";
    $contenu = $contenu . $msg;
    $contenu = $contenu . "\r\n";
    $contenu = $contenu . "Merci de v�rifier son bon fonctionnement.\r\n";
    $contenu = $contenu . "\r\n";
    $contenu = $contenu . "--\r\n";
    $contenu = $contenu . "Ce mail vous est envoy� automatiquement par le serveur " . FQDN . "."; 
    
    $mailto = new MailTo($objectInstancier);
    $mailto->mailRacineAdmins($sujet, $contenu, '');
    
}