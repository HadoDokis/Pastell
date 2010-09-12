<?php

require_once( PASTELL_PATH . "/lib/base/SQLQuery.class.php");

class UtilisateurListe {
	
	private $sqlQuery;
	
	
	public function __construct(SQLQuery $sqlQuery){
		$this->sqlQuery = $sqlQuery;
	}
	
	public function getNbUtilisateur(){
		$sql = "SELECT count(*) FROM utilisateur ";
		return $this->sqlQuery->fetchOneValue($sql);
	}
	
	public function getAll($offset,$limit){
		$sql = "SELECT utilisateur.*,entite.* FROM utilisateur " . 
				" LEFT JOIN utilisateur_role ON utilisateur.id_u = utilisateur_role.id_u " . 
				" LEFT JOIN entite ON utilisateur_role.siren = entite.siren ".
				" ORDER BY utilisateur.nom,prenom,login LIMIT $offset,$limit";
		return $this->sqlQuery->fetchAll($sql);
	}
	
	public function getUtilisateurByLogin($login){
		$sql = "SELECT id_u FROM utilisateur WHERE login = ?";
		return $this->sqlQuery->fetchOneValue($sql,array($login));
	}
	
	public function getUtilisateurByEntite($siren){
		$sql = "SELECT * FROM utilisateur_role " . 
				" JOIN utilisateur ON utilisateur_role.id_u = utilisateur.id_u ".
				" WHERE utilisateur_role.siren = ? " . 
				" ORDER BY utilisateur.nom,utilisateur.prenom";
		return $this->sqlQuery->fetchAll($sql,array($siren));
	}
	
}