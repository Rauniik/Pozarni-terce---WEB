<?php
namespace App;
use Nette;
use Nette\Security\SimpleIdentity;

class MyAuthenticator implements Nette\Security\Authenticator
{
	public function __construct(
		private Nette\Database\Explorer $database,
		private Nette\Security\Passwords $passwords,
	) {
	}

	public function authenticate(string $username, string $password): SimpleIdentity
	{
		$row = $this->database->table('uzivatel')
			->where('username', $username)
			->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('User not found.');
		}

		if (!$this->passwords->verify($password, $row->password)) {
			throw new Nette\Security\AuthenticationException('Invalid password.');
		}

		return new SimpleIdentity(
			$row->id,
			null, // nebo pole více rolí
			['name' => $row->username],
		);
	}
}
