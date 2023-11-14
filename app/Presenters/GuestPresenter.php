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

        $results = $this->database->fetchAll(
			"SELECT V.id, Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 1 AND id_uzivatel = ?;", $id);
		$this->template->tymy = $results;
        
        $results_zeny = $this->database->fetchAll(
			"SELECT V.id, Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 2 AND id_uzivatel = ?;", $id);
		$this->template->tymy_zeny = $results_zeny;

        $results_deti = $this->database->fetchAll(
			"SELECT V.id, Tym, cas,
                RANK() OVER(ORDER BY cas ASC) AS 'poradi'
            FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id
            WHERE V.id_kategorie = 3 AND id_uzivatel = ?;", $id);
		$this->template->tymy_deti = $results_deti;

    }
    
    public function actionselectUser(){
    
    }


}
