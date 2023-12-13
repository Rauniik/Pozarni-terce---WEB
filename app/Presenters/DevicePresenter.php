<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class DevicePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private Nette\Database\Explorer $database)
    {

    }

    public function renderDefault() {

        
        $this->template->actionName = $this->getPresenter()->action;

    }


}
