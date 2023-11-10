<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class AdminPresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
	    parent::startup();
	    if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }
        if ($this->user->getIdentity()->getData()['name'] != 'Rauniik'){
            if($this->user->getIdentity()->getData()['name'] != 'admin'){
            
            $this->redirect('Home:');
            }
        }
    }

    public function __construct(private Nette\Database\Explorer $database)
    {

    }
    
    public function renderDefault() {

        $results_users = $this->database->fetchAll(
			"SELECT id, username FROM uzivatel");
		$this->template->users = $results_users;

        $this->template->actionName = $this->getPresenter()->action;

    }
    
    public function actionDeleteUser($id){

        $this->database->table('uzivatel')->where('id',$id)->delete();
        $this->redirect('Admin:');
    }


}
