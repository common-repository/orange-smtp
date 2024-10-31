<?php 
/**
 * 
 *  Default smtp script
 * 
 */ 
namespace Orangewp\Smtp\Provider;
use Orangewp\Smtp\Includes\Message;

if ( ! defined( 'ABSPATH' ) ) exit;

class DefaultSmtp{

   use \Orangewp\Smtp\Includes\Smtptools;  //We define trait   
   private $message;

   public function default_api_key_callback() {
  
          $this->message = new Message();
          $options = get_option("smtp_service");
          $selectedSmtp = $this->getSelectedsmtptools();  //gmail smtp tools active

            if ($selectedSmtp === 'default') {
        
               if ( !empty($options)) 
                   {
                       $this->message->success_message();
                   }else{
                       $this->message->error_message();
                   }
         }     
    ?>

  <div id="default_callback">          
   <div class="smtp-tools">
      <table class="form-table general-setting">
         <tr>
            <td>
               <h2> <?php esc_html_e("Sender Settings", "orange-smtp"); ?> </h2>
            </td>
         </tr>
         <tr id="default_email">
            <th> <?php esc_html_e("From Email Address","orange-smtp")?> </th>
            <td> <?php        
                  $value = isset($options['default_email']) ? $options['default_email'] : '';
                  echo "<input type='text' name='smtp_service[default_email]' value='".esc_attr($value,'orange-smtp')."' />";
                  ?> <p>
               <i> <?php esc_html_e("Write the Email that will be used as the From Email","orange-smtp") ?>
               </p>
               </i>
            </td>
         </tr>
         <tr id="df_force_email">
            <th></th>
            <td> <?php 
                  $value = isset($options["df_force_email"]) ? $options["df_force_email"] : "";
                  $checked = !empty($value) ? 'checked' : '';
                  echo "<label for='smtp_service[df_force_email]'>";
                  echo '<input type="checkbox" id="smtp_service[df_force_email]" name="smtp_service[df_force_email]" value="1" ' . esc_attr( $checked ) . ' />';
                  esc_html_e("Force From Email","orange-smtp");
                  echo "</label>";
                  ?> <i>
               <p> <?php esc_html_e("If enabled, your specified From Email Address will be used for all outgoing emails, regardless of values set by other plugins.", "orange-smtp"); ?>
               </i>
               </p>
            </td>
         </tr>
         <tr id="default_name">
            <th> <?php esc_html_e("From Name","orange-smtp")?> </th>
            <td> <?php 
                  $value = isset($options['default_name']) ? $options['default_name'] : '';
                  echo "<input type='text' name='smtp_service[default_name]' value='".esc_attr($value,'orange-smtp')."' />";
                  ?> <p>
               <i> <?php esc_html_e("Write the name that will be used as the From Name", "orange-smtp") ?> </i>
               </p>
            </td>
         </tr>
         <tr id="df_force_email">
            <th></th>
            <td> <?php
                  $value = isset($options["df_force_name"]) ? $options["df_force_name"] : "";
                  $checked = !empty($value) ? 'checked' : '';
                  echo '<label for="smtp_service[df_force_name]">';
                  echo '<input type="checkbox" id="smtp_service[df_force_name]" name="smtp_service[df_force_name]" value="1" ' . esc_attr( $checked ) . '/>';
                  ?> <?php esc_html_e("Force From name Replacement","orange-smtp");
                  echo '</label>';
                  ?> </br>
                  
               <p>
               <i> <?php esc_html_e("If enabled, your specified From Email Address will be used for all outgoing emails, regardless of values set by other plugins.", "orange-smtp");?> </i>
               </p>
            </td>
         </tr>
      </table>
   </div> 
</div> 
<?php 
    }
  
}