<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class LoginPresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private Nette\Database\Explorer $database){}

    public function renderDefault($success) {

        $this->template->success = $success;
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
            $this->flashMessage('Logged in!', 'yes');
            $results_method = $this->database->fetchAll(
                "SELECT vypocet FROM admin WHERE id_uzivatele = ?", $this->user->getIdentity()->id);
            $method = $results_method[0]->vypocet;
            $this->redirect('Terce:casomira', [$method]);
    
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
        $usernameExists = $this->database->table('uzivatel')->where('username', $data->username)->fetch();

        if ($usernameExists) {
            echo '<h1 class="main-header" style="color:red; background-color:black">Uživatel s tímto jménem již existuje.</h1>';
        } else {
            // Uživatelské jméno je unikátní, můžete provést vložení do tabulky
            /*$this->database->table('uzivatel')->insert([
                'username' => $data->username,
                'password' => password_hash($data->password, PASSWORD_DEFAULT),
            ]);*/
            $this->database->query(
                'INSERT INTO uzivatel (username, password) VALUES (?, ?);', $data->username, password_hash($data->password, PASSWORD_DEFAULT)
            );
            $this->database->table('admin')->insert([
                'id_uzivatele' => $this->database->getInsertId(),
                'vypocet' => 'vse',
            ]);
            $this->redirect('Login:default', 'yes');
        }
        /*$results_username = $this->database->query('SELECT username FROM uzivatel');
            $this->database->table('uzivatel')->insert([
                'username' => $data->username,
                'password' => password_hash($data->password, PASSWORD_DEFAULT),
            ]);
        $this->redirect('Login:');*/

    }
    
    public function actionOut(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné.', 'out');
        $this->redirect('Home:');
    }



}
