<?php
/**
 * Logger General Settings
 *
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable
 *
 * @package Zephr
 */

?>
<div class="wrap ai-logger-settings">
	<h1><?php esc_html_e( 'Log Settings', 'ai-logger' ); ?></h1>
	<form method="post" action="options.php">
		<?php settings_fields( 'ai-logger' ); ?>
		<?php do_settings_sections( 'ai-logger' ); ?>
		<?php submit_button(); ?>
	</form>
</div>
