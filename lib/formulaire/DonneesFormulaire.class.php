<?php
require_once( PASTELL_PATH . "/lib/base/Recuperateur.class.php");
require_once( PASTELL_PATH . "/lib/formulaire/Formulaire.class.php");
require_once (PASTELL_PATH . "/ext/spyc.php");
require_once( PASTELL_PATH . "/lib/FileUploader.class.php");

class DonneesFormulaire {
	
	const TYPE_RESSOURCE_FORMULAIRE = 'formulaire';
	const TYPE_RESSOURCE_FORMULAIRE_ATTACHEMENT = 'fomulaire_attachment';
	
	private $formulaire;
	private $filePath;
	private $info;

	public function __construct($filePath){
		$this->filePath = $filePath;
		$this->retrieveInfo();
	}
	
	public function setFormulaire(Formulaire $formulaire){
		$this->formulaire = $formulaire;
	}
	
	public function getFormulaire(){
		return $this->formulaire;
	}
	
	
	
	public function injectData($field,$data){
		$this->info[$field] = $data;
	}
	
	private function retrieveInfo(){
		if ( ! file_exists($this->filePath)){
			return ;
		}
		$this->info = Spyc::YAMLLoad($this->filePath);		
	}
	
	public function save(Recuperateur $recuperateur, FileUploader $fileUploader){	
		
	
		foreach ($this->formulaire->getFields() as $field){
			$type = $field->getType();
			if ( $type == 'file'){
				$this->saveFile($field,$fileUploader);
			} else {
				$name = $field->getName();
				$value =  $recuperateur->get($name);
				if ( ($type != 'password') ||  $value){
					$this->info[$name] = $value;
				}
			}
		}
		$dump = Spyc::YAMLDump($this->info);
		file_put_contents($this->filePath,$dump);
	}
	
	private function saveFile(Field $field, FileUploader $fileUploader){
		$fname = $field->getName();
		
		if ($fileUploader->getName($fname)){
			$this->info[$fname] = $fileUploader->getName($fname);
			$fileUploader->save($fname, $this->getFilePath($fname));
		}
	}
	
	public function addFileFromData($field_name,$file_name,$raw_data){
		$this->info[$field_name] = $file_name;
		file_put_contents($this->getFilePath($field_name),$raw_data);
		$dump = Spyc::YAMLDump($this->info);
		file_put_contents($this->filePath,$dump);
	}
	
	public function removeFile($fieldName){
		$this->info[$fieldName] = "";
		$dump = Spyc::YAMLDump($this->info);
		file_put_contents($this->filePath,$dump);
	}
	
	public function getFilePath($field_name){
		return  $this->filePath."_".$field_name;
	}
	
	public function get($item){
		if (empty($this->info[$item])){
			return false;
		}
		return $this->info[$item];
	}
	
	public function geth($item){
		return nl2br(htmlentities($this->get($item),ENT_QUOTES));
	}
	
	public function isValidable(){
		foreach($this->formulaire->getAllFields() as $field){
			if ($field->isRequired() && ! $this->get($field->getName())){
				return false;
			}
		}
		return true;
	}
	
	public function getAllRessource(){
		$result = array();
		$result[] = array("url"=> $this->filePath,"type" => self::TYPE_RESSOURCE_FORMULAIRE);
		foreach($this->formulaire->getAllFields() as $field){
			if ($field->getType()== 'file' && $this->get($field->getName())){
				$result[] = array("url" => $this->getFilePath($field->getName()), "type" => self::TYPE_RESSOURCE_FORMULAIRE_ATTACHEMENT);
			}
		}
		return $result;
	}
}