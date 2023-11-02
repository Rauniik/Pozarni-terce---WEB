<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class TercePresenter extends Nette\Application\UI\Presenter
{
        //TEST FORMULAR
        protected function createComponentRegistrationForm(): Form
            {
                $form = new Form;
                $form->addInteger('id', 'ID Týmu:');
                //$form->addPassword('time', 'Čas:');
                $form->addSubmit('send', 'Registrovat');
                $form->onSuccess[] = [$this, 'formSucceeded'];
                return $form;
            }

        public function formSucceeded(Form $form, $data): void
            {
                $input_id = $form->getComponent('id');
                //echo($input_time = $form->getComponent('time'));
            }


        /*protected function createComponentMyForm()
            {
                $form = new \Nette\Application\UI\Form;
        
                $form->addText('cislo', 'Kladné číslo:')
                    ->setRequired('Zadejte kladné číslo.')
                    ->addRule(\Nette\Forms\Form::INTEGER, 'Zadejte platné číslo.');
        
                $form->addSubmit('submit', 'Odeslat');
        
                $form->onSuccess[] = [$this, 'processForm'];
        
                return $form;
            }
        
        public function processForm(\Nette\Application\UI\Form $form, $values)
            {
                // Zde můžete provést zápis do databáze
                $cislo = $values['cislo'];
            
                $database = $this->context->getByType(\Nette\Database\Context::class);
                $database->table('test')->insert(['id_tymu' => $cislo]);
            
                $this->flashMessage('Číslo bylo úspěšně uloženo do databáze.', 'success'); // Použijte 'success' jako klíč zprávy
                $this->redirect('this');
            }*/
            
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
