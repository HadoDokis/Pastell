<?php

class GradeSQL extends SQL {
	
	//Fili�re (C);Fili�re (L);Cadre d'emplois (C);Cadre d'emplois (L);Grade (C);Grade (L)
	public function add($info){
		
		$sql = "INSERT INTO grade (libelle,filiere,cadre_emploi) " . 
				" VALUES (?,?,?)";
		$this->query($sql,$info[5],$info[1],$info[3]);
		return true;
	}
	
	public function clean(){
		$sql =  "DELETE FROM grade ";
		$this->query($sql);
	}
	
	public function getFiliere(){
		$sql = "SELECT DISTINCT filiere as name FROM grade ORDER BY filiere";
		return $this->query($sql);
	}
	
	public function getCadreEmploi($filiere){
		$sql = "SELECT DISTINCT cadre_emploi as name FROM grade WHERE filiere=? ORDER BY cadre_emploi";
		return $this->query($sql,$filiere);
	}
	
	public function getLibelle($filiere,$cadre_emploi){
		$sql = "SELECT DISTINCT libelle as name FROM grade WHERE filiere=? AND cadre_emploi= ? ORDER BY libelle";
		return $this->query($sql,$filiere,$cadre_emploi);
	}
}