<?php 
require_once __DIR__.'/../init.php';

class SystemControlerTest extends PastellTestCase {
	
	public function __construct(){
		parent::__construct();
		$this->getSystemControler()->setDontRedirect(true);
	}
	
	/**
	 * @return SystemControler
	 */
	private function getSystemControler(){
		return $this->getObjectInstancier()->SystemControler;
	}
	
	public function reinitDatabaseOnSetup(){
		return true;
	}
	
	public function reinitFileSystemOnSetup(){
		return true;
	}
	
	public function setUp(){
		$this->getObjectInstancier()->Authentification->Connexion('admin',1);
		parent::setUp();
	}
	
	/**
     * @expectedException LastMessageException
     */
	public function testDoExtensionEditionAction() {
		$_POST['path'] = '/tmp/';
		$this->getSystemControler()->doExtensionEditionAction();
	}
	
	/**
	 * @expectedException LastErrorException
	 */
	public function testDoExtensionEditionActionFail() {
		$_POST['path'] = '';
		$this->getSystemControler()->doExtensionEditionAction();
	}

	public function testFluxDetailAction(){
		$_GET['id'] = 'actes-generique';
		$this->expectOutputRegex("##");
		$this->getSystemControler()->fluxDetailAction();
	}
	
	public function testIndex() {
		$this->expectOutputRegex("##");
		$this->getSystemControler()->indexAction();
	}
	
	public function testAddBuggyExtension(){
		$structure = array(
				'extension_buggy' => array(
						'module' => array(
							'toto'=>array()
						)
		
				),
		);
		$testStream = vfsStream::setup('root_buggy',null,$structure);
		$testStreamUrl = vfsStream::url('root_buggy');
		$_POST['path'] = $testStreamUrl."/extension_buggy/";
		try {
			$this->getSystemControler()->doExtensionEditionAction();
		} catch (LastMessageException $e) {}
		$_GET['page_number'] = 2;
		$this->expectOutputRegex("##");
		$this->getSystemControler()->indexAction();
				
	}
	
	
}