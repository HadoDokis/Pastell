<?php

require_once("init-api.php");

$recuperateur = new Recuperateur($_REQUEST);
$offset = $recuperateur->getInt('offset',0);
$limit = $recuperateur->getInt('limit',100);
$id_e = $recuperateur->getInt('id_e',0);
$type = $recuperateur->get('type');
$id_d = $recuperateur->get('id_d');
$id_user = $recuperateur->get('id_user');
$recherche = $recuperateur->get('recherche');
$date_debut = $recuperateur->get('date_debut');
$date_fin = $recuperateur->get('date_fin');
$format = $recuperateur->get('format');
$nbre_ligne_max_paquet = $recuperateur->get('nbre_ligne_max_paquet', 10000);
$csv_entete_colonne = $recuperateur->getInt('csv_entete_colonne', 0);

if   (! $roleUtilisateur->hasDroit($id_u,"journal:lecture",$id_e)){
	$JSONoutput->displayErrorAndExit("Acces interdit id_e=$id_e, id_d=$id_d,id_u=$id_u,type=$type");
}

// Pour �viter des probl�mes m�moires, au format CSV : 
//  - le chargement des lignes se fait par paquet.
//  - les lignes sont retourn�es au fur et � mesure des chargements des paquets.
//  - comme les traitements sont plus long, r�initialisation du temps max_execution_time dans chaque boucle.
// NB : Le probl�me "m�moire", existe toujours pour le format JSON.

if ($format == 'csv') {
    
    header("Content-type: text/csv; charset=iso-8859-1");
    header("Content-disposition: attachment; filename=pastell-export-journal-$id_e-$type-$id_d.csv");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
    $handle = fopen('php://output', 'w');
    
    if ($limit!=-1 && $limit < $nbre_ligne_max_paquet) {
        $nbreLigne=$limit;
    } else {
        $nbreLigne=$nbre_ligne_max_paquet;
    }
    $ligneDepart = $offset;    
            
    $continuer = true;
    $max_execution_time= ini_get('max_execution_time');
    while ($continuer) {
        ini_set('max_execution_time', $max_execution_time);        
        $tab = $journal->getAll($id_e, $type, $id_d, $id_user, $ligneDepart, $nbreLigne, $recherche, $date_debut, $date_fin, true);        
        $nbreLigne = sizeof($tab);        
        $ligneDepart = $ligneDepart + $nbreLigne;                
        foreach ($tab as $ligne) {
            if ($csv_entete_colonne) {
                // Les ent�tes sont les cl�s du tableau associatif
                $entetes = array_keys($ligne);                
                // Suppression de la colonne preuve
                $index_col_preuve = array_search('preuve', $entetes, true);
                array_splice($entetes, $index_col_preuve, 1);                
                fputcsv($handle, $entetes);
                $csv_entete_colonne=false;
            }
            $ligne['message'] = preg_replace("/(\r\n|\n|\r)/", " ", $ligne['message']);
            $ligne['message_horodate'] = preg_replace("/(\r\n|\n|\r)/", " ", $ligne['message_horodate']);
            unset($ligne['preuve']);
            fputcsv($handle, $ligne);
        }        
        unset($tab);
        $continuer = (($limit==-1) || ($limit > $nbre_ligne_max_paquet)) && ($nbreLigne == $nbre_ligne_max_paquet);
        if( $continuer) {
            // Calcul du nombre de ligne de la prochaine it�ration.
            if (($limit != -1) && (($ligneDepart + $nbreLigne) > $limit)) {
                $nbreLigne = $limit - $ligneDepart;
            }
        }        
    }   
    fclose($handle);

} else {
    $all = $journal->getAll($id_e, $type, $id_d, $id_user, $offset, $limit, $recherche, $date_debut, $date_fin);
    $JSONoutput->display($all);    
}
