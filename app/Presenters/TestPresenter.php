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

    protected function createComponentCommentForm(): Form
    {
            $form = new Form; // means Nette\Application\UI\Form

            $form->addInteger('id_tymu', 'ID Týmu:')
                ->setRequired();

            $form->addTextArea('celkove_body', 'Čas:')
                ->setRequired();

            $form->addSubmit('send', 'Nahrát do databáze');
           
            $form->onSuccess[] = $this->commentFormSucceeded(...);

            return $form;
            

    }
    private function commentFormSucceeded(\stdClass $data): void
    {

        $this->database->table('vysledky_zeny')->insert([
            'id_tymu' => $data->id_tymu,
            'celkove_body' => $data->celkove_body,
        ]);

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }


}
