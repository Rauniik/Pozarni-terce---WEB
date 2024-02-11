<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class AdminPresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
	    parent::startup();
	    if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }
        if ($this->user->getIdentity()->getData()['name'] != 'Rauniik'){
            if($this->user->getIdentity()->getData()['name'] != 'Petr'){
            $results_users = $this->database->fetchAll(
                "SELECT id, username, visibility FROM uzivatel WHERE id = ?", $this->user->getIdentity()->id);
            $visibility = $results_users[0]->visibility;
            $this->redirect('User:default', ['visibility' => $visibility]);
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

    protected function createComponentEditGuest(): Form
    {

            $form = new Form; // means Nette\Application\UI\Form

            $form->addInteger('int', 'Viditelnost uživatelů:')
                ->setRequired();

            $form->addSubmit('send', 'Nastavit');
        
            $form->onSuccess[] = $this->commentFormSucceededEditGuest(...);

            return $form;
            

    }
    private function commentFormSucceededEditGuest(\stdClass $data): void
    {
        $this->database->table('admin')->where('id', 1)->update([
            'guest_visibility' => $data->int,
        ]);

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }


}
