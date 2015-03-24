<?php
// Ce script lance en boucle les traitements des actions automatiques en file d'attente :
// Le traitement de chaque file d'attente est ex�cut� que si : 
//   - il n'est pas d�j� en cours d'ex�cution.
//   - Si le d�lai d'attente configur� sur chaque file d'attente est respect�

// Pour arr�ter ce script : 
//   - M�me m�canisme que le reste de l'application (fichier .lock ou batch.stop dans le r�pertoire /tmp/). N�anmoins le processus se relancera mais ne fera rien.
//   - Pour empecher que le processus se relance, il faut arr�ter de l'upstart


require_once( __DIR__ . "/../../web/init.php");
require_once( __DIR__ . "/BLBatch.class.php");
require_once( __DIR__ . "/FileAttente.class.php");

$blscript = new BLBatch();

$list_file_attente = FileAttente::getAllFileAttente();

while(true) {

    // Proposer une sortie pour �viter de lancer les traitements.
    $blscript->checkBatchStop();
    
    foreach($list_file_attente as $file_attente_courante) {
        $file_attente = new FileAttente($objectInstancier, WORKSPACE_PATH, $file_attente_courante['file'], $file_attente_courante['duree_attente']);            
        $file_en_cours = $file_attente->isFileAttenteEnCoursTraitement();       
        if (!$file_en_cours) {
            $lancer_process = false; 
            $date_debut_execution = $file_attente->getDateDerniereExecution();
            if ($date_debut_execution) {
                $duree_attente = $file_attente->getDureeAttente();
                if (strtotime($date_debut_execution) + $duree_attente < time()) {
                    $lancer_process = true;
                }
            } else {
                $lancer_process = true;
            }
            if ($lancer_process) {   
                if (!$file_attente->isFileAttenteStop()) {
                    // Ecriture de la date de lancement de la file dans un fichier                
                    //$file_attente->memoriserDateDerniereExecution(date('Y-m-d H:i:s'));
                    // Lancement du traitement de la file                
                    $outputfile = LOG_PATH . $file_attente->getLogFileName();            
                    $cmd = "/bin/sh /var/www/pastell/bl-core-extension/batch/bl-action-auto-file.sh " . $file_attente->getFileAttenteName() . " "  . $file_attente->getDureeAttente();
                    exec(sprintf("%s >> %s 2>&1 &", $cmd, $outputfile));
                }
            }
        }    
    }
    // Attente de 10 sec entre 2 v�rifications
    sleep(10);    
}