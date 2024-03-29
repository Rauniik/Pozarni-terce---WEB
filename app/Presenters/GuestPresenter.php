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

        $this->database->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        $inputValue = $this->getSession()->getSection('shared')->inputValue;

        $this->template->inputValue = $inputValue;

        $typ_vypoctu = 'all';
        
        $this->template->actionName = $this->getPresenter()->action;

        $result_visibility = $this->database->fetchAll("SELECT guest_visibility FROM admin WHERE id_uzivatele = 1");
        $data = array(
            'visibility' => $result_visibility,
        );

        // Získání hodnoty guest_visibility
        $guestVisibility = $data['visibility'][0]->guest_visibility;
        if($id>0){
        $result_method = $this->database->query('SELECT vypocet FROM admin WHERE id_uzivatele = ?;', $id)->fetchAll();
        $data = array(
            'typ_vypoctu' => $result_method,
        );
        $typ_vypoctu = $data['typ_vypoctu'][0]->vypocet;
        }

        $results_users = $this->database->fetchAll(
			"SELECT id, username, visibility FROM uzivatel WHERE id > ?", $guestVisibility);
		$this->template->users = $results_users;

            $teamsData = $this->database->fetchAll(
                "SELECT * FROM tymy WHERE id_uzivatele = ?;", $id);
    
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
                            id_kategorie, sekundy, poradi;', $id)->fetchAll();
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
                            id_kategorie, sekundy, poradi;', $id)->fetchAll();
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
                            id_kategorie, sekundy, poradi;', $id)->fetchAll();
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
                            id_kategorie, sekundy, poradi;', $id)->fetchAll();
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
                            v.id_kategorie, poradi, v.cas;', $id)->fetchAll();
                }
    
            $categoriesData = $this->database->table('kategorie')->fetchAll();
    
            // Předáme data do šablony
            $this->template->teamsData = $teamsData;
            $this->template->resultsData = $resultsData;
            $this->template->categoriesData = $categoriesData;
            $this->template->typ_vypoctu = $typ_vypoctu;

    }
    
    public function actionselectUser(){
    
    }




}
