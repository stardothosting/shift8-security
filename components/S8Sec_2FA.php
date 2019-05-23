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
	public $shift8_2fa_code;
	public $secretFactory;

	function generate() {
		$site_hostname = parse_url(get_site_url());
		$name = esc_attr(get_option('shift8_security_2fa_description'));
    	$secretFactory = new \Dolondro\GoogleAuthenticator\SecretFactory();
    	$secret = $secretFactory->create($name, $site_hostname);
    	$shift8_2fa_secret = $secret->getSecretKey();
		return $shift8_2fa_secret;
	}

	function generate_qr() {
		$qrImageGenerator = new \Dolondro\GoogleAuthenticator\QrImageGenerator\EndroidQrImageGenerator();
		$secretKey = esc_attr(get_option('shift8_security_2fa_secret'));
    	//$shift8_2fa_qr = $qrImageGenerator->generateUri($secretKey);
    	$shift8_2fa_qr = $qrImageGenerator->generateUri($secret);
    	return $secretKey;
	}
	function validate_code($code) {
		$googleAuthenticator = new \Dolondro\GoogleAuthenticator\GoogleAuthenticator();
		$secretKey = esc_attr(get_option('shift8_security_2fa_secret'));
		return $googleAuthenticator->authenticate($secretKey, $code);
	}
}