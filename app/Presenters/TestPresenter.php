<?php
namespace App\Presenters;

use Nette\Application\UI\Presenter;
use Nette\Database\Context;

class TestPresenter extends Presenter
{
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function renderDefault()
    {
        $results = $this->database->query(
            'SELECT cas, id_uzivatel, 
                RANK() OVER (PARTITION BY v.id_kategorie ORDER BY v.cas) AS umisteni,
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
                v.id_kategorie, v.cas;', $this->user->getIdentity()->id
        )->fetchAll();

        $this->template->results = $results;
    }


    public function actionDeleteTime($id){

        $this->database->table('vysledky')->where('id',$id)->delete();
        $this->redirect('Terce:casomira');
    }
}


/*public function renderCasomira() {

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

}*/
