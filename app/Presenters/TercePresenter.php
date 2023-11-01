<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;


final class TercePresenter extends Nette\Application\UI\Presenter
{

    public function __construct(private Nette\Database\Explorer $database)
    {
    }

    public function renderCasomira() {

        $results = $this->database->fetchAll(
			"SELECT Tym, celkove_body,
			RANK() OVER(
				ORDER BY celkove_body ASC)
			AS 'poradi' FROM vysledky AS V
            LEFT JOIN tymy AS T ON V.id_tymu = T.id;");
		$this->template->tymy = $results;
        $this->template->actionName = $this->getPresenter()->action;

    }


}
