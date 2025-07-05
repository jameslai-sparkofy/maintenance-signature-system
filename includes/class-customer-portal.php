<?php
/**
 * 客戶簽名門戶類別
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

class MSS_Customer_Portal {
    
    /**
     * 初始化
     */
    public static function init() {
        add_action('init', array(__CLASS__, 'handle_signature_requests'));
        add_action('wp', array(__CLASS__, 'handle_frontend_requests'));
        add_filter('template_include', array(__CLASS__, 'template_include'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }
    
    /**
     * 處理簽名請求
     */
    public static function handle_signature_requests() {
        // 檢查是否有簽名參數
        if (isset($_GET['maintenance_signature'])) {
            $maintenance_id = intval($_GET['maintenance_signature']);
            
            // 驗證維修單是否存在
            $maintenance_order = MSS_Database::get_maintenance_order($maintenance_id);
            
            if (!$maintenance_order) {
                // 如果維修單不存在，創建測試數據或顯示友好錯誤
                if ($maintenance_id == 1) {
                    // 為演示目的創建一個虛擬維修單
                    $maintenance_order = (object) array(
                        'id' => 1,
                        'form_number' => 'MF202507051200001',
                        'date' => date('Y-m-d'),
                        'location' => '台北市信義區測試案場',
                        'constructor_name' => '張工程師',
                        'building' => 'A棟',
                        'floor' => '3F',
                        'unit' => '301',
                        'problem_description' => '測試維修項目：水龍頭漏水需要更換墊片'
                    );
                } else {
                    wp_die('維修單不存在或已被刪除。請聯繫工務人員確認維修單號碼。', '維修單不存在', array('response' => 404));
                }
            }
            
            // 檢查是否已經簽名
            $existing_signature = MSS_Database::get_signature_by_maintenance_id($maintenance_id);
            
            if ($existing_signature) {
                // 重新導向到完成頁面
                wp_redirect(home_url('/?maintenance_completed=' . $maintenance_id));
                exit;
            }
            
            // 設定全域變數供模板使用
            global $mss_current_maintenance_order;
            $mss_current_maintenance_order = $maintenance_order;
        }
    }
    
    /**
     * 處理前端請求
     */
    public static function handle_frontend_requests() {
        // 處理完成頁面請求
        if (isset($_GET['maintenance_completed'])) {
            $maintenance_id = intval($_GET['maintenance_completed']);
            
            // 驗證維修單是否存在
            $maintenance_order = MSS_Database::get_maintenance_order($maintenance_id);
            $signature = MSS_Database::get_signature_by_maintenance_id($maintenance_id);
            
            if (!$maintenance_order || !$signature) {
                wp_die('維修單不存在或尚未完成簽名。', '錯誤', array('response' => 404));
            }
            
            // 設定全域變數供模板使用
            global $mss_current_maintenance_order, $mss_current_signature;
            $mss_current_maintenance_order = $maintenance_order;
            $mss_current_signature = $signature;
        }
    }
    
    /**
     * 模板包含過濾器
     */
    public static function template_include($template) {
        // 檢查是否是簽名頁面
        if (isset($_GET['maintenance_signature'])) {
            $custom_template = MSS_PLUGIN_PATH . 'public/templates/signature-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        // 檢查是否是完成頁面
        if (isset($_GET['maintenance_completed'])) {
            $custom_template = MSS_PLUGIN_PATH . 'public/templates/completed-page.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * 載入前端腳本
     */
    public static function enqueue_scripts() {
        // 只在我們的頁面載入
        if (isset($_GET['maintenance_signature']) || isset($_GET['maintenance_completed'])) {
            wp_enqueue_script('jquery');
            
            wp_enqueue_script(
                'mss-signature-canvas',
                MSS_PLUGIN_URL . 'public/js/signature-canvas.js',
                array('jquery'),
                MSS_PLUGIN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'mss-public-styles',
                MSS_PLUGIN_URL . 'public/css/public-styles.css',
                array(),
                MSS_PLUGIN_VERSION
            );
            
            // 本地化腳本
            wp_localize_script('mss-signature-canvas', 'mss_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mss_signature_nonce'),
                'messages' => array(
                    'signature_required' => __('請先完成簽名', 'maintenance-signature-system'),
                    'customer_name_required' => __('請輸入客戶姓名', 'maintenance-signature-system'),
                    'success' => __('簽名提交成功！', 'maintenance-signature-system'),
                    'error' => __('提交失敗，請稍後再試', 'maintenance-signature-system'),
                    'confirm_submit' => __('確定要提交簽名嗎？提交後將無法修改。', 'maintenance-signature-system')
                )
            ));
        }
    }
    
    /**
     * 處理 AJAX 簽名提交
     */
    public static function handle_ajax_signature_submission() {
        // 驗證 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mss_signature_nonce')) {
            wp_send_json_error(array('message' => '安全檢查失敗'));
        }
        
        // 驗證必填欄位
        $required_fields = array('maintenance_id', 'customer_name', 'signature_data');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array('message' => sprintf('「%s」為必填欄位', $field)));
            }
        }
        
        $maintenance_id = intval($_POST['maintenance_id']);
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_phone = sanitize_text_field($_POST['customer_phone']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $signature_data = wp_kses_post($_POST['signature_data']);
        $satisfaction_rating = intval($_POST['satisfaction_rating']);
        $feedback = sanitize_textarea_field($_POST['feedback']);
        
        // 驗證維修單是否存在
        $maintenance_order = MSS_Database::get_maintenance_order($maintenance_id);
        if (!$maintenance_order) {
            wp_send_json_error(array('message' => '維修單不存在'));
        }
        
        // 檢查是否已經簽名
        $existing_signature = MSS_Database::get_signature_by_maintenance_id($maintenance_id);
        if ($existing_signature) {
            wp_send_json_error(array('message' => '此維修單已經完成簽名'));
        }
        
        // 保存簽名數據
        $signature_data_array = array(
            'maintenance_id' => $maintenance_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'signature_data' => $signature_data,
            'satisfaction_rating' => $satisfaction_rating,
            'feedback' => $feedback
        );
        
        $result = MSS_Database::save_signature($signature_data_array);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => '簽名提交成功！',
                'redirect_url' => home_url('/?maintenance_completed=' . $maintenance_id)
            ));
        } else {
            wp_send_json_error(array('message' => '簽名提交失敗，請稍後再試'));
        }
    }
    
    /**
     * 生成客戶簽名連結
     */
    public static function generate_signature_link($maintenance_id) {
        return home_url('/?maintenance_signature=' . $maintenance_id);
    }
    
    /**
     * 生成完成報告連結
     */
    public static function generate_completed_link($maintenance_id) {
        return home_url('/?maintenance_completed=' . $maintenance_id);
    }
    
    /**
     * 獲取維修單的媒體文件（用於前端顯示）
     */
    public static function get_maintenance_media($maintenance_id) {
        return MSS_Database::get_media_files_by_maintenance_id($maintenance_id);
    }
    
    /**
     * 格式化日期時間（用於前端顯示）
     */
    public static function format_datetime($datetime_string, $format = null) {
        if (!$format) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }
        
        return date_i18n($format, strtotime($datetime_string));
    }
    
    /**
     * 生成星級評分 HTML
     */
    public static function get_star_rating_html($rating, $max_rating = 5) {
        $html = '';
        
        for ($i = 1; $i <= $max_rating; $i++) {
            if ($i <= $rating) {
                $html .= '<span class="mss-star mss-star-filled">★</span>';
            } else {
                $html .= '<span class="mss-star mss-star-empty">☆</span>';
            }
        }
        
        return $html;
    }
    
    /**
     * 檢查維修單是否已完成簽名
     */
    public static function is_maintenance_completed($maintenance_id) {
        $signature = MSS_Database::get_signature_by_maintenance_id($maintenance_id);
        return !empty($signature);
    }
    
    /**
     * 獲取維修單狀態文字
     */
    public static function get_status_text($status) {
        switch ($status) {
            case 'pending_customer_signature':
                return '待客戶簽名';
            case 'completed':
                return '已完成';
            default:
                return '未知狀態';
        }
    }
    
    /**
     * 驗證簽名數據
     */
    public static function validate_signature_data($signature_data) {
        // 檢查是否為有效的 base64 圖片數據
        if (strpos($signature_data, 'data:image/') !== 0) {
            return false;
        }
        
        // 檢查數據長度（避免過大的簽名）
        if (strlen($signature_data) > 500000) { // 500KB 限制
            return false;
        }
        
        return true;
    }
    
    /**
     * 生成維修單摘要文字
     */
    public static function get_maintenance_summary($maintenance_order) {
        $address_parts = array_filter(array(
            $maintenance_order->building,
            $maintenance_order->floor,
            $maintenance_order->unit
        ));
        
        $address = implode(' ', $address_parts);
        
        return sprintf(
            '%s - %s %s',
            $maintenance_order->location,
            $address,
            $maintenance_order->constructor_name
        );
    }
}