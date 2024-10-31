<?php
/**
 *
 *  Gmail smtp script
 *
 */
namespace Orangewp\Smtp\Provider;
use Orangewp\Smtp\Includes\Message;

if ( ! defined( 'ABSPATH' ) ) exit;

class GmailSmtp
    {   
        use \Orangewp\Smtp\Includes\Smtptools;  //We define trait   
        private $message;

        public function gmail_fields_callback()
        {
            $this->message = new Message();
            $options = get_option("smtp_service");
            $selectedSmtp = $this->getSelectedsmtptools();  //gmail smtp tools active
            if ($selectedSmtp === 'gmail') {
        
            if ( ( isset($options["gmail_smtp_user"])) && $options["gmail_smtp_password"] !=='') 
                {
                    $this->message->success_message();
                }else{
                    $this->message->error_message();
                }
            } 
        ?>          

  <div id="gmail_callback">         
    <div class="smtp-tool">
      <h2><?php esc_html_e("Configure your Gmail SMTP connection","orange-smtp")?></h2>
    </div>         
   <div class="smtp-tools">
   <table class="form-table" role="presentation">

      <tr id="gmail_smtp_user">
       <td>
         <label><?php esc_html_e("SMTP Username", "orange-smtp"); ?></label>
       <p><i><?php esc_html_e("The Gmail SMTP server email address For example: xyz@gmail.com","orange-smtp"); ?></i></p>
             <?php
                if (isset($options["gmail_smtp_user"]) && stristr($options["gmail_smtp_user"], "@gmail.com") !==
                    false
                ) {
                    $value = esc_attr($options["gmail_smtp_user"]);
                } else {
                    $value = "";
                }
                echo "<input type='text' name='smtp_service[gmail_smtp_user]' value='" .esc_attr($value) ."' />";
                ?> 
          </td>
          <td>
            
          <label><?php esc_html_e("SMTP Password", "orange-smtp"); ?></label>
          <p><i><?php esc_html_e("Your SMTP Password","orange-smtp"); ?>
             </i></p>
             <?php
                $value = isset($options["gmail_smtp_password"])? $options["gmail_smtp_password"]: "";
                echo "<input type='password' name='smtp_service[gmail_smtp_password]' value='".esc_attr($value,'orange-smtp')."' />";
                ?>
             
          </td> 
      </tr>
       
       <tr id="gmail_smtp_encryp">
          <td>
           <label><?php esc_html_e("Type of Encryption","orange-smtp"); ?></label>
           <p><i><?php esc_html_e("The encryption recommended would TLS.","orange-smtp"); ?></i></p>
             <?php
                $value = isset($options["gmail_smtp_encryp"])? sanitize_text_field($options["gmail_smtp_encryp"]) : "";
                
                // Define your select options
                $select_options = [
                    "tls" => "TLS",
                    "ssl" => "SSL",
                    "none" => "No Encryption",
                ];
                
                echo '<select name="smtp_service[gmail_smtp_encryp]" id="gmail_smtp_encryp_select">';
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
          <td>
          <label><i><?php esc_html_e("SMTP Port", "orange-smtp"); ?></i></label>
          <p><i><?php esc_html_e("If you choose encryption type TLS, it should be set to 587. For SSL 465 instead.","orange-smtp"); ?></i></p>
             <?php
                $value = isset($options["gmail_smtp_port"])? sanitize_text_field($options["gmail_smtp_port"]) : "";
                echo "<input type='text' name='smtp_service[gmail_smtp_port]' id='gmail_smtp_port_input' value='" .
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