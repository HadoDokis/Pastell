Bonjour, 

Votre dossier <?php echo $info['docObjet']?> vient d'�tre <?php echo $info['etat']?>.
<?php 
if (isset($info['parapheur_annotation_rejet'])) {
    echo "Motif du rejet : \"" . $info['parapheur_annotation_rejet'] . "\"";
}
?>


Cordialement.

-- 
Ce mail vous est envoy� automatiquement par l'application "Bus Berger-Levrault".
