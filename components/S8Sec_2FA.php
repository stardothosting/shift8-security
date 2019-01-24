<?php

/**
 * PHP Class for handling 2FA Secret and QR generation
 *
 * @author Michael Kliewe
 * @copyright 2012 Michael Kliewe
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 *
 * @link http://www.phpgangsta.de/
 */
class S8Sec_2FA
{

	public $shift8_2fa_secret;
	public $shift8_2fa_qr;

	function generate_secret($name) {
		var_dump($name);
		var_dump(wp_salt('auth'));
		exit(0);
		die();
    	$secretFactory = new \Dolondro\GoogleAuthenticator\SecretFactory();
    	$secret = $secretFactory->create($name, wp_salt('auth'));
    	$shift8_2fa_secret = $secret->getSecretKey();
    	return $shift8_2fa_secret;
	}

	function generate_qr() {
		$qrImageGenerator = new \Dolondro\GoogleAuthenticator\QrImageGenerator\EndroidQrImageGenerator();
		$shift8_2fa_qr = $qrImageGenerator->generateUri($shift8_2fa_secret);
	}

}