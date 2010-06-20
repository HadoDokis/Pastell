<?php
require_once( ZEN_PATH . "/lib/SQLQuery.class.php");

class Utilisateur {

	private $sqlQuery;
	private $id_u;
		
	public function __construct(SQLQuery $sqlQuery,$id_u){
		$this->sqlQuery = $sqlQuery;
		$this->id_u = $id_u;
	}
	
	public function setNomPrenom($nom,$prenom){
		$sql = "UPDATE utilisateur SET nom = ? , prenom = ? WHERE id_u = ?";
		$this->sqlQuery->query($sql,array($nom,$prenom,$this->id_u));
	}
	
	public function getInfo(){
		$sql = "SELECT * FROM utilisateur WHERE id_u = ?";
		return $this->sqlQuery->fetchOneLine($sql,array($this->id_u));
	}
	
	public function validMail($password){
		$sql = "SELECT id_u FROM utilisateur " . 
				" WHERE id_u =? AND mail_verif_password= ? ";
		$result = $this->sqlQuery->fetchOneValue($sql,array($this->id_u, $password));
		if ( ! $result){
			return false;
		}
		$this->validMailAuto();
		return true;
	}
	
	public function validMailAuto(){
		$sql = "UPDATE utilisateur SET mail_verifie=1 WHERE id_u=?";
		$this->sqlQuery->query($sql, array($this->id_u));
	}
	
	public function verifPassword($password){
		$info = $this->getInfo();
		return  ($info['password'] == $password );
	}
	
	public function desinscription(){
		$sql = "DELETE FROM utilisateur WHERE id_u=?";
		$this->sqlQuery->query($sql,array($this->id_u));
	}
}