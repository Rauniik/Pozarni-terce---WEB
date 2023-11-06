<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class TercePresenter extends Nette\Application\UI\Presenter
{
        protected function createComponentUploadForm(): Form
        {
                $results_tymy = $this->database->query("SELECT * FROM tymy");
                $tymy = [];
                foreach($results_tymy as $tym){
                    $tymy [$tym->id] = $tym->Tym;
                }

                $form = new Form; // means Nette\Application\UI\Form

                /*$form->addInteger('id_tymu', 'ID Týmu:')
                    ->setRequired();*/

                $form->addSelect('tym', 'Tým:', $tymy)
                    ->setRequired();

                $form->addText('cas', 'Čas:')
                    ->addRule($form::Pattern, 'Chyba vstupu', '[0-9]{2}:[0-5][0-9].[0-9]{2}')
                    ->setRequired();

                $form->addSubmit('send', 'Nahrát do databáze');
            
                $form->onSuccess[] = $this->commentFormSucceeded(...);

                return $form;
                

        }
        private function commentFormSucceeded(\stdClass $data): void
        {

            $this->database->table('vysledky_zeny')->insert([
                'id_tymu' => $data->tym,
                'cas' => $data->cas,
            ]);

            /*$this->database->query(
                "SELECT Tym, cas,
                AS 'poradi' FROM vysledky_zeny AS V
                LEFT JOIN tymy AS T ON V.id_tymu = T.id;");*/

            $this->flashMessage('Data byla nahrána', 'success');
            $this->redirect('this');
        }
            
    public function __construct(private Nette\Database\Explorer $database)
    {
    }

    public function renderCasomira() {

        $results = $this->database->fetchAll(
			"SELECT Tym, cas,
			RANK() OVER(
				ORDER BY cas ASC)
			AS 'poradi' FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");
		$this->template->tymy = $results;
        
        $results_zeny = $this->database->fetchAll(
			"SELECT Tym, cas,
			RANK() OVER(
				ORDER BY cas ASC)
			AS 'poradi' FROM vysledky_zeny AS VZ
            LEFT JOIN tymy AS T ON VZ.id_tymu = T.id;");
		$this->template->tymy_zeny = $results_zeny;
        

        $this->template->actionName = $this->getPresenter()->action;

    }


}
