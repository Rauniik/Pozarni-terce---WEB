<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class RedirectPresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
	    parent::startup();
	    if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }
        $results_method = $this->database->fetchAll(
                "SELECT vypocet FROM admin WHERE id = ?", $this->user->getIdentity()->id);
            $method = $results_method[0]->vypocet;
        $this->redirect('Terce:casomira', ['typ_vypoctu' => $method]);
    }

    public function __construct(private Nette\Database\Explorer $database)
    {

    }

    public function renderDefault() {

        
        $this->template->actionName = $this->getPresenter()->action;

    }


}
