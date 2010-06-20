<?php
require_once( ZEN_PATH . "/lib/PasswordGenerator.class.php");
require_once( ZEN_PATH . "/lib/SQLQuery.class.php");

require_once( PASTELL_PATH . "/lib/MailValidator.class.php");

class UtilisateurCreator {
	
	private $sqlQuery;
	private $passwordGenerator;
	private $mailValidator;
	
	public function __construct(SQLQuery $sqlQuery){
		$this->sqlQuery = $sqlQuery;
		$this->setPasswordGenertor(new PasswordGenerator());
		$this->setMailValidator(new MailValidator());
	}
	
	public function setPasswordGenertor(PasswordGenerator $passwordGenerator){
		$this->passwordGenerator = $passwordGenerator;
	}
	
	public function setMailValidator(MailValidator $mailValidator){
		$this->mailValidator = $mailValidator;
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
		
		if (! $this->mailValidator->isValid($email)){
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
		
		return $this->sqlQuery->fetchOneValue("SELECT id_u FROM utilisateur WHERE login=?",array($login));
	}
	
	public function loginExists($login){
		$sql = "SELECT count(*) FROM utilisateur WHERE login = ?";
		return $this->sqlQuery->fetchOneValue($sql,array($login));
	}
	
}