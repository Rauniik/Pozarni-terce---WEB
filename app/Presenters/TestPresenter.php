<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;


final class TestPresenter extends Nette\Application\UI\Presenter
{
    
            
    public function __construct(private Nette\Database\Explorer $database)
    {
    }

    /*protected function createComponentCommentForm(): Form
    {
            $form = new Form; // means Nette\Application\UI\Form

            $form->addInteger('id_tymu', 'ID Týmu:')
                ->setRequired();

            $form->addTextArea('cas', 'Čas:')
                ->setRequired();

            $form->addSubmit('send', 'Nahrát do databáze');
           
            $form->onSuccess[] = $this->commentFormSucceeded(...);

            return $form;
            

    }
    private function commentFormSucceeded(\stdClass $data): void
    {

        $this->database->table('vysledky_zeny')->insert([
            'id_tymu' => $data->id_tymu,
            'cas' => $data->cas,
        ]);

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }*/

    public function renderTest() {

        $results = $this->database->fetchAll(
			"SELECT Tym, cas,
			RANK() OVER(
				ORDER BY cas ASC)
			AS 'poradi' FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");
		$this->template->tymy = $results;

        $this->template->actionName = $this->getPresenter()->action;
            }
}
