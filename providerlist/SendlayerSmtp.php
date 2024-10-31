<?php 
/**
 * 
 *  Sendlayer smtp script
 * 
 */    
namespace Orangewp\Smtp\Provider;

if ( ! defined( 'ABSPATH' ) ) exit;

class SendlayerSmtp{

    public function sendlayer_api_key_callback() {
?>             
    <table width="100%">
    <tr>
        <td> </td>
    </tr>
    <tr>
        <td class="orange_pro_title">
            <h3><?php esc_html_e( 'Sendlayer is', 'orange-smtp' ); ?> <span><?php esc_html_e( 'Pro Features', 'orange-smtp' ); ?></span></h3>
        </td>
    </tr>
    <tr> 
        <td class="orange_pro_description"><?php esc_html_e( "We're apology, sendlayer is not available on this free Plan. Please upgrade to our PRO Package to unlock all the awesome features.", 'orange-smtp' ); ?></td>
    </tr>
    <tr>
        <td>
            <div class="orange_pro_features"> <a href="#"><?php esc_html_e( 'Upgrade Pro Features', 'orange-smtp' ); ?> </a> </div>
        </td>
    </tr>
</table>

      
<?php 
    }
 }