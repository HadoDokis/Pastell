<?php

require_once( __DIR__ . "/../init.php");

$x_hub_signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
$rawdata = file_get_contents('php://input');

file_put_contents("/tmp/pastell-oasis.tmp", $x_hub_signature.$rawdata);

$oasisProvisionning = $objectInstancier->ConnecteurFactory->getGlobalConnecteur('oasis-provisionning');
if (!$oasisProvisionning){
	http_response_code(400);
	echo "Aucun connecteur Oasis Provisionning trouv�";
	exit;
}

if (empty($_SERVER['HTTP_X_HUB_SIGNATURE'])){
	http_response_code(400);
	echo "L'entete X-Hub-Signature n'a pas �t� trouv�e";
	exit;
}


try {
	$instance_id = $oasisProvisionning->getInstanceIdFromDeleteInstanceMessage($rawdata,$x_hub_signature);
	$selected_id_e = false;
	foreach($objectInstancier->ConnecteurEntiteSQL->getAllById("openid-authentication") as $connecteur_info){
		$connecteur_config = $objectInstancier->ConnecteurFactory->getConnecteurConfig($connecteur_info['id_ce']);
		if ($connecteur_config->get("instance_id") == $instance_id){
			$selected_id_e = $connecteur_info['id_e'];
			break;
		}
	}
	if (! $selected_id_e){
		throw new Exception("Impossible de trouv� une entit� correspondante � l'instance_id $instance_id");
	}
	
	$objectInstancier->EntiteSQL->setActive($selected_id_e,0);
	
} catch (Exception $e){
	http_response_code(400);
	echo $e->getMessage();
	exit;
}
echo "ok";

