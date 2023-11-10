<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class LoginPresenter extends Nette\Application\UI\Presenter
{

    public function __construct()
    {

    }

    public function renderDefault() {


        $this->template->actionName = $this->getPresenter()->action;

    }

    protected function createComponentUploadFormLogin(): Form
    {

            $form = new Form;

            $form->addText('password', 'Password:')
                ->setRequired(' ');

            $form->addText('username', 'Login:')
                ->setRequired(' ');

            $form->addSubmit('send', 'Přihlásit');
        
            $form->onSuccess[] = $this->commentFormSucceededLogin(...);

            return $form;
            

    }
    private function commentFormSucceededLogin(\stdClass $data): void
    {
        try {
            $this->getUser()->login($data->username, $data->password);
            $this->redirect('Terce:casomira');
    
        } catch (Nette\Security\AuthenticationException $e) {
            $this->redirect('Home:');
        }
    }

    protected function createComponentUploadFormRegister(): Form
    {

            $form = new Form;

            $form->addText('password', 'Password:')
                ->setRequired(' ');

            $form->addText('username', 'Login:')
                ->setRequired(' ');

            $form->addSubmit('send', 'Registrovat');
        
            $form->onSuccess[] = $this->commentFormSucceededRegister(...);

            return $form;
            

    }
    private function commentFormSucceededRegister(\stdClass $data): void
    {
        $this->database->table('uzivatel')->insert([
            'username' => $data->username,
            'password' => $data->password,
        ]);
    }
    
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.');
        $this->redirect('Home:');
    }


}
