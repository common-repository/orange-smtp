<?php
/**
 * 
 *  Mailgun mailer script 
 * 
*/
namespace Orangewp\Smtp\Provider;
use Orangewp\Smtp\Includes\Message;

if ( ! defined( 'ABSPATH' ) ) exit;

Class MailgunSmtp{

    use \Orangewp\Smtp\Includes\Smtptools;  //We define trait   
    private $message;

    public function mailgun_fields_callback(){
        
        $this->message = new Message();
        $options = get_option("smtp_service");
        $selectedSmtp = $this->getSelectedsmtptools();  //gmail smtp tools active
        if ($selectedSmtp === 'mailgun') {

        if ( ( isset($options["mailgun_username"]))  && $options["mailgun_password"] !=='') 
            {
                $this->message->success_message();
            }else{
                $this->message->error_message();
            }
        }
    ?>

<div id="mailgun_callback">         
     <div class="smtp-tool">
       <h2><?php esc_html_e("Configure your Mailgun SMTP connection","orange-smtp")?></h2>
     </div>  
        
 <div class="smtp-tools">
    <table class="form-table" role="presentation">
        <tr id="mailgun_username">
             <td>
               <label><?php esc_html_e("Username", "orange-smtp") ?> </label>
               <p><i><?php esc_html_e("Recommended Only Valid Mailgun SMTP Username", 'orange-smtp') ?></i></p>
               <?php $value = isset($options['mailgun_username']) ? sanitize_text_field($options['mailgun_username']) : '';
                echo "<input type='text'id='mailgun_username' name='smtp_service[mailgun_username]' value='" . esc_attr($value, 'orange-smtp') . "' />"; ?> 
             </td>
             <td>
                 <label><?php esc_html_e("Password", 'orange-smtp') ?></labe>
                 <p><i><?php esc_html_e("Your SMTP Password", 'orange-smtp') ?></i></p> 
                    <?php $value = isset($options['mailgun_password']) ? $options['mailgun_password'] : '';
                        echo "<input type='password' id='mailgun_password' name='smtp_service[mailgun_password]' value='" . esc_attr($value, 'orange-smtp') . "' />";
                        ?>
                </td>
            </tr>
            <tr id="mailgun_private_key">
                <td>
                <label><?php esc_html_e("Private Key", "orange-smtp") ?></label>
                  <p><i><?php
                        $link_text = 'Get a Private API Key';
                        $mailgun_api_key_url = esc_url('https://app.mailgun.com/app/account/security/api_keys');
                        $link = sprintf('Click the link to get a private Api key.: 
                                        <a href="%s" target="_blank" rel="noopener noreferrer">%s</a>', $mailgun_api_key_url, $link_text);
                        echo wp_kses($link, array(
                            'a' => array(
                                'href' => array() ,
                                'target' => array() ,
                                'rel' => array() ,
                            ) ,
                            'br' => array() ,
                        ));
                        ?> 
                    </i></p>
                    <?php $value = isset($options['mailgun_private_key']) ? sanitize_text_field($options['mailgun_private_key']) : '';
                        echo "<input type='password' id='mailgun_private_key' name='smtp_service[mailgun_private_key]' value='" . esc_attr($value, 'orange-smtp') . "'/>";
                    ?> 
                    </td>
                <td>
                  <label><?php esc_html_e("Mailgun Domain Name", "orange-smtp") ?></label>
                  <p><i><?php esc_html_e("Your Mailgun Domain Name", 'orange-smtp') ?></i></p>
                    <?php  $value = isset($options['mailgun_smtp_domain']) ? sanitize_text_field($options['mailgun_smtp_domain']) : '';
                        echo "<input type='text' id='mailgun_smtp_domain' name='smtp_service[mailgun_smtp_domain]' value='" . esc_attr($value, 'orange-smtp') . "' />";
                        ?>  
                </td>
            </tr>
            <tr id="mailgun_smtp_region">
                <td>
                <label><?php esc_html_e("Region", "orange-smtp") ?></label>
                    <p><i><?php
                            $region = "EU";
                            $endpoint_url = esc_url('https://www.mailgun.com/regions');
                            $link_text = 'Mailgun.com';
                            
                            $link = sprintf('Choose which endpoint you want to use for sending messages.You may be <br>obligated to use the EU region if you are working under EU laws.                               
                                                    <a href="%s" rel="" target="_blank">%s</a>', $endpoint_url, $link_text);
                            
                            echo wp_kses($link, array(
                                'a' => array(
                                    'href' => array() ,
                                    'target' => array() ,
                                    'rel' => array() ,
                                ) ,
                                'br' => array() ,
                            ));
                            
                            ?>
                     </i></p>
                    <?php
                        $value = isset($options['mailgun_smtp_region']) ? sanitize_text_field($options['mailgun_smtp_region']) : '';
                        $region_options = array(
                            'us' => 'US',
                            'eu' => 'EU',
                        );
                        ?> 
                    <select id="mailgun_smtp_region" name="smtp_service[mailgun_smtp_region]">
                        <?php foreach ($region_options as $option_value => $option_label): ?> 
                        <option value="
                        <?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>> <?php echo esc_html($option_label); ?> </option>
                        <?php
                        endforeach; ?> 
                    </select>
                     
                </td>
                <td>
                <label> <?php esc_html_e("Type of Encryption","orange-smtp"); ?> </label>
                <p><i><?php esc_html_e("The encryption recommended would TLS.","orange-smtp"); ?></i></p>
                <?php
                $value = isset($options["mailgun_smtp_encryp"])? sanitize_text_field($options["mailgun_smtp_encryp"]) : "";

                // Define your select options
                $select_options = [
                    "tls" => "TLS",
                    "ssl" => "SSL",
                    "none" => "No Encryption",
                ];

                echo '<select name="smtp_service[mailgun_smtp_encryp]" id="gmail_smtp_encryp_select">';
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
            <tr id="mailgun_smtp_port">
              <td>
            <label><?php esc_html_e("SMTP Port", "orange-smtp"); ?></label>
            <p><i><?php esc_html_e("If you choose encryption type TLS, it should be set to 587. For SSL 465 instead.","orange-smtp"); ?></i></p>    
                <?php
                $value = isset($options["mailgun_smtp_port"])? sanitize_text_field($options["mailgun_smtp_port"]) : "";
                echo "<input type='text' name='smtp_service[mailgun_smtp_port]' value='" .esc_attr($value) ."'/>";                                
                ?>
                </td>
            </tr>
        </table>
    </div> 
  </div>             
    <?php
    }
 }  // end the class 