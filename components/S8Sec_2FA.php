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
		return null;
	}

	function generate_qr() {
		return null;
	}
	function validate_code($code) {
		return null;
	}
}