<?php 
/**
 * 
 *  email template script
 * 
 */    
namespace Orangewp\Smtp\Provider;

if ( ! defined( 'ABSPATH' ) ) exit;

class smtptemplate{

    public function emailtemplate(){
?>

 <div class="orange_template">
    <table class="ornage_table">
        <tr>
            <td>
                <h1> <?php esc_html_e("Select an email template","orange-smtp")?></h1><span>PRO</span> 
                <h5><?php esc_html_e("A collection of email templates for you to use.","orange-smtp")?></h5>
            </td>
        </tr>
    </table>
    <div class="orange_parent">

        <div class="orange_child" id="orangetemplate-1">
            <?php echo '<img src="' . esc_url(plugins_url('../assets/images/eCommerce.jpg', __FILE__)) . '" width="320" height="440" alt="orangesmtp">'?> 
            <div class="orange_infoText" id="orange_infoText-1">Pro Version</div>
            <div class="orange_infoText" id="orange_infoText-1">Pro Version22</div>
        </div>

        <div class="orange_child" id="orangetemplate-2">
            <?php echo '<img src="' . esc_url(plugins_url('../assets/images/marketing.jpg', __FILE__)) . '" width="320" height="440" alt="orangesmtp">'?> 
            <div class="orange_infoText" id="orange_infoText-2">Pro Version</div>
            <div class="orange_infoText" id="orange_infoText-2">Pro Version44</div>
        </div>

        <div class="orange_child" id="orangetemplate-3">
            <?php echo '<img src="' . esc_url(plugins_url('../assets/images/eCommerce.jpg', __FILE__)) . '" width="320" height="440" alt="orangesmtp">'?> 
            <div class="orange_infoText" id="orange_infoText-3">Pro Version</div>
            <div class="orange_infoText" id="orange_infoText-3">Pro Version55</div>
        </div>

        <div class="orange_child" id="orangetemplate-4">
            <?php echo '<img src="' . esc_url(plugins_url('../assets/images/offer.jpg', __FILE__)) . '" width="320" height="440" alt="orangesmtp">'?> 
            <div class="orange_infoText" id="orange_infoText-4">Pro Version</div>
            <div class="orange_infoText" id="orange_infoText-4">Pro Version666</div>
        </div>
    </div>
  </div>
 <?php
    }
}