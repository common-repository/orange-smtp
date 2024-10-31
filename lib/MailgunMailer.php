<?php
/**
 * 
 *  Mailgun mailer script 
 * 
*/
namespace Orangewp\Smtp\lib;
if ( ! defined( 'ABSPATH' ) ) exit;

use Mailgun\Mailgun;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class MailgunMailer{

    private $smtpHost;

    use \Orangewp\Smtp\Includes\Smtptools;  //We define trait  

    public function __construct() {     
        if (session_status() == PHP_SESSION_NONE) { 
            session_start();
        }
        $this->msmtp_mailgun_smtp_setting();
    }   



      //Mailgun SMTP setting
      public function msmtp_mailgun_smtp_setting() { 

          //$this->smttptools();  

        if (isset($_POST["mailgun_mailer_send_email"])) {   
          if ( ! isset($_POST['msmtp_mailgun_name']) || ! wp_verify_nonce(sanitize_text_field( wp_unslash ( $_POST['msmtp_mailgun_name'] ) ), 'msmtp_mailgun_action'))    {     
  
          $msmtp_mailgun_testemail = isset($_POST['msmtp_mailgun_testemail']) ? sanitize_text_field( wp_unslash ($_POST['msmtp_mailgun_testemail'])) : '';

          if (!empty($msmtp_mailgun_testemail)) {
                  return;
          }
          $_POST['msmtp_mailgun_testemail'] = time();        
              
          // mailgun SMTP settings from options 
              $msmtp_mailgun = get_option('smtp_service');
              //Mailgun Test Mail send  
              $subject = sanitize_text_field($_POST["msmtp_mailgun_subject_address"]);
              $email = sanitize_email($_POST["msmtp_mailgun_email"]);
              $replyemail = sanitize_email($_POST["msmtp_mailgun_reply"]);
              $bccemail = sanitize_email($_POST["msmtp_mailgun_bcc"]);
              $ccemail = sanitize_email($_POST["msmtp_mailgun_cc"]);
              $plainsms = sanitize_textarea_field($_POST["msmtp_mailgun_plaintext_message"]);
  
              // Check if email address is provided
              if (empty($email)) {
                  // Handle the case where the email address is empty
                  $_SESSION['oerror_message'] = esc_html_e("Email address is required!", "orange-smtp");
  
              }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                  $_SESSION['gerror_message'] = esc_html_e('Invalid email address. Please provide a valid email address.','orange-smtp');                      
              } else {
                  // Load PHPMailer library
                  require_once(ABSPATH . WPINC . "/PHPMailer/PHPMailer.php");
                  require_once(ABSPATH . WPINC . "/PHPMailer/Exception.php");
                  require_once(ABSPATH . WPINC . "/PHPMailer/SMTP.php");
  
                  $mailgunsmtp = new PHPMailer;
                
              // Set the SMTP server settings
                  $mailgunsmtp->isSMTP();
                  $mailgunsmtp->SMTPAuth = true; 
                        
                    if ('us' === trim($msmtp_mailgun['mailgun_smtp_region'])) {
                        $mailgunsmtp->Host = 'smtp.mailgun.org';  // mailgun US SMTP server address
                    } elseif ('eu' === trim($msmtp_mailgun['mailgun_smtp_region'])) {
                        $mailgunsmtp->Host = 'smtp.eu.mailgun.org';  // mailgun SMTP server address
                    }

                  //$mailgunsmtp->Host = $this->smtpHost;  

                  $mailgunsmtp->Username = $msmtp_mailgun['mailgun_username']; // Your SMTP username
                  $mailgunsmtp->Password = $msmtp_mailgun['mailgun_password']; // Your SMTP password
                  $mailgunsmtp->SMTPSecure =$msmtp_mailgun['mailgun_secrtype']; // TLS or SSL
  
                  if ('ssl' === $msmtp_mailgun['mailgun_secrtype']) {
                      // For SSL-only connections, use 465
                      $mailgunsmtp->Port = 465;
                  } else {
                      // Otherwise, use 587.
                      $mailgunsmtp->Port = 587;
                  }
  
                    // Set additional settings
                    $mailgunsmtp->SMTPDebug = 0;
                    $mailgunsmtp->Debugoutput = 'html';
                    $mailgunsmtp->IsHTML( true );
                  

                    // Set the sender and recipient information
                    $mailgunsmtp->From = $msmtp_mailgun['mailgun_fromemail'];
                    $mailgunsmtp->FromName = $msmtp_mailgun['mailgun_fromname'];
                    $mailgunsmtp->addAddress($email);
                    
                    //Force email & Name
                    $mailnamevalue = isset($msmtp_mailgun["mailgun_force_name"]) ? $msmtp_mailgun["mailgun_force_name"] : '';
                    $mailemailvalue = isset($msmtp_mailgun["mailgun_force_email"]) ? $msmtp_mailgun["mailgun_force_email"] : '';


                    if ('1' === $mailnamevalue && '1' === $mailemailvalue ) {

                    $mailgunsmtp->setFrom($msmtp_mailgun['mailgun_fromemail'],  $msmtp_mailgun['mailgun_fromname']);
                    $mailgunsmtp->addAddress($email,'');       
                    
                    }elseif('1' === $mailnamevalue){

                    $mailgunsmtp->addReplyTo($email);
                    $mailgunsmtp->setFrom($msmtp_mailgun['mailgun_fromemail'], $msmtp_mailgun['mailgun_fromname']);
                    $mailgunsmtp->addAddress($email,'');          

                    }else if ('1' === $mailemailvalue) {
    
                    $mailgunsmtp->addReplyTo($email);
                    $mailgunsmtp->setFrom($msmtp_mailgun['mailgun_fromemail'], 'Notifications');
                    $mailgunsmtp->addAddress($email,'');          

                    }else {

                    $mailgunsmtp->setFrom($msmtp_mailgun['mailgun_fromemail'], 'Notifications');
                    $mailgunsmtp->addAddress($email,'');
                    $mailgunsmtp->addReplyTo($email,'');
                    $mailgunsmtp->Sender = $email;  
                 }

                    // Set email subject and body
                    $mailgunsmtp->Subject = $subject;
                    $mailgunsmtp->Body = $plainsms;

                    // Send the email
                    if ($mailgunsmtp->Send()) {
                        // Email sent successfully

                    global $wpdb;  
                    $table_name = $wpdb->prefix . 'orgsmtp_logs';
                    // Define the data to insert
                    $data_to_insert = array(
                        'recipient_email' => $email,
                        'sender_name' => $msmtp_mailgun['mailgun_fromemail'], // Replace with the correct key
                        'subject'   => $subject,
                        'reply_email' => $replyemail,
                        'bcc_email' => $bccemail,
                        'cc_email' => $ccemail,
                        'message' => $plainsms,
                        'status' => 'success'
                    );
                    // Insert the data into the database table
                      $wpdb->insert($table_name, $data_to_insert);  //db call ok
                    //add_option($table_name, $data_to_insert );

                    $_SESSION['osuccess_message'] = esc_html_e("Your message has been sent successfully!", "orange-smtp");
                  } else {
                        // Failed to send email

                    global $wpdb;  
                    $table_name = $wpdb->prefix . 'orgsmtp_logs';
                    // Define the data to insert
                    $data_to_insert = array(
                        'recipient_email' => $email,
                        'sender_name' => $msmtp_mailgun['mailgun_fromemail'], // Replace with the correct key
                        'subject'   => $subject,
                        'reply_email' => $replyemail,
                        'bcc_email' => $bccemail,
                        'cc_email' => $ccemail,
                        'message' => $plainsms,
                        'status' => 'failed'
                    );
                    // Insert the data into the database table
                    $wpdb->insert($table_name, $data_to_insert);  //db call ok
                    
                    $_SESSION['oerror_message'] = esc_html_e("Failed to send email", "orange-smtp");
            }  
          }   
       } 
     }
  }

    public function mailgun_contact_form() {
    ?>
    <h2 class='orange-smtp-header'><?php esc_html_e('Mailgun SMTP Test Mail','orange-smtp')?></h2>
    <form method="post" action="">
        <?php wp_nonce_field('msmtp_mailgun_action', 'msmtp_mailgun_name'); ?>

        <?php if (isset($_SESSION['oerror_message'])) : ?>
            <p class="error"><?php echo esc_html($_SESSION['oerror_message']); ?></p>
            <?php unset($_SESSION['oerror_message']); // Clear the session variable ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['osuccess_message'])) : ?>
            <p class="success"><p><?php echo esc_html( $_SESSION['osuccess_message'] ); ?></p></p>
            <?php unset($_SESSION['osuccess_message']); // Clear the session variable ?>
        <?php endif; ?>
    
    <div class="orange_email">                
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="msmtp_mailgun_email"><?php esc_html_e('To', 'orange-smtp');?></label></th>
                        <td><input name="msmtp_mailgun_email" type="email" id="msmtp_mailgun_email" value="" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Email address of the recipient', 'orange-smtp');?></p></td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="msmtp_mailgun_subject_address"><?php esc_html_e('Subject', 'orange-smtp');?></label></th>
                        <td><input name="msmtp_mailgun_subject_address" type="text" id="msmtp_mailgun_subject_address" value="" class="regular-text" required>
                            <p class="description"><?php esc_html_e('Subject of the email', 'orange-smtp');?></p></td>
                    </tr>                               
                        <tr valign="top" id="msmtp_textarea">
                            <th scope="row"><label for="msmtp_mailgun_plaintext_message"><?php esc_html_e('Message', 'orange-smtp');?></label></th>
                            <td>
                            <textarea name="msmtp_mailgun_plaintext_message" id="msmtp_mailgun_plaintext_message" cols="50" rows="6">  </textarea>
                                <p class="description"><?php esc_html_e('Email body', 'orange-smtp');?></p>
                            </td>
                        </tr>
                <tr valign="top">
                    <th scope="row"><label for="toggleFields">Advanced Settings</label></th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" id="toggleFields"> <!-- Add "checked" here -->
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>

                <!-- Fields to be revealed (initially hidden) -->
                <tr valign="top" class="hidden-field" style="display: none;">
                    <th scope="row"><label for="msmtp_mailgun_reply"> <?php esc_html_e('Reply-To Email Address', 'orange-smtp');?> </label></th>
                    <td><input name="msmtp_mailgun_reply" type="email" id="msmtp_mailgun_reply" value="" class="regular-text">
                    </td>
                </tr>

                <tr valign="top" class="hidden-field" style="display: none;">
                    <th scope="row"><label for="msmtp_mailgun_bcc"><?php esc_html_e('BCC Email Address', 'orange-smtp');?> </label></th>
                    <td><input name="msmtp_mailgun_bcc" type="text" id="msmtp_mailgun_bcc" value="" class="regular-text">
                    </td>
                </tr>

                <tr valign="top" class="hidden-field" style="display: none;">
                    <th scope="row"><label for="msmtp_mailgun_cc"><?php esc_html_e('CC Email Address', 'orange-smtp');?></label></th>
                    <td><input name="msmtp_mailgun_cc" type="text" id="msmtp_mailgun_cc" value="" class="regular-text">
                    </td>
                </tr> 
                </tbody>
            </table>
    </div>        
        <input type="hidden" name="msmtp_mailgun_testemail" id="msmtp_mailgun_testemail" value="">
        <p class="submit"><input type="submit" name="mailgun_mailer_send_email" id="mailgun_mailer_send_email" class="button button-primary" value="<?php esc_html_e('Test Email', 'orange-smtp');?>"></p>
    </form>

    <?php
  }
} /*end the class*/