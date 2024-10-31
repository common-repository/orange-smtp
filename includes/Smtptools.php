<?php 
/**
 * 
 * Which tools are currently active following script is working for this.
 * 
*/
namespace Orangewp\Smtp\Includes;
if ( ! defined( 'ABSPATH' ) ) exit;

trait Smtptools {

    public function getSelectedsmtptools() {
        $options = get_option('smtp_service');
        $selected_smtp = isset($options['smtp_service_tools']) ? sanitize_text_field($options['smtp_service_tools']) : 'default';
        return $selected_smtp;
    }
}