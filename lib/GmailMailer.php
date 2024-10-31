<?php
/**
* 
*  Gmail Mailer connection script
*
**/
namespace Orangewp\Smtp\lib;

if ( ! defined( 'ABSPATH' ) ) exit;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class GmailMailer {
    
    private $smtpHost;

    use \Orangewp\Smtp\Includes\Smtptools;  //We define trait  

    public function __construct() {
      if (session_status() == PHP_SESSION_NONE) { 
        session_start();
       }
        $this->send_smtp_phpmailer();
    }

    public function smttptools(){
        $selectedSmtp = $this->getSelectedsmtptools();  //gmail smtp tools active     

        if ($selectedSmtp === "gmail") {            
           $this->smtpHost="smtp.google.com";
         } elseif ($selectedSmtp === "mailgun") {
            $this->smtpHost = "smtp.mailgun.com";
        }    
         
    }

    public function send_smtp_phpmailer() {

        $this->smttptools();  

        if (isset($_POST["gmail_mailer_send_email"])) {
            
        if ( ! isset($_POST['gsmtp_gmailtest_name']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['gsmtp_gmailtest_name'] ) ), 'gsmtp_gmailtest_action')) {
            $gsmtp_gmail_testemail = isset($_POST['gsmtp_gmail_testemail']) ? sanitize_text_field($_POST['gsmtp_gmail_testemail']) : '';

            if (!empty($gsmtp_gmail_testemail)) {
                return;
            }
            $_POST['gsmtp_gmail_testemail'] = time();

            $options = get_option('smtp_service');

            require_once(ABSPATH . WPINC . "/PHPMailer/PHPMailer.php");
            require_once(ABSPATH . WPINC . "/PHPMailer/Exception.php");
            require_once(ABSPATH . WPINC . "/PHPMailer/SMTP.php");

            $gwpsmtp = new PHPMailer;

            // SMTP settings
            $gwpsmtp->IsSMTP();
            if (isset($options['gmail_smtp_host']) && !empty($options['gmail_smtp_host'])) {
                $gwpsmtp->Host = $this->smtpHost; //$options['gmail_smtp_host'];
            }

            // send plain text test email
			$gwpsmtp->ContentType = 'text/plain';
			$gwpsmtp->IsHTML( false );

            // SMTP auth check
            if (isset($options['gmail_smtp_auth']) && $options['gmail_smtp_auth'] == true) {
                $gwpsmtp->SMTPAuth = true;
                $gwpsmtp->Username = $options['gmail_smtp_user'];
                $gwpsmtp->Password = $options['gmail_smtp_password'];
            }

            // Set encryption
            if (isset($options['gmail_smtp_encryption']) && !empty($options['gmail_smtp_encryption'])) {
                $gmail_of_encryption = $options['gmail_smtp_encryption'];
                if ($gmail_of_encryption == "none") {
                    $gmail_of_encryption = '';
                }
                $gwpsmtp->SMTPSecure = $gmail_of_encryption;
            }

            if (isset($options['gmail_smtp_port']) && !empty($options['gmail_smtp_port'])) {
                $gwpsmtp->Port = $options['gmail_smtp_port'];
            }

            $gwpsmtp->SMTPDebug = 0;
            $gwpsmtp->Debugoutput = 'html';

            $subject = sanitize_text_field($_POST["gsmtp_gmail_Test_subject_address"]);
            $email = sanitize_email($_POST["gsmtp_gmail_email_test"]);
            $replyemail = sanitize_email($_POST["gsmtp_gmail_test_reply"]);
            $bccemail = sanitize_email($_POST["gsmtp_gmail_test_bcc"]);
            $ccemail = sanitize_email($_POST["gsmtp_gmail_test_cc"]);
            $message = sanitize_textarea_field($_POST["gsmtp_gmail_test_message"]);

            // Validate the recipient's email address
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['gerror_message'] = esc_html_e('Invalid email address. Please provide a valid email address.','orange-smtp');
            } else {
                
            $namevalue = isset($options["g_force_name"]) ? $options["g_force_name"] : '';
            $emailvalue = isset($options["g_force_email"]) ? $options["g_force_email"] : '';    
         

            if ('1' === $namevalue && '1' === $emailvalue) {

                $gwpsmtp->setFrom($options['gmail_smtp_from_email'],  $options['gmail_smtp_from_name']);
                $gwpsmtp->addAddress($email,'');

            } else if('1' === $namevalue){
                
                $gwpsmtp->addReplyTo($email);
                $gwpsmtp->setFrom($options['gmail_smtp_from_email'], $options['gmail_smtp_from_name']);
                $gwpsmtp->addAddress($email,'');

            }else if ('1' === $emailvalue) {
                
                $gwpsmtp->setFrom($options['gmail_smtp_from_email'], 'Notifications');
                $gwpsmtp->addAddress($email,'');   

            } else {
                
                $gwpsmtp->setFrom($options['gmail_smtp_from_email'], 'Notifications');
                $gwpsmtp->addAddress($email,'');
                $gwpsmtp->addReplyTo($email,'');
                $gwpsmtp->Sender = $email;  
            }    

            //Add reply-to if set in settings.
			if ( ! empty($replyemail) ) {
				$gwpsmtp->AddReplyTo($replyemail, $options['gmail_smtp_from_name']);
			}

            if (!empty($replyemail) ) {
				$gwpsmtp->AddReplyTo($replyemail, '');
            }    

			//Add BCC if set in settings.
			if ( ! empty($bccemail) ) {
				$bcc_emails = explode( ',', $bccemail);
				foreach ( $bcc_emails as $bcc_email ) {
					$bcc_email = trim( $bcc_email );
					$gwpsmtp->AddBcc( $bcc_email );
				}
			}
            //Add CC if set in settings.
            if ( ! empty($ccemail) ) {
				$cc_emails = explode( ',', $ccemail);
				foreach ( $cc_emails as $cc_email ) {
					$cc_email = trim( $cc_email );
                    $gwpsmtp->AddCC($cc_email);
				}
			}

            //Set email subject and body
            $gwpsmtp->Subject = $subject;
            $gwpsmtp->Body = $message;

                if ( isset( $attachments ) && ! empty( $attachments ) ) {
                    foreach ( $attachments as $filename => $attachment ) {
                            $filename = is_string( $filename ) ? $filename : '';        
                            try {
                                    $gwpsmtp->addAttachment($attachment, $filename);
                            } catch ( PHPMailer\PHPMailer\Exception $e ) {
                                    continue;
                            }
                    }   
                }

                // Send the email
                if ($gwpsmtp->Send()) {
                      //recipient email and sender name insert Success into database
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'orgsmtp_logs';
           
                    // Define the data to insert
                    $data_to_insert = array(
                        'recipient_email' => $email,
                        'sender_name' => $options['gmail_smtp_from_name'],
                        'subject'   => $subject,
                        'reply_email' => $replyemail,
                        'bcc_email' => $bccemail,
                        'cc_email' => $ccemail,
                        'message' => $message,
                        'status' => 'success'
                    );
                    //Insert the data into the database table
                    $wpdb->insert($table_name, $data_to_insert); //db call ok
                    //add_option($table_name, $data_to_insert );

                        $_SESSION['gsuccess_message'] = esc_html_e("Your message has been sent successfully!", "orange-smtp");
                    } else {

                         //recipient email and sender name insert failed into database   
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'orgsmtp_logs';

                        // Define the data to insert
                        $data_to_insert = array(
                            'recipient_email' => $email,
                            'sender_name' => $options['gmail_smtp_from_name'],
                            'subject'   => $subject,
                            'reply_email' => $replyemail,
                            'bcc_email' => $bccemail,
                            'cc_email' => $ccemail,
                            'message' => $message,
                            'status' => 'failed'
                        );
                        //Insert the data into the database table
                        $wpdb->insert($table_name, $data_to_insert); //db call ok
                        //add_option($table_name, $data_to_insert );
                        
                        $_SESSION['gerror_message'] = esc_html_e("Failed to send email. Gmail", "orange-smtp");
                }
            }
        }
      }
    }
    
    public function gmail_contact_form() {
        ?>

    <h2 class='orange-smtp-header'><?php esc_html_e('Gmail SMTP Test Mail','orange-smtp')?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('gsmtp_gmailtest_action', 'gsmtp_gmailtest_name'); ?>

            <?php if (isset($_SESSION['gerror_message'])) : ?>
                <p class="error"><?php echo esc_html($_SESSION['gerror_message']); ?></p>
                <?php unset($_SESSION['gerror_message']); // Clear the session variable ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['gsuccess_message'])) : ?>
                <p class="success"><?php echo esc_html($_SESSION['gsuccess_message']); ?></p>
                <?php unset($_SESSION['gsuccess_message']); // Clear the session variable ?>
            <?php endif; ?>

    <div class="orange_email">            
            <table class="form-table">
               <tbody>
                  <tr valign="top">
                     <th scope="row"><label for="gsmtp_gmail_email_test"><?php esc_html_e('To', 'orange-smtp');?></label></th>
                     <td>
                        <input name="gsmtp_gmail_email_test" type="email" id="gsmtp_gmail_email_test" value="" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Email address of the recipient', 'orange-smtp');?></p>
                     </td>
                  </tr>
                  <tr valign="top">
                     <th scope="row"><label for="gsmtp_gmail_Test_subject_address"><?php esc_html_e('Subject', 'orange-smtp');?></label></th>
                     <td>
                        <input name="gsmtp_gmail_Test_subject_address" type="text" id="gsmtp_gmail_Test_subject_address" value="" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Subject of the email', 'orange-smtp');?></p>
                     </td>
                  </tr>
                  <tr valign="top">
                     <th scope="row"><label for="gsmtp_gmail_test_message"><?php esc_html_e('Message', 'orange-smtp');?></label></th>
                     <td>
                        <textarea name="gsmtp_gmail_test_message" id="gsmtp_gmail_test_message" rows="6" cols="48" required></textarea>
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
                     <th scope="row"><label for="gsmtp_gmail_test_reply"> <?php esc_html_e('Reply-To Email Address', 'orange-smtp');?> </label></th>
                     <td><input name="gsmtp_gmail_test_reply" type="email" id="gsmtp_gmail_test_reply" value="" class="regular-text">
                     </td>
                  </tr>
                  <tr valign="top" class="hidden-field" style="display: none;">
                     <th scope="row"><label for="gsmtp_gmail_test_bcc"><?php esc_html_e('BCC Email Address', 'orange-smtp');?> </label></th>
                     <td><input name="gsmtp_gmail_test_bcc" type="text" id="gsmtp_gmail_test_bcc" value="" class="regular-text">
                     </td>
                  </tr>
                  <tr valign="top" class="hidden-field" style="display: none;">
                     <th scope="row"><label for="gsmtp_gmail_test_cc"><?php esc_html_e('CC Email Address', 'orange-smtp');?></label></th>
                     <td><input name="gsmtp_gmail_test_cc" type="text" id="gsmtp_gmail_test_cc" value="" class="regular-text">
                     </td>
                  </tr>
               </tbody>
             </table>
             </div>         
            <input type="hidden" name="gsmtp_gmail_testemail" id="gsmtp_gmail_testemail" value="">
            <p class="submit"><input type="submit" name="gmail_mailer_send_email" id="gmail_mailer_send_email" class="button button-primary" value="<?php esc_html_e('Test Email', 'orange-smtp');?>"></p>
        </form>
        <?php
    }
} 