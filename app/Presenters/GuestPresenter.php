<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class GuestPresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private Nette\Database\Explorer $database)
    {

    }

    public function renderDefault($id) {

        
        $this->template->actionName = $this->getPresenter()->action;

        $results_users = $this->database->fetchAll(
			"SELECT id, username FROM uzivatel WHERE id > 0");
		$this->template->users = $results_users;

            $teamsData = $this->database->fetchAll(
                "SELECT * FROM tymy WHERE id_uzivatele = ?;", $id);
    
            $resultsData = $this->database->query(
                'SELECT cas, id_uzivatel, V.id_tymu, V.id_kategorie, V.id, 
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
