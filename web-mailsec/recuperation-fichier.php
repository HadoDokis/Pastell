<?php
require_once( __DIR__ . "/../web/init.php");

$recuperateur = new Recuperateur($_GET);
$key = $recuperateur->get('key');
$field = $recuperateur->get('field');
$num = $recuperateur->getInt('num');

$documentEmail = $objectInstancier->DocumentEmail;
$info  = $documentEmail->getInfoFromKey($key);
if (! $info ){
	header("Location: invalid.php");
	exit;
}
$id_d = $info['id_d'];


$donneesFormulaire = $donneesFormulaireFactory->get($id_d,'mailsec-destinataire');

$ip = $_SERVER['REMOTE_ADDR'];

if ($donneesFormulaire->get('password') && (empty($_SESSION["consult_ok_{$key}_{$ip}"]))){
	header("Location: password.php?key=$key");
	exit;
}


$documentEmail->consulter($key,$journal);



$donneesFormulaire = $donneesFormulaireFactory->get($id_d,'mailsec-destinataire');


$file_path = $donneesFormulaire->getFilePath($field,$num);
$file_name_array = $donneesFormulaire->get($field);
$file_name= $file_name_array[$num];

if (! file_exists($file_path)){
	$lastError->setLastError("Ce fichier n'existe pas");
	header("Location: index.php");
	exit;
}

header("Content-type: ".mime_content_type($file_path));
header("Content-disposition: attachment; filename=\"$file_name\"");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");

readfile($file_path);