<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\Responses\TextResponse;


final class GuestPresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private Nette\Database\Explorer $database)
    {

    }

    public function renderDefault($id) {


        $inputValue = $this->getSession()->getSection('shared')->inputValue;

        $this->template->inputValue = $inputValue;

        
        $this->template->actionName = $this->getPresenter()->action;

        $result_visibility = $this->database->fetchAll("SELECT guest_visibility FROM admin");
        $data = array(
            'visibility' => $result_visibility
        );
        
        // Získání hodnoty guest_visibility
        $guestVisibility = $data['visibility'][0]->guest_visibility;

        $results_users = $this->database->fetchAll(
			"SELECT id, username FROM uzivatel WHERE id > ?", $guestVisibility);
		$this->template->users = $results_users;

            $teamsData = $this->database->fetchAll(
                "SELECT * FROM tymy WHERE id_uzivatele = ?;", $id);
    
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
                    v.id_kategorie, v.cas;', $id)->fetchAll();
    
            $categoriesData = $this->database->table('kategorie')->fetchAll();
    
            // Předáme data do šablony
            $this->template->teamsData = $teamsData;
            $this->template->resultsData = $resultsData;
            $this->template->categoriesData = $categoriesData;

    }
    
    public function actionselectUser(){
    
    }




}
