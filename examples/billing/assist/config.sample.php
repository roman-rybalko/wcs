<?php

class Config {
	const DB_DSN = 'mysql:host=localhost;dbname=XXX';
	const DB_USER = 'XXX';
	const DB_PASSWORD = 'XXX';
	const ASSIST_SERVER = 'test.paysecure.ru';
	const ASSIST_MERCHANT_ID = 123;
	const ASSIST_LOGIN = 'XX';
	const ASSIST_PASSWORD = 'XXXX';
	const ASSIST_SECRET_WORD = 'XXXXX';
	const ASSIST_SECRET_KEY = 'file:///tmp/assist.key';  /// requires working openssl_sign() - it's often broken
}
