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
        /*if ($this->user->getIdentity()->getData()['name'] != 'admin' || 'user'){
            $this->redirect('Home:');
        }*/
    }
    protected function createComponentUploadForm(): Form
    {       
            $result_kategorie = $this->database->query("SELECT * FROM kategorie");
            $kat = [];
            foreach($result_kategorie as $kategorie){
                $results_tymy = $this->database->query("SELECT * FROM tymy WHERE id_kategorie = ? AND id_uzivatele = ? ORDER BY Tym ASC;", $kategorie->id, $this->user->getIdentity()->id);
                $tymy = [];
                foreach($results_tymy as $tym){
                    $tymy [$tym->id] = $tym->Tym;
                }
                $kat [$kategorie->kategorie] = $tymy;
            }

            $form = new Form; // means Nette\Application\UI\Form

            /*$form->addInteger('id_tymu', 'ID Týmu:')
                ->setRequired();*/

            $form->addSelect('tym', 'Tým:', $kat)
                ->setRequired();

            $form->addText('cas', 'Čas:')
                ->addRule($form::Pattern, 'Chyba vstupu', '[0-9]{2}:[0-5][0-9]:[0-5][0-9].[0-9]{2}')
                ->setRequired();

            $form->addHidden('id_uzivatel')
                ->setRequired();

            $form->addSubmit('send', 'Nahrát do databáze');
        
            $form->onSuccess[] = $this->commentFormSucceeded(...);

            return $form;
            

    }
    private function commentFormSucceeded(\stdClass $data): void
    {
        $tym = $this->database->table('tymy')->get($data->tym);
        $this->database->table('vysledky')->insert([
            'id_tymu' => $data->tym,
            'cas' => $data->cas,
            'id_kategorie' => $tym->id_kategorie,
            'id_uzivatel' => $data->id_uzivatel
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
			"SELECT V.id, Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 1 AND id_uzivatel = ?;", $this->user->getIdentity()->id);
		$this->template->tymy = $results;
        
        $results_zeny = $this->database->fetchAll(
			"SELECT V.id, Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 2 AND id_uzivatel = ?;", $this->user->getIdentity()->id);
		$this->template->tymy_zeny = $results_zeny;

        $results_deti = $this->database->fetchAll(
			"SELECT V.id, Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 3 AND id_uzivatel = ?;", $this->user->getIdentity()->id);
		$this->template->tymy_deti = $results_deti;
        

        $this->template->actionName = $this->getPresenter()->action;

    }
    public function renderTymy(){

        $results_tymy = $this->database->fetchAll(
			"SELECT T.id, Tym, id_kategorie, kategorie 
            FROM tymy AS T 
            LEFT JOIN kategorie AS K ON T.id_kategorie = K.id 
            WHERE T.id_uzivatele = ? 
            ORDER BY K.id ASC, Tym ASC
            ", $this->user->getIdentity()->id);
		$this->template->editor_tymy = $results_tymy;
    }

    public function actionDeleteTime($id){

        $this->database->table('vysledky')->where('id',$id)->delete();
        $this->redirect('Terce:casomira');
    }
    public function actionDeleteTeam($id){

        $this->database->table('tymy')->where('id',$id)->delete();
        $this->redirect('Terce:tymy');
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
    
            /*$form->addHidden('id_uzivatele')
                ->setRequired();*/

            $form->addSubmit('send', 'Registrovat');
        
            $form->onSuccess[] = $this->commentFormSucceededKategorie(...);

            return $form;
            

    }
    private function commentFormSucceededKategorie(\stdClass $data): void
    {

        $this->database->table('tymy')->insert([
            'Tym' => $data->nazev,
            'id_kategorie' => $data->kat,
            'id_uzivatele' => $this->user->getIdentity()->id,
        ]);

        /*$this->database->query(
            "SELECT Tym, cas,
            AS 'poradi' FROM vysledky_zeny AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");*/

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }


}
