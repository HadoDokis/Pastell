<?php
require_once( PASTELL_PATH . "/lib/base/PasswordGenerator.class.php");
require_once( PASTELL_PATH . "/lib/base/SQLQuery.class.php");
require_once( PASTELL_PATH . "/lib/helper/mail_validator.php");

class UtilisateurCreator {
	
	private $sqlQuery;
	private $passwordGenerator;
	
	public function __construct(SQLQuery $sqlQuery,Journal $journal){
		$this->sqlQuery = $sqlQuery;
		$this->setPasswordGenertor(new PasswordGenerator());
		$this->journal = $journal;
	}
	
	public function setPasswordGenertor(PasswordGenerator $passwordGenerator){
		$this->passwordGenerator = $passwordGenerator;
	}
	
	
	public function getLastError(){
		return $this->lastError;
	}
	
	public function create($login,$password,$password2,$email){
		if ( ! $login ){
			$this->lastError = "Il faut saisir un login";
			return false;
		}

		if ( ! $password ){
			$this->lastError = "Il faut saisir un mot de passe";
			return false;
		}
	
		if ($password != $password2){
			$this->lastError = "Les mots de passes ne correspondent pas";
			return false;
		}
		
		if (! is_mail($email)){
			$this->lastError ="Votre adresse email ne semble pas valide";
			return false;
		}

		if ($this->loginExists($login)){
			$this->lastError = "Ce login existe d�j�";
			return false;
		}
		
		$password_validation = $this->passwordGenerator->getPassword();
		
		$sql = "INSERT INTO utilisateur(login,password,email,mail_verif_password,date_inscription) " . 
				" VALUES (?,?,?,?,now())";
		$this->sqlQuery->query($sql,array($login,$password,$email,$password_validation));
		
		$id_u =  $this->sqlQuery->fetchOneValue("SELECT id_u FROM utilisateur WHERE login=?",array($login));
		
		
		return $id_u;
	}
	
	public function loginExists($login){
		$sql = "SELECT count(*) FROM utilisateur WHERE login = ?";
		return $this->sqlQuery->fetchOneValue($sql,array($login));
	}
	
}