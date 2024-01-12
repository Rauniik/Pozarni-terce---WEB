<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class DevicePresenter extends Nette\Application\UI\Presenter
{
    private $httprequest;

    public function startup(): void
    {
	    parent::startup();
	    /*if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }*/
        $this->httprequest=$this->getHttpRequest();
    }

    public function __construct(private Nette\Database\Explorer $database)
    {

    }

    public function renderDefault() {

        
        $this->template->actionName = $this->getPresenter()->action;

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
		$this->template->tymy = $kat;

    }

    public function actionData() {

        if($this->httprequest->isMethod('POST')){
            $body=$this->httprequest->getRawBody();

            $data = json_decode($body, false);

            $tym = $this->database->table('tymy')->get($data->Tym);
            
            $this->database->table('vysledky')->insert([
                'id_tymu' => $data->Tym,
                'cas' => $data->Data,
                'id_kategorie' => $tym->id_kategorie,
                'id_uzivatel' => $data->User
            ]);

            $this->sendJson(json_encode([]));

        }else{
            $this->error();
        }
    }

}
