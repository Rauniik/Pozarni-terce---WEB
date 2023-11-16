<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class UserPresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
	    parent::startup();
	    if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }
        /*if ($this->user->getIdentity()->getData()['name'] != 'admin' || 'user'){
            $this->redirect('Home:');
        }*/
    }

    public function __construct(private Nette\Database\Explorer $database)
    {

    }

    public function renderDefault() {

        
        $this->template->actionName = $this->getPresenter()->action;

        $results_users = $this->database->fetchAll(
			"SELECT id, username FROM uzivatel WHERE id = ?", $this->user->getIdentity()->id);
		$this->template->users = $results_users;

        $this->template->actionName = $this->getPresenter()->action;

    }

    public function actionDeleteUser($id){

        $this->database->table('uzivatel')->where('id',$id)->delete();
        $this->redirect('Login:Out');
    }
}
