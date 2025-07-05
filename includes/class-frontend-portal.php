<?php
/**
 * 前端工務人員門戶類別
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

class MSS_Frontend_Portal {
    
    /**
     * 初始化
     */
    public static function init() {
        add_action('wp', array(__CLASS__, 'handle_frontend_requests'));
        add_filter('template_include', array(__CLASS__, 'template_include'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        
        // 處理 AJAX 請求
        add_action('wp_ajax_mss_create_maintenance_order', array(__CLASS__, 'ajax_create_maintenance_order'));
        add_action('wp_ajax_nopriv_mss_create_maintenance_order', array(__CLASS__, 'ajax_create_maintenance_order'));
        add_action('wp_ajax_mss_get_maintenance_orders', array(__CLASS__, 'ajax_get_maintenance_orders'));
        add_action('wp_ajax_nopriv_mss_get_maintenance_orders', array(__CLASS__, 'ajax_get_maintenance_orders'));
        add_action('wp_ajax_mss_manage_constructors', array(__CLASS__, 'ajax_manage_constructors'));
        add_action('wp_ajax_nopriv_mss_manage_constructors', array(__CLASS__, 'ajax_manage_constructors'));
    }
    
    /**
     * 處理前端請求
     */
    public static function handle_frontend_requests() {
        // 工務人員管理面板
        if (isset($_GET['mss_dashboard'])) {
            global $mss_show_dashboard;
            $mss_show_dashboard = true;
            // 防止 WordPress 載入其他內容
            remove_all_actions('wp_head');
            add_action('wp_head', 'wp_enqueue_scripts', 1);
            add_action('wp_head', 'wp_print_styles', 8);
            add_action('wp_head', 'wp_print_head_scripts', 9);
        }
        
        // 新增維修單頁面
        if (isset($_GET['mss_create'])) {
            global $mss_show_create_form;
            $mss_show_create_form = true;
            // 防止 WordPress 載入其他內容
            remove_all_actions('wp_head');
            add_action('wp_head', 'wp_enqueue_scripts', 1);
            add_action('wp_head', 'wp_print_styles', 8);
            add_action('wp_head', 'wp_print_head_scripts', 9);
        }
        
        // 系統設定頁面
        if (isset($_GET['mss_settings'])) {
            global $mss_show_settings;
            $mss_show_settings = true;
            // 防止 WordPress 載入其他內容
            remove_all_actions('wp_head');
            add_action('wp_head', 'wp_enqueue_scripts', 1);
            add_action('wp_head', 'wp_print_styles', 8);
            add_action('wp_head', 'wp_print_head_scripts', 9);
        }
    }
    
    /**
     * 模板包含過濾器
     */
    public static function template_include($template) {
        // 直接檢查 URL 參數，不依賴全域變數
        if (isset($_GET['mss_dashboard'])) {
            $custom_template = MSS_PLUGIN_PATH . 'public/templates/frontend-dashboard.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        if (isset($_GET['mss_create'])) {
            $custom_template = MSS_PLUGIN_PATH . 'public/templates/frontend-create.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        if (isset($_GET['mss_settings'])) {
            $custom_template = MSS_PLUGIN_PATH . 'public/templates/frontend-settings.php';
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
        // 直接檢查 URL 參數
        if (isset($_GET['mss_dashboard']) || isset($_GET['mss_create']) || isset($_GET['mss_settings'])) {
            wp_enqueue_script('jquery');
            
            wp_enqueue_script(
                'mss-frontend-app',
                MSS_PLUGIN_URL . 'public/js/frontend-app.js',
                array('jquery'),
                MSS_PLUGIN_VERSION,
                true
            );
            
            wp_enqueue_style(
                'mss-frontend-styles',
                MSS_PLUGIN_URL . 'public/css/frontend-styles.css',
                array(),
                MSS_PLUGIN_VERSION
            );
            
            // 本地化腳本
            wp_localize_script('mss-frontend-app', 'mss_frontend', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('mss_frontend_nonce'),
                'home_url' => home_url(),
                'messages' => array(
                    'success' => '操作成功！',
                    'error' => '操作失敗，請稍後再試',
                    'confirm_delete' => '確定要刪除嗎？此操作無法復原。',
                    'required_fields' => '請填寫所有必填欄位',
                    'creating' => '建立中...',
                    'loading' => '載入中...'
                )
            ));
        }
    }
    
    /**
     * AJAX: 建立維修單
     */
    public static function ajax_create_maintenance_order() {
        // 驗證 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mss_frontend_nonce')) {
            wp_send_json_error(array('message' => '安全檢查失敗'));
        }
        
        // 收集表單數據
        $form_data = array(
            'form_number' => sanitize_text_field($_POST['form_number']),
            'date' => sanitize_text_field($_POST['date']),
            'location' => sanitize_text_field($_POST['location']),
            'constructor_name' => sanitize_text_field($_POST['constructor_name']),
            'building' => sanitize_text_field($_POST['building']),
            'floor' => sanitize_text_field($_POST['floor']),
            'unit' => sanitize_text_field($_POST['unit']),
            'problem_description' => sanitize_textarea_field($_POST['problem_description'])
        );
        
        // 驗證必填欄位
        $required_fields = array('form_number', 'date', 'location', 'constructor_name', 'problem_description');
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                wp_send_json_error(array('message' => "「{$field}」為必填欄位"));
            }
        }
        
        // 建立維修單
        $maintenance_id = MSS_Database::create_maintenance_order($form_data);
        
        if ($maintenance_id) {
            // 處理圖片上傳
            if (!empty($_FILES['maintenance_images'])) {
                self::handle_image_uploads($maintenance_id, $_FILES['maintenance_images']);
            }
            
            // 生成客戶簽名連結
            $customer_link = MSS_Customer_Portal::generate_signature_link($maintenance_id);
            
            wp_send_json_success(array(
                'message' => '維修單建立成功！',
                'maintenance_id' => $maintenance_id,
                'form_number' => $form_data['form_number'],
                'customer_link' => $customer_link
            ));
        } else {
            wp_send_json_error(array('message' => '維修單建立失敗'));
        }
    }
    
    /**
     * AJAX: 獲取維修單列表
     */
    public static function ajax_get_maintenance_orders() {
        // 驗證 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mss_frontend_nonce')) {
            wp_send_json_error(array('message' => '安全檢查失敗'));
        }
        
        $page = intval($_POST['page']) ?: 1;
        $search = sanitize_text_field($_POST['search']) ?: '';
        $per_page = 10;
        $offset = ($page - 1) * $per_page;
        
        if ($search) {
            $orders = MSS_Database::search_maintenance_orders($search, $per_page, $offset);
        } else {
            $orders = MSS_Database::get_all_maintenance_orders($per_page, $offset);
        }
        
        $total_orders = MSS_Database::get_maintenance_orders_count();
        $total_pages = ceil($total_orders / $per_page);
        
        // 為每個訂單添加客戶連結
        foreach ($orders as $order) {
            $order->customer_link = MSS_Customer_Portal::generate_signature_link($order->id);
            $order->formatted_date = date_i18n(get_option('date_format'), strtotime($order->created_at));
            $order->status_text = MSS_Customer_Portal::get_status_text($order->status);
        }
        
        wp_send_json_success(array(
            'orders' => $orders,
            'pagination' => array(
                'current_page' => $page,
                'total_pages' => $total_pages,
                'total_orders' => $total_orders
            )
        ));
    }
    
    /**
     * AJAX: 管理工務人員
     */
    public static function ajax_manage_constructors() {
        // 驗證 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mss_frontend_nonce')) {
            wp_send_json_error(array('message' => '安全檢查失敗'));
        }
        
        $action = sanitize_text_field($_POST['constructor_action']);
        
        switch ($action) {
            case 'get':
                $constructors = MSS_Database::get_constructors();
                wp_send_json_success(array('constructors' => $constructors));
                break;
                
            case 'add':
                $name = sanitize_text_field($_POST['constructor_name']);
                if (empty($name)) {
                    wp_send_json_error(array('message' => '請輸入工務姓名'));
                }
                
                $result = MSS_Database::add_constructor($name);
                if ($result) {
                    wp_send_json_success(array('message' => '工務姓名新增成功'));
                } else {
                    wp_send_json_error(array('message' => '新增失敗，可能已存在相同姓名'));
                }
                break;
                
            case 'delete':
                $id = intval($_POST['constructor_id']);
                if ($id <= 0) {
                    wp_send_json_error(array('message' => '無效的ID'));
                }
                
                $result = MSS_Database::delete_constructor($id);
                if ($result) {
                    wp_send_json_success(array('message' => '工務姓名已刪除'));
                } else {
                    wp_send_json_error(array('message' => '刪除失敗'));
                }
                break;
                
            default:
                wp_send_json_error(array('message' => '無效的操作'));
        }
    }
    
    /**
     * 處理圖片上傳
     */
    private static function handle_image_uploads($maintenance_id, $files) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $upload_overrides = array('test_form' => false);
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = array(
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                );
                
                $uploaded_file = wp_handle_upload($file, $upload_overrides);
                
                if (!isset($uploaded_file['error'])) {
                    MSS_Database::save_media_file(array(
                        'maintenance_id' => $maintenance_id,
                        'file_name' => basename($uploaded_file['file']),
                        'file_path' => $uploaded_file['url'],
                        'file_type' => $uploaded_file['type'],
                        'file_size' => $files['size'][$i]
                    ));
                }
            }
        }
    }
    
    /**
     * 生成前端管理連結
     */
    public static function get_dashboard_url() {
        return home_url('/?mss_dashboard=1');
    }
    
    public static function get_create_url() {
        return home_url('/?mss_create=1');
    }
    
    public static function get_settings_url() {
        return home_url('/?mss_settings=1');
    }
}