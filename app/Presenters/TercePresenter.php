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
            $result_kategorie = $this->database->query("SELECT * FROM kategorie WHERE id_uzivatele = ?", $this->user->getIdentity()->id);
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

    public function renderCasomira($typ_vypoctu)
    {
        // Získejte data pro vytvoření tabulek
        
        $teamsData = $this->database->fetchAll(
        "SELECT * FROM tymy WHERE id_uzivatele = ?;", $this->user->getIdentity()->id);
        $this->database->table('admin')->where('id_uzivatele=?', $this->user->getIdentity()->id)->update([
            'vypocet' => $typ_vypoctu,
        ]);
        $this->database->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
        if($typ_vypoctu=='min'){
            $resultsData = $this->database->query(
                'WITH AvgCasu AS (
                    SELECT
                        cas,
                        id_uzivatel,
                        v.id_tymu,
                        v.id_kategorie,
                        v.id,
                        t.Tym AS nazev_tymu,
                        MIN(TIME_TO_SEC(cas)) AS sekundy,
                        MIN(MICROSECOND(cas) / 1000000.0) AS milisekundy
                    FROM
                        tymy t
                    JOIN
                        vysledky v ON t.id = v.id_tymu
                    WHERE
                        id_uzivatel = ?
                    GROUP BY
                        id_tymu
                )
                SELECT
                    cas,
                    id_uzivatel,
                    id_tymu,
                    id_kategorie,
                    id,
                    nazev_tymu,
                    milisekundy,
                    SEC_TO_TIME(sekundy + milisekundy) AS vysledny_cas,
                    RANK() OVER (PARTITION BY id_kategorie ORDER BY sekundy) AS poradi,
                    id AS vysledek_id
                FROM
                    AvgCasu
                ORDER BY
                    id_kategorie, sekundy, poradi;', $this->user->getIdentity()->id)->fetchAll();
        }
        else if($typ_vypoctu=='max'){
            $resultsData = $this->database->query(
                'WITH AvgCasu AS (
                    SELECT
                        cas,
                        id_uzivatel,
                        v.id_tymu,
                        v.id_kategorie,
                        v.id,
                        t.Tym AS nazev_tymu,
                        MAX(TIME_TO_SEC(cas)) AS sekundy,
                        MAX(MICROSECOND(cas) / 1000000.0) AS milisekundy
                    FROM
                        tymy t
                    JOIN
                        vysledky v ON t.id = v.id_tymu
                    WHERE
                        id_uzivatel = ?
                    GROUP BY
                        id_tymu
                )
                SELECT
                    cas,
                    id_uzivatel,
                    id_tymu,
                    id_kategorie,
                    id,
                    nazev_tymu,
                    milisekundy,
                    SEC_TO_TIME(sekundy + milisekundy) AS vysledny_cas,
                    RANK() OVER (PARTITION BY id_kategorie ORDER BY sekundy) AS poradi,
                    id AS vysledek_id
                FROM
                    AvgCasu
                ORDER BY
                    id_kategorie, sekundy, poradi;', $this->user->getIdentity()->id)->fetchAll();
        }
        else if($typ_vypoctu=='prum'){

            $resultsData = $this->database->query(
                'WITH AvgCasu AS (
                    SELECT
                        cas,
                        id_uzivatel,
                        v.id_tymu,
                        v.id_kategorie,
                        v.id,
                        t.Tym AS nazev_tymu,
                        AVG(TIME_TO_SEC(cas)) AS sekundy,
                        AVG(MICROSECOND(cas) / 1000000.0) AS milisekundy
                    FROM
                        tymy t
                    JOIN
                        vysledky v ON t.id = v.id_tymu
                    WHERE
                        id_uzivatel = ?
                    GROUP BY
                        id_tymu
                )
                SELECT
                    cas,
                    id_uzivatel,
                    id_tymu,
                    id_kategorie,
                    id,
                    nazev_tymu,
                    milisekundy,
                    SEC_TO_TIME(sekundy + milisekundy) AS vysledny_cas,
                    RANK() OVER (PARTITION BY id_kategorie ORDER BY sekundy) AS poradi,
                    id AS vysledek_id
                FROM
                    AvgCasu
                ORDER BY
                    id_kategorie, sekundy, poradi;', $this->user->getIdentity()->id)->fetchAll();
        }
        else if($typ_vypoctu=='sum'){
            $resultsData = $this->database->query(
                'WITH AvgCasu AS (
                    SELECT
                        cas,
                        id_uzivatel,
                        v.id_tymu,
                        v.id_kategorie,
                        v.id,
                        t.Tym AS nazev_tymu,
                        SUM(TIME_TO_SEC(cas)) AS sekundy,
                        SUM(MICROSECOND(cas) / 1000000.0) AS milisekundy
                    FROM
                        tymy t
                    JOIN
                        vysledky v ON t.id = v.id_tymu
                    WHERE
                        id_uzivatel = ?
                    GROUP BY
                        id_tymu
                )
                SELECT
                    cas,
                    id_uzivatel,
                    id_tymu,
                    id_kategorie,
                    id,
                    nazev_tymu,
                    milisekundy,
                    SEC_TO_TIME(sekundy + milisekundy) AS vysledny_cas,
                    RANK() OVER (PARTITION BY id_kategorie ORDER BY sekundy) AS poradi,
                    id AS vysledek_id
                FROM
                    AvgCasu
                ORDER BY
                    id_kategorie, sekundy, poradi;', $this->user->getIdentity()->id)->fetchAll();
        } //SEC_TO_TIME(SUM(TIME_TO_SEC(v.cas))) AS vysledny_cas
        else{
            $resultsData = $this->database->query(
                'SELECT cas, id_uzivatel, v.id_tymu, v.id_kategorie, v.id, 
                RANK() OVER (PARTITION BY v.id_kategorie ORDER BY v.cas) AS poradi,
                t.Tym AS nazev_tymu,
                v.cas AS vysledny_cas,
                v.id AS vysledek_id
                FROM
                    tymy t
                JOIN
                    vysledky v ON t.id = v.id_tymu
                WHERE
                    id_uzivatel = ?
                ORDER BY
                    v.id_kategorie, poradi, v.cas;', $this->user->getIdentity()->id)->fetchAll();
        }

        $categoriesData = $this->database->table('kategorie')->fetchAll();

        // Předáme data do šablony
        $this->template->teamsData = $teamsData;
        $this->template->resultsData = $resultsData;
        $this->template->categoriesData = $categoriesData;
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
        $this->redirect('Terce:casomira typ_vypoctu:min');
    }
    public function actionDeleteTeam($id){

        $this->database->table('tymy')->where('id',$id)->delete();
        $this->redirect('Terce:tymy');
    }


    //Zápis týmů
    protected function createComponentUploadFormTymy(): Form
    {
            $results_kategorie = $this->database->query("SELECT * FROM kategorie WHERE id_uzivatele = ?", $this->user->getIdentity()->id);
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
        
            $form->onSuccess[] = $this->commentFormSucceededTymy(...);

            return $form;
            

    }
    private function commentFormSucceededTymy(\stdClass $data): void
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

    //Zápis kategorií
    protected function createComponentUploadFormKategorie(): Form
    {

            $form = new Form; // means Nette\Application\UI\Form

            $form->addText('nazev', 'Název kategorie:')
                ->setRequired();
    
            /*$form->addHidden('id_uzivatele')
                ->setRequired();*/

            $form->addSubmit('send', 'Registrovat');
        
            $form->onSuccess[] = $this->commentFormSucceededKategorie(...);

            return $form;
            

    }
    private function commentFormSucceededKategorie(\stdClass $data): void
    {

        $this->database->table('kategorie')->insert([
            'kategorie' => $data->nazev,
            'id_uzivatele' => $this->user->getIdentity()->id,
        ]);

        /*$this->database->query(
            "SELECT Tym, cas,
            AS 'poradi' FROM vysledky_zeny AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");*/

        $this->flashMessage('Data byla nahrána', 'success');
        $this->redirect('this');
    }

    public function actionDeleteKategorie($id){

        $this->database->table('kategorie')->where('id',$id)->delete();
        $this->redirect('Terce:kategorie');
    }

    public function renderKategorie(){

        $results_kat = $this->database->fetchAll(
			"SELECT * FROM kategorie WHERE id_uzivatele = ?
            ", $this->user->getIdentity()->id);
		$this->template->editor_kat = $results_kat;
    }


}
