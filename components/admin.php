<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Admin welcome page
if (!function_exists('shift8_main_page')) {
	function shift8_main_page() {
	?>
	<div class="wrap">
	<h2>Shift8 Plugins</h2>
	Shift8 is a Toronto based web development and design company. We specialize in Wordpress development and love to contribute back to the Wordpress community whenever we can! You can see more about us by visiting <a href="https://www.shift8web.ca" target="_new">our website</a>.
	</div>
	<?php
	}
}

// Admin settings page
function shift8_security_settings_page() {
?>
<div class="wrap">
<h2>Shift8 Security Settings</h2>
<?php if (is_admin()) { 
$active_tab = (esc_attr($_GET[ 'tab' ]) != null ? esc_attr($_GET[ 'tab' ]) : 'core_options');
$plugin_data = get_plugin_data( S8SEC_DIR . '/shift8-security.php');
$plugin_name = $plugin_data['TextDomain'];
?>
<h2 class="nav-tab-wrapper">
    <a href="?page=<?php echo $plugin_name; ?>%2Fcomponents%2Fsettings.php%2Fcustom&tab=core_options" class="nav-tab <?php echo $active_tab == 'core_options' ? 'nav-tab-active' : ''; ?>">Core Options</a>
    <a href="?page=<?php echo $plugin_name; ?>%2Fcomponents%2Fsettings.php%2Fcustom&tab=twofactor_options" class="nav-tab <?php echo $active_tab == 'twofactor_options' ? 'nav-tab-active' : ''; ?>">Two Factor Auth</a>
    <a href="?page=<?php echo $plugin_name; ?>%2Fcomponents%2Fsettings.php%2Fcustom&tab=scanblock_options" class="nav-tab <?php echo $active_tab == 'scanblock_options' ? 'nav-tab-active' : ''; ?>">Scan Blocking</a>
</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'shift8-security-settings-group' ); ?>
    <?php do_settings_sections( 'shift8-security-settings-group' ); ?>
    <table class="form-table">
    <!-- CORE SETTINGS -->
    <tbody class="<?php echo $active_tab == 'core_options' ? 'shift8-security-admin-tab-active' : 'shift8-security-admin-tab-inactive'; ?>">
    <tr valign="top">
    <th scope="row">Core Settings</th>
	</tr>
	<tr valign="top">
    <td><span id="shift8-security-notice">
    </span></td>
	</tr>
	<tr valign="top">
	<td>Enable Shift8 Security : </td>
	<td>
	<?php 
	if (esc_attr( get_option('shift8_security_enabled') ) == 'on') { 
		$enabled_checked = "checked";
	} else {
		$enabled_checked = "";
	}
	?>
    <label class="switch">
    <input type="checkbox" name="shift8_security_enabled" <?php echo $enabled_checked; ?>>
    <div class="slider round"></div>
    </label>
	</td>
	</tr>
    <!-- 2FA SETTINGS -->
    <tbody class="<?php echo $active_tab == 'twofactor_options' ? 'shift8-security-admin-tab-active' : 'shift8-security-admin-tab-inactive'; ?>">
    <tr valign="top">
    <th scope="row">Two Factor Authentication Settings</th>
    </tr>
    <tr valign="top">
    <td><span id="shift8-security-notice">
    </span></td>
    </tr>
    <tr valign="top">
    <td>Enable 2FA (Free OTP) Security : </td>
    <td>
    <?php
    if (esc_attr( get_option('shift8_security_2fa_enabled') ) == 'on') {
        $enabled_2fa_checked = "checked";
    } else {
        $enabled_2fa_checked = "";
    }
    ?>
    <label class="switch">
    <input type="checkbox" name="shift8_security_2fa_enabled" <?php echo $enabled_2fa_checked; ?>>
    <div class="slider round"></div>
    </label>
    </td>
    </tr>
    <!-- WPSCAN OPTIONS -->
    <tbody class="<?php echo $active_tab == 'scanblock_options' ? 'shift8-security-admin-tab-active' : 'shift8-security-admin-tab-inactive'; ?>">
    <tr valign="top">
    <th scope="row">WPScan Options</th>
    </tr>
    <tr valign="top">
    <td>Block basic WPScan probes : </td>
    <td>
    <?php
    if (esc_attr( get_option('shift8_security_wpscan_basic') ) == 'on') {
        $enabled_wpscan_basic_checked = "checked";
    } else {
        $enabled_wpscan_basic_checked = "";
    }
    ?>
    <label class="switch">
    <input type="checkbox" name="shift8_security_wpscan_basic" <?php echo $enabled_wpscan_basic_checked; ?>>
    <div class="slider round"></div>
    </label>
    </td>
    </tr>
    <tr valign="top">
    <td>Block WPScan plugin enumeration : </td>
    <td>
    <?php
    if (esc_attr( get_option('shift8_security_wpscan_eap') ) == 'on') {
        $enabled_wpscan_eap_checked = "checked";
    } else {
        $enabled_wpscan_eap_checked = "";
    }
    ?>
    <label class="switch">
    <input type="checkbox" name="shift8_security_wpscan_eap" <?php echo $enabled_wpscan_eap_checked; ?>>
    <div class="slider round"></div>
    </label>
    </td>
    </tr>
	</table>
    <?php submit_button(); ?>
</form>
</div>
<?php 
	} // is_admin
}
