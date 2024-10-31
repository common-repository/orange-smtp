<?php
/**
* 
*  STMP Email Log list
*
**/
namespace Orangewp\Smtp\Includes;

if (!defined('ABSPATH')) {
    exit;
}

class EmaillogList
{   
    //private $success_message = '';

    function __construct()
    {
       $this->recorddata();
    }

    public function recorddata(){
        global $wpdb;
        // Define table name
        $table_name = $wpdb->prefix . 'orgsmtp_logs';
        $delete_message = '';
    
        // Check if delete action
        if (isset($_GET['action']) && sanitize_text_field($_GET['action']) === 'delete' && isset($_GET['id'])) {
            // Verify nonce
            $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field($_GET['_wpnonce']) : '';
            if (wp_verify_nonce($nonce, 'smtp_delete_log_' . sanitize_text_field(wp_unslash($_GET['id'])))) {
                $delete_id = absint($_GET['id']);
                $cached_data = wp_cache_get('smtp_delete_log_' . $delete_id);
                if (false === $cached_data) {
                    $wpdb->delete($table_name, array('id' => $delete_id), array('%d'));
                    // Cache the result
                    wp_cache_set('smtp_delete_log_' . $delete_id, 'deleted', '', 1800); 
                }
                $delete_message = esc_html__('Record deleted successfully!', 'orange-smtp'); 
            }
        }
    
        $query = "SELECT * FROM $table_name";
        $total_query = $wpdb->prepare("SELECT COUNT(1) FROM (%s) AS orgsmtp_logs", $query);
        $results = $wpdb->get_results($query, OBJECT); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    
        // Output the delete message
        if (!empty($delete_message)) {
            echo '<div class="delete-message">' . esc_html($delete_message) . '</div>';
        }
        echo '<table id="organgetable" class="display" style="width:100%">
        <thead>
            <tr> 
                <th>Status</th> 
                <th>To</th>
                <th>Subject</th>
                <th>Sender</th>
                <th>Action</th>
            </tr>
        </thead>';
        echo '<tbody>';
        foreach ($results as $result) {
            echo "<tr>
                    <td><div class='status'>" . esc_html($result->estatus) . "</div></td>
                    <td>" . esc_html($result->recipient_email) . "</td>
                    <td>" . esc_html($result->esubject) . "</td>
                    <td>" . esc_html($result->sender_email) . "</td>
                    <td><a href='" . esc_url(add_query_arg(array('action' => 'delete', 'id' => $result->id, '_wpnonce' => wp_create_nonce('smtp_delete_log_' . $result->id)))) . "' onclick='return confirmation();'> <img src='" . plugins_url("../assets/images/Trash.png", __FILE__) . "'>  </a></td>
                </tr>";
        }
        echo '</tbody>
        </table>';    
     }
   }
    ?>
    