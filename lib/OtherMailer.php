<?php
/**
* 
*  Other Mailer connection script
*
**/
namespace Orangewp\Smtp\lib;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class OtherMailer {

    public function __construct() {
          
        if (session_status() == PHP_SESSION_NONE) { 
             session_start();
            }
          $this->other_smtp_phpmailer();
    }

    public function other_smtp_phpmailer() {
        // Check if the form is submitted
        if (isset($_POST["other_mailer_send_email"])) {
            // Check nonce verification
            if (isset($_POST['org_othertest_name']) || ! wp_verify_nonce( sanitize_text_field ( wp_unslash ( $_POST['org_othertest_name'] ) ), 'org_othertest_action')) {

                $org_other_testemail = isset($_POST['org_other_testemail']) ? sanitize_text_field($_POST['org_other_testemail']) : '';

                if (!empty($org_other_testemail)) {
                    return;
            }
            $_POST['org_other_testemail'] = time();
    
                // Get email parameters from the form
                $subject = sanitize_text_field($_POST["other_Test_subject_address"]);
                $email = sanitize_email($_POST["other_email_test"]);
                $replyemail = sanitize_email($_POST["other_test_reply"]);
                $bccemail = sanitize_email($_POST["other_test_bcc"]);
                $ccemail = sanitize_email($_POST["other_test_cc"]);
                $plainsms = sanitize_textarea_field($_POST["other_plain_text"]);
                // Check if email address is provided
                if (empty($email)) {
                    // Handle the case where the email address is empty
                    $_SESSION['oerror_message'] = esc_html_e("Email address is required!", "orange-smtp");

                }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['gerror_message'] = esc_html_e("Invalid email address. Please provide a valid email address.", "orange-smtp");                      
                } else {
                    // Load PHPMailer library
                    require_once(ABSPATH . WPINC . "/PHPMailer/PHPMailer.php");
                    require_once(ABSPATH . WPINC . "/PHPMailer/Exception.php");
                    require_once(ABSPATH . WPINC . "/PHPMailer/SMTP.php");

                    // Get SMTP settings from options
                    $options = get_option('smtp_service');

                    $orgwpsmtp = new PHPMailer;
                    // Set SMTP settings
                    $orgwpsmtp->IsSMTP();
                    $orgwpsmtp->Host = isset($options['other_smtp_host']) ? $options['other_smtp_host'] : '';
                    $orgwpsmtp->SMTPAuth = isset($options['other_smtp_auth']) ? $options['other_smtp_auth'] : false;
                    $orgwpsmtp->Username = isset($options['other_smtp_username']) ? $options['other_smtp_username'] : '';
                    $orgwpsmtp->Password = isset($options['other_smtp_password']) ? $options['other_smtp_password'] : '';
                    $orgwpsmtp->SMTPSecure = isset($options['other_smtp_encryp']) ? $options['other_smtp_encryp'] : '';
                    $orgwpsmtp->Port = isset($options['other_smtp_port']) ? $options['other_smtp_port'] : '';

                    // Set additional settings
                    $orgwpsmtp->SMTPDebug = 0;
                    $orgwpsmtp->Debugoutput = 'html';
                    $orgwpsmtp->IsHTML( true );
                    
                    $othernamevalue = isset($options["other_smtp_force_name"]) ? $options["other_smtp_force_name"] : '';
                    $otheremailvalue = isset($options["other_smtp_force_email"]) ? $options["other_smtp_force_email"] : '';    

                    // Set sender information
                    if (isset($options['other_smtp_from_email']) && isset($options['other_smtp_fromname'])) {
                        $orgwpsmtp->SetFrom($options['other_smtp_from_email'], $options['other_smtp_fromname']);
                    }

                    // Set email subject and body
                    $orgwpsmtp->Subject = $subject;
                    $orgwpsmtp->Body = $plainsms;

                    // Add recipients
                    $orgwpsmtp->AddAddress($email);
                    if (!empty($replyemail)) $orgwpsmtp->AddReplyTo($replyemail);
                    if (!empty($bccemail)) $orgwpsmtp->AddBCC($bccemail);
                    if (!empty($ccemail)) $orgwpsmtp->AddCC($ccemail);

                    // Send the email
                    if (!$orgwpsmtp->Send()) {
                        // Email sent successfully
                    global $wpdb;  
                    $table_name = $wpdb->prefix . 'orgsmtp_logs';
                    // Define the data to insert
                    $data_to_insert = array(
                        'recipient_email' => $email,
                        'sender_name' => $options['gmail_smtp_from_name'], // Replace with the correct key
                        'subject'   => $subject,
                        'reply_email' => $replyemail,
                        'bcc_email' => $bccemail,
                        'cc_email' => $ccemail,
                        'message' => $plainsms,
                        'status' => 'success'
                    );
                    // Insert the data into the database table
                    $wpdb->insert($table_name, $data_to_insert); //db call ok  
                    
                    $_SESSION['osuccess_message'] = esc_html_e("Your message has been sent successfully!", "orange-smtp");
                } else {
                    // Failed to send email
                    global $wpdb;  
                    $table_name = $wpdb->prefix . 'orgsmtp_logs';
                    // Define the data to insert
                    $data_to_insert = array(
                        'recipient_email' => $email,
                        'sender_name' => $options['gmail_smtp_from_name'], // Replace with the correct key
                        'subject'   => $subject,
                        'reply_email' => $replyemail,
                        'bcc_email' => $bccemail,
                        'cc_email' => $ccemail,
                        'message' => $plainsms,
                        'status' => 'failed'
                    );
                    // Insert the data into the database table
                    $wpdb->insert($table_name, $data_to_insert); //db call ok
                    
                    $_SESSION['oerror_message'] = esc_html_e("Failed to send email", "orange-smtp");
                    }
                }
            }
        }
    }   


    public function other_contact_form() {
        ?>
        <h2 class='orange-smtp-header'><?php esc_html_e('Other SMTP Test Mail','orange-smtp')?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('org_othertest_action', 'org_othertest_name'); ?>

            <?php if (isset($_SESSION['oerror_message'])) : ?>
                <p class="error"><?php echo esc_html($_SESSION['oerror_message']); ?></p>
                <?php unset($_SESSION['oerror_message']); // Clear the session variable ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['osuccess_message'])) : ?>
                <p class="success"><?php echo esc_html($_SESSION['osuccess_message']); ?></p>
                <?php unset($_SESSION['osuccess_message']); // Clear the session variable ?>
            <?php endif; ?>

    <div class="orange_email">            

        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="other_email_test"><?php esc_html_e('To', 'orange-smtp');?></label></th>
                    <td><input name="other_email_test" type="email" id="other_email_test" value="" class="regular-text" required>
                    <p class="description"><?php esc_html_e('Email address of the recipient', 'orange-smtp');?></p></td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="other_Test_subject_address"><?php esc_html_e('Subject', 'orange-smtp');?></label></th>
                    <td><input name="other_Test_subject_address" type="text" id="other_Test_subject_address" value="" class="regular-text" required>
                        <p class="description"><?php esc_html_e('Subject of the email', 'orange-smtp');?></p></td>
                </tr>                 
                <tr valign="top" id="org_textarea">
                    <th scope="row"><label for="other_plain_text"><?php esc_html_e('Message', 'orange-smtp');?></label></th>
                    <td>
                        <textarea name="other_plain_text" id="other_plain_text" cols="50" rows="6">  </textarea>
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
                <th scope="row"><label for="other_test_reply"> <?php esc_html_e('Reply-To Email Address', 'orange-smtp');?> </label></th>
                <td><input name="other_test_reply" type="email" id="other_test_reply" value="" class="regular-text">
                </td>
            </tr>

            <tr valign="top" class="hidden-field" style="display: none;">
                <th scope="row"><label for="other_test_bcc"><?php esc_html_e('BCC Email Address', 'orange-smtp');?> </label></th>
                <td><input name="other_test_bcc" type="text" id="other_test_bcc" value="" class="regular-text">
                </td>
            </tr>

            <tr valign="top" class="hidden-field" style="display: none;">
                <th scope="row"><label for="other_test_cc"><?php esc_html_e('CC Email Address', 'orange-smtp');?></label></th>
                <td><input name="other_test_cc" type="text" id="other_test_cc" value="" class="regular-text">
                </td>
            </tr> 

            </tbody>
        </table>
    </div>    
            <input type="hidden" name="org_other_testemail" id="org_other_testemail" value="">
            <p class="submit"><input type="submit" name="other_mailer_send_email" id="other_mailer_send_email" class="button button-primary" value="<?php esc_html_e('Test Email', 'orange-smtp');?>"></p>
        </form>
        <?php
    }
}