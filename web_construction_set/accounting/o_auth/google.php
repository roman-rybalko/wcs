<?php

namespace WebConstructionSet\Accounting\OAuth;

/**
 * Google OAuth (2.0)
 */
class Google extends \WebConstructionSet\Accounting\OAuth\OAuth2 {
	public function __construct($authData) {
		parent::__construct('https://accounts.google.com/o/oauth2/auth', 'https://accounts.google.com/o/oauth2/token', $authData);
	}
}

?>