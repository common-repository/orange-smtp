<?php
/**
 * Plugin Name: Orange Smtp
 * Plugin URI: https://orangesmtp.com/orange-smtp
 * Description: Orange SMTP plugin.
 * Version: 1.0
 * Tested up to: 6.5
 * Author: Orangetoolz
 * Author URI: https://orangesmtp.com/
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  orange-smtp
 */

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Orangewp\Smtp\lib\TestMailer;
use Orangewp\Smtp\lib\Documentation;
use Orangewp\Smtp\Includes\SmtpList;
use Orangewp\Smtp\Includes\EmaillogList;
use Orangewp\Smtp\Provider\smtptemplate;


class Orange_Smtp
{       
    use \Orangewp\Smtp\Includes\Smtptools;  //We define trait    
    
    private $smtp_log;
    private $smtp_list;
    private $emailTemplate;  
    private $smtptemplate;
    private $testmailer;
    private $doc;
        
    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '1.2';

    public function __construct()
    {
        $this->orange_smtp_define_constants();
        add_action('admin_menu', [$this, 'orange_smtp_menu']);
        add_action('admin_init', [$this, 'orange_smtp_initialize_settings']);
        add_action('admin_enqueue_scripts', [$this, 'orange_smtp_enqueue_assets']);
        add_action('plugins_loaded', [$this,'load_plugin_textdomain']);     
        register_activation_hook(__FILE__, [$this, 'activate']); //create the db 
    }

    public function activate()
    {
        global $wpdb;
        $logs_table = $wpdb->prefix . 'orgsmtp_logs';
        $blukemail_table = $wpdb->prefix . 'orgsmtp_blukemail';
        $charset_collate = $wpdb->get_charset_collate();

        $logs = "CREATE TABLE $logs_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            sender_email VARCHAR(200) NOT NULL,
            recipient_email VARCHAR(200) NOT NULL,
            mailer VARCHAR(100) NOT NULL,
            opened VARCHAR(200) NOT NULL,
            clicked VARCHAR(100) NOT NULL,
            attachments VARCHAR(100) NOT NULL,
            esubject VARCHAR(200) NOT NULL,
            reply_email VARCHAR(200) NOT NULL,
            bcc_email VARCHAR(200) NOT NULL,
            cc_email VARCHAR(200) NOT NULL,
            emessage tinytext NOT NULL,
            estatus VARCHAR(200) NOT NULL,
            created_date VARCHAR(100) NOT NULL,
            created_time VARCHAR(100) NOT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($logs);

        $blukemail = "CREATE TABLE $blukemail_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            email VARCHAR(200) NOT NULL,
            PRIMARY KEY (id)
          ) $charset_collate;";
  
          require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
          dbDelta($blukemail);
    }
    
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
          'orange-smtp',
          false,
          dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
    /**
     * Define the menu and submenu
     *
     * @return void
     */

    public function orange_smtp_menu()
     {
        add_menu_page(
            __('Orange SMTP', 'orange-smtp'), // Page Title
            __('Orange SMTP', 'orange-smtp'), // Menu Title
            'manage_options', // Capability
            'orange-smtp', // Page slug
            array($this, 'smtp_setting_page'), // Callback to print HTML
            plugin_dir_url(__FILE__) . 'assets/images/orange_icon.png'
        );
         
         add_submenu_page(
            __('orange-smtp', 'orange-smtp'),
            __('Email Log List', 'orange-smtp'),
             'Email Log List',
             'manage_options',
             'orange-logs',
             array($this, 'orange_smtp_log_list')
         );
     }

    /**
     *
     * Define the required plugin constants
     *
     * @return void
     */

     public function orange_smtp_define_constants()
     {
         define('ORANGE_SMTP_VERSION', self::VERSION);
         define('ORANGE_SMTP_FILE', __FILE__);
         define('ORANGE_SMTP_PATH', __DIR__);
         define('ORANGE_SMTP_PLUGIN_DIR', plugin_dir_path(__FILE__));
         define('ORANGE_SMTP_URL', plugins_url('', ORANGE_SMTP_FILE));
         define('ORANGE_SMTP_ASSETS', ORANGE_SMTP_URL . '/assets');
 
     }

    /**
     *
     * Custom JS and CSS enqueue
     *
     * @return void
     */

     public function orange_smtp_enqueue_assets()
     {       
    
        $screen = get_current_screen();
        if ($screen->id === 'toplevel_page_orange-smtp' || $screen->id === 'orange-smtp_page_orange-logs') {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_style('orange-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', array(), time(), false);
        wp_enqueue_style('datatables-css', plugin_dir_url(__FILE__) . 'assets/css/datatables.min.css', array(), time(), false);
        wp_enqueue_script('orange-js', plugin_dir_url(__FILE__) . 'assets/js/custom.js', array('jquery'), time(), true);
        wp_enqueue_script('datatables-js', plugin_dir_url(__FILE__) . 'assets/js/datatables.min.js', array('jquery'), time(), true);
        }
     }
    
    /**
     * Tools radio button 
     *
     * @return void
     */
     
    public function orange_smtp_log_list(){  
        //email Log list   
        if (is_admin() ) {           
        ?>
        <div class="org-email-logs">
            <h2><?php esc_html_e("Email Logs","orange-smtp")?></h2>
            <?php 
             $this->smtp_log = new EmaillogList();
            ?>
        </div>
    <?php 
           }
        }      

    /**
     * mailer Class initialize
     *
     * @return void
     */

     public function orange_smtp_initialize_settings()
     {
            if ( is_admin() ) {
             $this->smtp_list = new SmtpList();
             $this->smtp_list->register_settings_section();
             $selectedSmtp = $this->getSelectedsmtptools();             
             $this->testmailer = new TestMailer();
         }
     }

    /**
     * Setting tab
     *
     * @return void
     */

    public function smtp_setting_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        // Get the active tab from the $_GET param
        $default_tab = 'config'; // Set your default tab here
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $default_tab;
        // Nonce verification code
        if ( !isset( $_GET['tab'] ) || ! wp_verify_nonce(sanitize_text_field( wp_unslash ($_GET['tab'] ))) ){

        echo '<div class="wrap">';
        echo '<div class="orange-smtp-navbar-header">';
        echo '<div class="orange-smtp-logo"><a href="' . esc_url( '?page=orange-smtp&tab=config' ) . '"><img src="' . esc_url(plugins_url('/assets/images/orange-logo.png', __FILE__)) . '" alt="orangesmtp"></a></div>'; // Add this line for the image
        // Here are our tabs
        echo '<nav class="nav-tab-wrapper">';
        echo '<a href="' . esc_url( '?page=orange-smtp&tab=config' ) . '" class="nav-tab ' . ( esc_attr( $tab ) === 'config' ? 'nav-tab-active' : '' ) . '"><span class="icon-config"></span>' . esc_html__( 'Configure', 'orange-smtp' ) . '</a>';
        echo '<a href="' . esc_url( '?page=orange-smtp&tab=emailtest' ) . '" class="nav-tab ' . ( esc_attr( $tab ) === 'emailtest' ? 'nav-tab-active' : '' ) . '"><span class="icon-emailtest"></span>' . esc_html__( 'Email Test', 'orange-smtp' ) . '</a>';
        echo '<a href="' . esc_url( '?page=orange-smtp&tab=smtptemplate' ) . '" class="nav-tab ' . ( esc_attr( $tab ) === 'smtptemplate' ? 'nav-tab-active' : '' ) . '"><span class="icon-smtptemplate"></span>' . esc_html__( 'Email Template', 'orange-smtp' ) . '</a>';
        echo '<a href="' . esc_url( '?page=orange-smtp&tab=doc' ) . '" class="nav-tab ' . ( esc_attr( $tab ) === 'doc' ? 'nav-tab-active' : '' ) . '"><span class="icon-doc"></span>' . esc_html__( 'Documentation', 'orange-smtp' ) . '</a>';
        echo '</nav>';

        echo '</div>';  
        echo '<div class="tab-content">';
        if ( is_admin() ) {
        switch ($tab):
            case 'config':               
                $this->smtp_list = new SmtpList();
                $this->smtp_list->smtplist_tools_page();
            break;
            case 'emailtest': 
                $this->testmailer = new TestMailer();
                $this->testmailer->test_mail_form();
            break;
            case 'smtptemplate':
                $this->smtptemplate = new smtptemplate();
                $this->smtptemplate->emailtemplate();
            break;

            case 'doc':
                $this->doc = new Documentation();
            break;
        endswitch;
        }
        
        echo '</div></div>';

        } else {
            // Nonce is not valid, then showing this error
            echo esc_html_e( "Nonce verification failed!", "orange-smtp");
        }
    }
}
// Initialize the plugin
new Orange_Smtp();