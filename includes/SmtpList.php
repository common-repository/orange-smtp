<?php
/**
 *
 *  STMP TOOL CONNECTIONS
 *
 **/
namespace Orangewp\Smtp\Includes;

if ( ! defined( 'ABSPATH' ) ) exit;

use Orangewp\Smtp\Provider\GmailSmtp;
use Orangewp\Smtp\Provider\DefaultSmtp;
use Orangewp\Smtp\Provider\OtherSmtp;
use Orangewp\Smtp\Provider\MailgunSmtp;
use Orangewp\Smtp\Provider\ProVersion;
use Orangewp\Smtp\Provider\SendgridSmtp;
use Orangewp\Smtp\Provider\PostmarkSmtp;
use Orangewp\Smtp\Provider\SparkpostSmtp; 
use Orangewp\Smtp\Provider\Smtpprovider; 
use Orangewp\Smtp\Includes\Sendersettings;

class SmtpList
{
    private $gmail_settings;
    private $default_settings;
    private $other_settings;
    private $mailgun_settings;
    private $pro_settings;
    private $sendgrid_settings;
    private $postmark_settings;
    private $sparkpost_settings;
    private $smtp_settings; 
    private $sender;

    public function __construct()
    {
        $this->gmail_settings = new GmailSmtp();
        $this->default_settings = new DefaultSmtp();
        $this->other_settings = new OtherSmtp();
        $this->mailgun_settings = new MailgunSmtp();
        $this->pro_settings = new ProVersion();
        $this->sendgrid_settings = new SendgridSmtp();
        $this->postmark_settings = new PostmarkSmtp();
        $this->sparkpost_settings = new SparkpostSmtp();
        $this->smtp_settings = new Smtpprovider();
        $this->register_settings_section();   
    }

    //Register a Settings Section
    public function register_settings_section()
    {   
        add_settings_section(
            "orange_smtp_service_tools_section","",
            [$this, "orange_smtp_settings_section_callback"], "smtp-settings");

        register_setting("smtp_service_options", "smtp_service", [$this,"smtp_sanitize",]);

        add_settings_field("orange_smtp_service_tools_field", " ", [$this, "orange_smtp_settings_field_callback"],
            "smtp-settings",
            "orange_smtp_service_tools_section"
        );
    }

    public function orange_smtp_settings_section_callback()
    {
    ?>    
      <div class="orange-wrapper">  

        <div class='orange-smtp-header'>
         <h2><p><?php esc_html_e('Select your','orange-smtp')?> </p>
         <p><?php esc_html_e('smtp service provider','orange-smtp')?> </p></h2>
         <h4><?php esc_html_e('You are configuring your email service provider connection','orange-smtp')?></h4>
        </div>    
    <?php    
    }

    public function orange_smtp_settings_field_callback()
    {
        $options = get_option("smtp_service");
        $selected_service = isset($options["smtp_service_tools"]) ? $options["smtp_service_tools"] : "default";

        $services = [
            "default" => "Default",
            "smtp" => "SMTP",
            "brevo" => "Brevo",
            "gmail" => "Gmail",
            "mailgun" => "Mailgun",
            "zoho"    => "Zoho Mail",
            "sendgrid" => "Sendgrid",
            "postmark" => "Postmark",
            "aws"  => "Amazon Ses",
            "sparkpost" => "Sparkpost",
            "sendlayer" => "Sendlayer",
            "other" => "Other",
        ];

        // Add a CSS class for styling
        echo "<style>.radio-option { display: inherit;}</style>";
        echo "<div class='smtp_mailer-settings'>";
        $i = 1;
        $itemsPerRow = 2; // Change this to the number of items you want per row

        foreach (array_chunk($services, $itemsPerRow, true) as $rowItems) {
            echo "<div class='smtp_mailer_row'>";
            foreach ($rowItems as $service_key => $service_label) {
                
                $checked = checked($selected_service, $service_key, false);
                $image_url = plugins_url("../assets/images/" . $service_key . ".svg", __FILE__);
                $isDisabled = in_array($service_key, ['brevo', 'zoho', 'aws', 'sendlayer']);

                echo "<div class='smtp_mailer_item' id='orangesmtp" . esc_attr($i++) . "'>";
                echo "<label>";
                echo "<img src='" . esc_url($image_url) . "' alt='" . esc_attr($service_label) . "' class='toollist'>";
                echo "<input type='radio' id='orangesmtp" . esc_attr($i++) . "' name='smtp_service[smtp_service_tools]' value='" . esc_attr($service_key) . "' " . esc_attr($checked) .
                ($isDisabled ? ' disabled' : '') . " data-service-key='" . esc_attr($service_key) . "'>";
                echo esc_html($service_label);
                echo "</label>";
                echo "</div>";
            }
            echo "</div>";
        }

        echo "<div class='orange_overlay' id='orange_overlay'></div>";
        echo "<div id='orange_popup' class='orange_popup'>";
        echo  "<span class='orange_close' id='closePopup' onclick='closePopup'>&times;</span>";
                    
            $this->pro_settings->pro_callback();
            
        echo "</div>";
        echo "</div>";

        //$this->default_settings->default_api_key_callback();  // default smtp tools
        $this->gmail_settings->gmail_fields_callback();      // gmail smtp tools 
        $this->other_settings->other_fields_callback();     // Other smtp tools 
        $this->mailgun_settings->mailgun_fields_callback(); // mailgun smtp tools 
        $this->sendgrid_settings->sendgrid_callback();     // sendgrid smtp tools
        $this->postmark_settings->postmark_callback();    //postmark smtp tools
        $this->sparkpost_settings->sparkpost_callback(); //sparkpost smtp tools
        $this->smtp_settings->smtp_callback();  //smtp tools 
        $this->sender = new Sendersettings();  //email sender setting default
    }

    //Sanitize Callback Function (optional)
    public function smtp_sanitize($input_field)
    {
        // Verify nonce
        if (
            !isset($_POST["smtpsettings_nonce"]) ||
            !wp_verify_nonce($_POST["smtpsettings_nonce"], "smtpsettings_nonce")
        ) {
            return $input_field;
        }

        // Sanitize and validate each field
        $smtp_sanitized_field = [];

        // Sanitize and validate smtp_service_tools
        if (isset($input_field["smtp_service_tools"])) {
            $smtp_service_tools = sanitize_text_field(
                $input_field["smtp_service_tools"]
            );

            // Validate smtp_service_tools against allowed values
            $allowed_services = ["default", "gmail", "other"];
            if (in_array($smtp_service_tools, $allowed_services)) {
                $smtp_sanitized_field["smtp_service_tools"] = $smtp_service_tools;
            }
        }

        /**
         *
         * Sanitize and validate other fields based on the selected service
         *
         */

        if ($smtp_sanitized_field["smtp_service_tools"] === "default") {

            if (isset($input_field["defaultsmtp"])) {$smtp_sanitized_field["defaultsmtp"] = sanitize_text_field(
                    $input_field["defaultsmtp"]);
            }

            if (isset($input_field["df_force_name"])) {
                $smtp_sanitized_field["df_force_name"] = sanitize_text_field($input_field["df_force_name"]);
            }

            if (isset($input_field["df_force_email"])) {
                $smtp_sanitized_field["df_force_email"] = sanitize_text_field($input_field["df_force_email"]);
            }

        } elseif ($smtp_sanitized_field["smtp_service_tools"] === "gmail") {

            if (isset($input_field["gmail_smtp_user"])) { $smtp_sanitized_field["gmail_smtp_user"] = sanitize_text_field($input_field["gmail_smtp_user"]);
            }

            if (isset($input_field["gmail_smtp_password"])) {
                $smtp_sanitized_field["gmail_smtp_password"] = sanitize_text_field($input_field["gmail_smtp_password"]);
            }

            if (isset($input_field["gmail_smtp_encryp"])) {
                $smtp_sanitized_field["gmail_smtp_encryp"] = sanitize_text_field($input_field["gmail_smtp_encryp"]);

            }
            if (isset($input_field["gmail_smtp_port"])) {
                $smtp_sanitized_field["gmail_smtp_port"] = sanitize_text_field($input_field["gmail_smtp_port"]);
            }

            if (isset($input_field["smtp_from_email"])) {$smtp_sanitized_field["smtp_from_email"] 
                = sanitize_text_field($input_field["smtp_from_email"]);
            }

            if (isset($input_field["smtp_from_name"])) {$smtp_sanitized_field["smtp_from_name"] 
                = sanitize_text_field($input_field["smtp_from_name"]);
            }


          } elseif ($smtp_sanitized_field["smtp_service_tools"] === "smtp") {
            
            if (isset($input_field["smtp_username"])) {$smtp_sanitized_field["smtp_username"] 
                = sanitize_text_field($input_field["smtp_username"]);
            }

            if (isset($input_field["smtp_password"])) {$smtp_sanitized_field["smtp_password"] 
                = sanitize_text_field($input_field["smtp_password"]);
            }

            if (isset($input_field["smtp_encryp"])) {$smtp_sanitized_field["smtp_encryp"] 
                = sanitize_text_field($input_field["smtp_encryp"]);
            }

            if (isset($input_field["smtp_port"])) {$smtp_sanitized_field["smtp_port"] 
                = sanitize_text_field($input_field["smtp_port"]);
            }            

          } elseif ($smtp_sanitized_field["smtp_service_tools"] === "mailgun") {

            if (isset($input_field["mailgun_username"])) {
                $smtp_sanitized_field["mailgun_username"] = sanitize_text_field($input_field["mailgun_username"]);
            }

            if (isset($input_field["mailgun_password"])) {
                $smtp_sanitized_field["mailgun_password"] = sanitize_text_field($input_field["mailgun_password"]);
            }

            if (isset($input_field["mailgun_private_key"])) {
                $smtp_sanitized_field[ "mailgun_private_key"] = sanitize_text_field($input_field["mailgun_private_key"]);
            }

            if (isset($input_field["mailgun_smtp_domain"])) {
                $smtp_sanitized_field["mailgun_smtp_domain"] = sanitize_text_field($input_field["mailgun_smtp_domain"]);
            }

            if (isset($input_field["mailgun_smtp_region"])) {
                $smtp_sanitized_field["mailgun_smtp_region"] = sanitize_text_field($input_field["mailgun_smtp_region"]);
            }


            if (isset($input_field["mailgun_smtp_encryp"])) {
                $smtp_sanitized_field["mailgun_smtp_encryp"] = sanitize_text_field($input_field["mailgun_smtp_encryp"]);
            }

            if (isset($input_field["mailgun_smtp_port"])) {
                $smtp_sanitized_field["mailgun_smtp_port"] = sanitize_text_field($input_field["mailgun_smtp_port"]);
            }
            
        } elseif ($smtp_sanitized_field["smtp_service_tools"] === "other") {

            if (isset($input_field["other_smtp_host"])) {$smtp_sanitized_field["other_smtp_host"] = sanitize_text_field(
                $input_field["other_smtp_host"]);
            }

            if (isset($input_field["other_smtp_username"])) {
                $smtp_sanitized_field["other_smtp_username"] = sanitize_text_field($input_field["other_smtp_username"]);
            }

            if (isset($input_field["other_smtp_password"])) {
                $smtp_sanitized_field["other_smtp_password"] = sanitize_text_field($input_field["other_smtp_password"]);
            }

            if (isset($input_field["other_smtp_encryp"])) {
                $smtp_sanitized_field["other_smtp_encryp"] = sanitize_text_field($input_field["other_smtp_encryp"]);
            }

            if (isset($input_field["other_smtp_port"])) {
                $smtp_sanitized_field["other_smtp_port"] = sanitize_text_field($input_field["other_smtp_port"]);
            }            


        } elseif ($smtp_sanitized_field["smtp_service_tools"] === "sendgrid") {

            if (isset($input_field["sdgd_smtp_username"])) {
                $smtp_sanitized_field["sdgd_smtp_username"] = sanitize_text_field($input_field["sdgd_smtp_username"]);
            }

            if (isset($input_field["sdgd_smtp_password"])) {
                $smtp_sanitized_field["sdgd_smtp_password"] = sanitize_text_field($input_field["sdgd_smtp_password"]);
            }

            if (isset($input_field["sdgd_smtp_encryp"])) {
                $smtp_sanitized_field["sdgd_smtp_encryp"] = sanitize_text_field($input_field["sdgd_smtp_encryp"]);
            }

            if (isset($input_field["sdgd_smtp_port"])) {
                $smtp_sanitized_field["sdgd_smtp_port"] = sanitize_text_field($input_field["sdgd_smtp_port"]);
            }

            
        } elseif ($smtp_sanitized_field["smtp_service_tools"] === "sparkpost") {

            if (isset($input_field["spk_smtp_username"])) {
                $smtp_sanitized_field["spk_smtp_username"] = sanitize_text_field($input_field["spk_smtp_username"]);
            }

            if (isset($input_field["skp_smtp_password"])) {
                $smtp_sanitized_field["skp_smtp_password"] = sanitize_text_field($input_field["skp_smtp_password"]);
            }

            if (isset($input_field["skp_api_key"])) {
                $smtp_sanitized_field["skp_api_key"] = sanitize_text_field($input_field["skp_api_key"]);
            }

            if (isset($input_field["skp_smtp_encryp"])) {
                $smtp_sanitized_field["skp_smtp_encryp"] = sanitize_text_field($input_field["skp_smtp_encryp"]);
            }

            if (isset($input_field["skp_smtp_port"])) {
                $smtp_sanitized_field["skp_smtp_port"] = sanitize_text_field($input_field["skp_smtp_port"]);
            }      

        }elseif ($smtp_sanitized_field["smtp_service_tools"] === "postmark") {

            if (isset($input_field["pmak_smtp_username"])) {
                $smtp_sanitized_field["pmak_smtp_username"] = sanitize_text_field($input_field["pmak_smtp_username"]);
            }
            if (isset($input_field["pmak_smtp_password"])) {
                $smtp_sanitized_field["pmak_smtp_password"] = sanitize_text_field($input_field["pmak_smtp_password"]);
            }
            if (isset($input_field["pmak_api_token"])) {
                $smtp_sanitized_field["pmak_api_token"] = sanitize_text_field($input_field["pmak_api_token"]);
            }

            if (isset($input_field["pmak_smtp_encryp"])) {
                $smtp_sanitized_field["pmak_smtp_encryp"] = sanitize_text_field($input_field["pmak_smtp_encryp"]);
            }

            if (isset($input_field["pmak_smtp_port"])) {
                $smtp_sanitized_field["pmak_smtp_port"] = sanitize_text_field($input_field["pmak_smtp_port"]);
            }

        } 

        return $smtp_sanitized_field;
        // Return the sanitized and validated input
    }

    //Create the Settings Page Callback
    public function smtplist_tools_page()
    {
    ?>
      <div class="wrap">
      <form method="post" action="options.php">
                <?php
                wp_nonce_field("smtptools_action", "smtp_tools_name");
                settings_fields("smtp_service_options");
                do_settings_sections("smtp-settings");
                submit_button();?>
      </form>
        </div>
    </div> 
     <?php
    }

} /*End the class*/