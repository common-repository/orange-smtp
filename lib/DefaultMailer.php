<?php 
/**
* 
*  Default mailer connection script
*
**/
namespace Orangewp\Smtp\lib;
if ( ! defined( 'ABSPATH' ) ) exit;

class DefaultMailer {

   private $success_message;
   private $error_message;

   public function __construct() {
      $this->success_message = '';
      $this->error_message = '';
      $this->default_smtpmail_page();
   }

 public function default_smtpmail_page() { 

      $options = get_option('smtp_service');
   
      if (isset($_POST["smtp_mailer_send_test_email"])){   
        if (isset($_POST['deafult_mailtest_name']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash ( $_POST['deafult_mailtest_name'] ) ), 'default_mailtest_action')) {

         $defaultname = $options['default_email'];
            
        $email = '';
        if(isset($_POST['smtp_email_test_option_name']) && !empty($_POST['smtp_email_test_option_name'])){
            $email = sanitize_email($_POST['smtp_email_test_option_name']);
        }
        $subject = '';
        if(isset($_POST['email_Test_subject']) && !empty($_POST['email_Test_subject'])){
            $subject = sanitize_text_field($_POST['email_Test_subject']);
        }
        $message = '';
        if(isset($_POST['smtp_email_test_message']) && !empty($_POST['smtp_email_test_message'])){
            $message = sanitize_text_field($_POST['smtp_email_test_message']);
      }

      //Send email
      $to = $email; // Change this to your email address
      $subject = $subject;
      $headers = "From: <$email>";
      $sent = wp_mail($to, $subject, $message, $headers);

      if ($sent) {

          //recipient email and sender name insert failed into database   
          global $wpdb;
          $table_name = $wpdb->prefix . 'orgsmtp_logs';
          
          // Define the data to insert
          $data_to_insert = array(
              'recipient_email' => $wpdb->prepare('%s', $to),//$to,
              'subject'   =>  $wpdb->prepare('%s', $subject), //$subject,
              'message' =>  $wpdb->prepare('%s', $message), //$message,
              'status' => 'success',
              'sender_name' => $wpdb->prepare('%s', $defaultname), //$defaultname,

          );
          //Insert the data into the database table
          $wpdb->insert($table_name, $data_to_insert); //db call ok
          $this->success_message = esc_html_e("Your message has been sent successfully!","orange-smtp");
        
          } else {

          //recipient email and sender name insert failed into database   
          global $wpdb;
          $table_name = $wpdb->prefix . 'orgsmtp_logs';

          // Define the data to insert
          $data_to_insert = array(
              'recipient_email' => $wpdb->prepare('%s', $to),//$to,
              'subject'   =>  $wpdb->prepare('%s', $subject), //$subject,
              'message' =>  $wpdb->prepare('%s', $message), //$message,
              'status' => 'failed',
              'sender_name' => $wpdb->prepare('%s', $defaultname), //$defaultname,

          );   

          //Insert the data into the database table
          $wpdb->insert($table_name, $data_to_insert); //db call ok
          $this->error_message =  esc_html_e("There was an error sending your message.","orange-smtp");
       }
      } 
    }
 }

  public function default_contact_form () {
    ?>
    <h2 class='orange-smtp-header'><?php esc_html_e('Default SMTP Test Mail','orange-smtp')?></h2>
    <form method="post" action="">
      <?php wp_nonce_field('default_mailtest_action', 'deafult_mailtest_name'); ?>
      <?php if (!empty($this->error_message)) : ?>
      <p class="error"><?php echo esc_html( $this->error_message ); ?></p>
      <?php endif; ?>
      <?php if (!empty($this->success_message)) : ?>
      <p class="success"><?php echo esc_html( $this->success_message ); ?></p>
    <?php endif; ?>
   
   <div class="orange_email"> 
         
      <table class="form-table">
           <tbody>
              <tr valign="top">
                 <th scope="row"><label for="smtp_email_test_option_name"><?php esc_html_e('To', 'orange-smtp');?></label></th>
                 <td>
                    <input name="smtp_email_test_option_name" type="email" id="smtp_email_test_option_name" value="" class="regular-text" required>
                    <p class="description"><i><?php esc_html_e('Email address of the recipient', 'orange-smtp');?></i></p>
                 </td>
              </tr>
              <tr valign="top">
                 <th scope="row"><label for="email_Test_subject"><?php esc_html_e('Subject', 'orange-smtp');?></label></th>
                 <td>
                    <input name="email_Test_subject" type="text" id="email_Test_subject" value="" class="regular-text" required>
                    <p class="description"><i><?php esc_html_e('Subject of the email', 'orange-smtp');?></i></p>
                 </td>
              </tr>
              <tr valign="top">
                 <th scope="row"><label for="smtp_email_test_message"><?php esc_html_e('Message', 'orange-smtp');?></label></th>
                 <td>
                    <textarea name="smtp_email_test_message" id="smtp_email_test_message" rows="6" cols="48" required></textarea>
                    <p class="description"><i><?php esc_html_e('Email body', 'orange-smtp');?></i></p>
                 </td>
              </tr>
            
            <tr valign="top">
               <th scope="row"><label for="toggleFields">Advanced Settings</label></th>
               <td>
                  <label for="toggleFields" class="switch">
                     <input type="checkbox" id="toggleFields" name="toggleFields"> <!-- Add "checked" here -->
                     <span class="slider round"></span>
                  </label>
               </td>
            </tr>

              <!-- Fields to be revealed (initially hidden) -->
              <tr valign="top" class="hidden-field" style="display: none;">
                 <th scope="row"><label for="deafult_gmail_test_reply"> <?php esc_html_e('Reply-To Email Address', 'orange-smtp');?> </label></th>
                 <td><input name="deafult_gmail_test_reply" type="email" id="deafult_gmail_test_reply" value="" class="regular-text">
                 </td>
              </tr>
              <tr valign="top" class="hidden-field" style="display: none;">
                 <th scope="row"><label for="deafult_gmail_test_bcc"><?php esc_html_e('BCC Email Address', 'orange-smtp');?> </label></th>
                 <td><input name="deafult_gmail_test_bcc" type="text" id="deafult_gmail_test_bcc"  value="" class="regular-text">
                 </td>
              </tr>
              <tr valign="top" class="hidden-field" style="display: none;">
                 <th scope="row"><label for="deafult_gmail_test_cc"><?php esc_html_e('CC Email Address', 'orange-smtp');?></label></th>
                 <td><input name="deafult_gmail_test_cc" type="text" id="deafult_gmail_test_cc" value="" class="regular-text">
                 </td>
              </tr>
           </tbody>
        </table>
</div>        
      <p class="submit"><input type="submit" name="smtp_mailer_send_test_email" id="smtp_mailer_send_test_email" class="button button-primary" value="<?php esc_html_e('Send Email', 'orange-smtp');?>"></p>
    </form>
<?php 
  } 
}