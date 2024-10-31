<?php 
/**
 * 
 *  Other smtp script
 * 
 */
namespace Orangewp\Smtp\Provider;
use Orangewp\Smtp\Includes\Message;

if ( ! defined( 'ABSPATH' ) ) exit;

class OtherSmtp{

   use \Orangewp\Smtp\Includes\Smtptools;  //We define trait   
   private $message;
    /**
    * Callback function for handling other SMTP fields.
    */
   public function other_fields_callback() {
       $this->message = new Message();
       $options = get_option("smtp_service");
       $selectedSmtp = $this->getSelectedsmtptools();

       // Check if the selected SMTP service is 'other'.
       if ($selectedSmtp === 'other') {
           // Check if other SMTP settings are provided.
           if (isset($options["other_smtp_host"]) && 
               isset($options["other_smtp_username"]) && 
               isset($options["other_smtp_password"]) &&
               !empty($options["other_smtp_host"]) &&
               !empty($options["other_smtp_username"]) &&
               !empty($options["other_smtp_password"])) {
               // Other SMTP settings are valid.
               $this->message->success_message();
           } else {
               // Other SMTP settings are incomplete or missing.
               $this->message->error_message();
           }
       }            
    ?>  
 
    <div id="other_callback">
      <div class="smtp-tool">
         <h2><?php esc_html_e("Configure your Other Tools connection","orange-smtp")?></h2>
      </div> 
     <div class="smtp-tools">  
      <table class="form-table" role="presentation">
       <tr id="other_smtp_host">
          <td>
            <label><?php esc_html_e("SMTP Host","orange-smtp")?> </label> 
            <p><i><?php esc_html_e("The SMTP server which will be used to send email. For example: smtp.mail.yahoo.com",
                   "orange-smtp")?> </i></p>
            <p><i><?php esc_html_e("or smtp-mail.outlook.com","orange-smtp")?> </i></p>       
             <?php $value = isset($options['other_smtp_host']) ? sanitize_text_field($options['other_smtp_host']) : '';
                echo "<input type='text' id='other_smtp_host' name='smtp_service[other_smtp_host]' 
                value='".esc_attr($value,'orange-smtp')."' />"?>
          </td>          
          <td>
           <label><?php esc_html_e("SMTP Username","orange-smtp")?> </label>
           <p><i><?php esc_html_e("The SMTP server email address For example: abc@mail.com","orange-smtp")?></i></p>
             <?php $value = isset($options['other_smtp_username']) ? sanitize_text_field($options['other_smtp_username']) : '';
                echo "<input type='text' name='smtp_service[other_smtp_username]' value='".esc_attr($value,'orange-smtp')."' />";
                ?>       
          </td>
       </tr>
       <tr id="other_smtp_password">
          <td>
          <label><?php esc_html_e("SMTP Password","orange-smtp")?></label>
          <p><i><?php esc_html_e("Your SMTP Password","orange-smtp")?></i></p>
          <?php 
               $value = isset($options['other_smtp_password']) ? sanitize_text_field($options['other_smtp_password']) : '';
                echo "<input type='password' name='smtp_service[other_smtp_password]' value='".esc_attr($value,'orange-smtp')."' />";
          ?>             
          </td>
          <td>
          <label><?php esc_html_e("Type of Encryption","orange-smtp"); ?></label>
          <p><i><?php esc_html_e("The encryption recommended would TLS.","orange-smtp"); ?></i></p>
             <?php
                $value = isset($options["other_smtp_encryp"])? sanitize_text_field($options["other_smtp_encryp"]) : "";
                
                // Define your select options
                $select_options = [
                    "tls" => "TLS",
                    "ssl" => "SSL",
                    "none" => "No Encryption",
                ];
                
                echo '<select name="smtp_service[other_smtp_encryp]" id="other_smtp_encryp_select">';
                foreach ($select_options as $option_value => $option_label) {
                    $selected = selected($value, $option_value, false);
                    echo "<option value=" .
                        esc_attr($option_value, "orange-smtp") .
                        esc_attr($selected, "orange-smtp") .">" .
                        esc_html($option_label) . 
                        "</option>";
                }
                echo "</select>";
                ?>
           
          </td>
       </tr>
       <tr id="other_smtp_port">
          <td>
           <label> <?php esc_html_e("SMTP Port", "orange-smtp"); ?> </label>
            <p><i><?php esc_html_e("If you choose encryption type TLS, it should be set to 587. For SSL 465 instead.","orange-smtp"); ?></i></p>
             <?php
                $value = isset($options["other_smtp_port"])? sanitize_text_field($options["other_smtp_port"]) : "";
                echo "<input type='text' name='smtp_service[other_smtp_port]' id='other_smtp_port_input' value='" .
                esc_attr($value) ."'/>";                                
                ?>
          </td>
       </tr>
    </table> 
     </div>
   </div>

    <?php     
  }  

}