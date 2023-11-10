<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class AdminPresenter extends Nette\Application\UI\Presenter
{
    public function startup(): void
    {
	    parent::startup();
	    if (!$this->getUser()->isLoggedIn()) {
		    $this->redirect('Login:');
	    }
        if ($this->user->getIdentity()->getData()['name'] != 'Rauniik'){
            $this->redirect('Home:');
        }
    }

    public function __construct()
    {

    }

    public function renderDefault() {

        
        $this->template->actionName = $this->getPresenter()->action;

    }


}
