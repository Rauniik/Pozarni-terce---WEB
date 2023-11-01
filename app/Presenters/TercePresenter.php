<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class TercePresenter extends Nette\Application\UI\Presenter
{
        //FORMULAR
        protected function createComponentRegistrationForm(): Form
            {
                $form = new Form;
                $form->addInteger('id', 'ID Týmu:');
                $form->addPassword('time', 'Čas:');
                $form->addSubmit('send', 'Registrovat');
                $form->onSuccess[] = [$this, 'formSucceeded'];
                return $form;
            }

        public function formSucceeded(Form $form, $data): void
            {
                // tady zpracujeme data odeslaná formulářem
                // $data->name obsahuje jméno
                // $data->password obsahuje heslo
                $this->flashMessage('Data vložena');
                $this->redirect('Terce:casomira');
            }
    public function __construct(private Nette\Database\Explorer $database)
    {
    }

    public function renderCasomira() {

        $results = $this->database->fetchAll(
			"SELECT Tym, celkove_body,
			RANK() OVER(
				ORDER BY celkove_body ASC)
			AS 'poradi' FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");
		$this->template->tymy = $results;
        
        $results_zeny = $this->database->fetchAll(
			"SELECT Tym, celkove_body,
			RANK() OVER(
				ORDER BY celkove_body ASC)
			AS 'poradi' FROM vysledky_zeny AS VZ
            LEFT JOIN tymy AS T ON VZ.id_tymu = T.id;");
		$this->template->tymy_zeny = $results_zeny;
        

        $this->template->actionName = $this->getPresenter()->action;

    }


}
