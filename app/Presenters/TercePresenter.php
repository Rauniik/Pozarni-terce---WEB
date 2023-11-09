<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class TercePresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
	    parent::startup();

	    if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }
    }
    protected function createComponentUploadForm(): Form
    {
            $results_tymy = $this->database->query("SELECT * FROM tymy WHERE id_kategorie = 1;");
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
                ->addRule($form::Pattern, 'Chyba vstupu', '[0-9]{2}:[0-5][0-9]:[0-5][0-9].[0-9]{2}')
                ->setRequired();

            $form->addSubmit('send', 'Nahrát do databáze');
        
            $form->onSuccess[] = $this->commentFormSucceeded(...);

            return $form;
            

    }
    private function commentFormSucceeded(\stdClass $data): void
    {

        $this->database->table('vysledky')->insert([
            'id_tymu' => $data->tym,
            'cas' => $data->cas,
            'id_kategorie' => 1
        ]);

        /*$this->database->query(
            "SELECT Tym, cas,
            AS 'poradi' FROM vysledky_zeny AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");*/

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }


        protected function createComponentUploadFormZeny(): Form
        {
                $results_tymy = $this->database->query("SELECT * FROM tymy WHERE id_kategorie = 2;");
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
                    ->addRule($form::Pattern, 'Chyba vstupu', '[0-9]{2}:[0-5][0-9]:[0-5][0-9].[0-9]{2}')
                    ->setRequired();

                $form->addSubmit('send', 'Nahrát do databáze');
            
                $form->onSuccess[] = $this->commentFormSucceededZeny(...);

                return $form;
                

        }
        private function commentFormSucceededZeny(\stdClass $data): void
        {

            $this->database->table('vysledky_zeny')->insert([
                'id_tymu' => $data->tym,
                'cas' => $data->cas,
                'id_kategorie' => 2
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
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 1;");
		$this->template->tymy = $results;
        
        $results_zeny = $this->database->fetchAll(
			"SELECT Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 2;");
		$this->template->tymy_zeny = $results_zeny;
        

        $this->template->actionName = $this->getPresenter()->action;

    }


    //Zápis týmů
    protected function createComponentUploadFormKategorie(): Form
    {
            $results_kategorie = $this->database->query("SELECT * FROM kategorie");
            $kategorie = [];
            foreach($results_kategorie as $kat){
                $kategorie [$kat->id] = $kat->kategorie;
            }

            $form = new Form; // means Nette\Application\UI\Form

            /*$form->addInteger('id_tymu', 'ID Týmu:')
                ->setRequired();*/

            $form->addSelect('kat', 'Kategorie:', $kategorie)
                ->setRequired();

            $form->addText('nazev', 'Název:')
                ->setRequired();

            $form->addSubmit('send', 'Registrovat');
        
            $form->onSuccess[] = $this->commentFormSucceededKategorie(...);

            return $form;
            

    }
    private function commentFormSucceededKategorie(\stdClass $data): void
    {

        $this->database->table('tymy')->insert([
            'Tym' => $data->nazev,
            'id_kategorie' => $data->kat,
        ]);

        /*$this->database->query(
            "SELECT Tym, cas,
            AS 'poradi' FROM vysledky_zeny AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");*/

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }


}
