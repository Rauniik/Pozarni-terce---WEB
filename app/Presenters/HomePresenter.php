<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class HomePresenter extends Nette\Application\UI\Presenter
{

    public function __construct()
    {

    }

    public function renderDefault() {

        
        $this->template->actionName = $this->getPresenter()->action;

    }


}
