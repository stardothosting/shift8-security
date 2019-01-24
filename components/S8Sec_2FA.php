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
	public $secretFactory;

	function generate($name) {
		$site_hostname = parse_url(get_site_url());
    	$this->secretFactory = new \Dolondro\GoogleAuthenticator\SecretFactory();
    	$this->secret = $this->secretFactory->create($name, $site_hostname);
    	$this->shift8_2fa_secret = $this->secret->getSecretKey();

    	$qrImageGenerator = new \Dolondro\GoogleAuthenticator\QrImageGenerator\EndroidQrImageGenerator();
    	$this->shift8_2fa_qr = $qrImageGenerator->generateUri($this->secret);
    	return array(
    		'secret' => $this->shift8_2fa_secret,
    		'qr_img' => $this->shift8_2fa_qr,
    	);
	}
}