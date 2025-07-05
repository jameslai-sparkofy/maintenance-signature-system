<?php
/**
 * Plugin Name: 維修單線上簽名系統
 * Plugin URI: https://github.com/your-username/maintenance-signature-system
 * Description: 專業的維修單管理系統，支援工務人員建立維修單、上傳圖片、生成客戶簽名連結，並提供完整的報告功能。
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://your-website.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: maintenance-signature-system
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 定義插件常量
define('MSS_PLUGIN_FILE', __FILE__);
define('MSS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MSS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MSS_PLUGIN_VERSION', '1.0.0');
define('MSS_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * 主插件類別
 */
class MaintenanceSignatureSystem {
    
    /**
     * 插件實例
     */
    private static $instance = null;
    
    /**
     * 獲取插件實例
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 私有建構函式
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * 初始化插件鉤子
     */
    private function init_hooks() {
        // 插件啟動時的鉤子
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // 插件停用時的鉤子
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // 插件卸載時的鉤子
        register_uninstall_hook(__FILE__, array('MaintenanceSignatureSystem', 'uninstall'));
        
        // 初始化插件
        add_action('init', array($this, 'init'));
        
        // 管理後台初始化
        add_action('admin_init', array($this, 'admin_init'));
        
        // 管理後台選單
        add_action('admin_menu', array($this, 'admin_menu'));
        
        // 載入腳本和樣式
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // 處理前端請求
        add_action('wp_ajax_mss_handle_signature', array($this, 'handle_signature_submission'));
        add_action('wp_ajax_nopriv_mss_handle_signature', array($this, 'handle_signature_submission'));
        
        // 短代碼支援
        add_shortcode('maintenance_signature_form', array($this, 'signature_form_shortcode'));
        
        // 載入文本域
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * 插件啟動
     */
    public function activate() {
        // 創建數據庫表
        $this->create_database_tables();
        
        // 設定默認選項
        $this->set_default_options();
        
        // 刷新重寫規則
        flush_rewrite_rules();
        
        // 記錄啟動時間
        update_option('mss_activation_time', current_time('timestamp'));
    }
    
    /**
     * 插件停用
     */
    public function deactivate() {
        // 清除暫存
        wp_cache_flush();
        
        // 刷新重寫規則
        flush_rewrite_rules();
    }
    
    /**
     * 插件卸載
     */
    public static function uninstall() {
        // 刪除數據庫表
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'mss_maintenance_orders',
            $wpdb->prefix . 'mss_maintenance_signatures',
            $wpdb->prefix . 'mss_maintenance_media'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        // 刪除選項
        delete_option('mss_db_version');
        delete_option('mss_activation_time');
        delete_option('mss_settings');
        
        // 清除暫存
        wp_cache_flush();
    }
    
    /**
     * 初始化插件
     */
    public function init() {
        // 載入必要的類別
        $this->load_dependencies();
        
        // 檢查數據庫版本
        $this->check_database_version();
    }
    
    /**
     * 管理後台初始化
     */
    public function admin_init() {
        // 檢查用戶權限
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // 註冊設定
        $this->register_settings();
    }
    
    /**
     * 管理後台選單
     */
    public function admin_menu() {
        // 主選單頁面
        add_menu_page(
            __('維修單管理', 'maintenance-signature-system'),
            __('維修單系統', 'maintenance-signature-system'),
            'manage_options',
            'maintenance-signature-system',
            array($this, 'admin_page'),
            'dashicons-clipboard',
            30
        );
        
        // 子選單頁面
        add_submenu_page(
            'maintenance-signature-system',
            __('所有維修單', 'maintenance-signature-system'),
            __('所有維修單', 'maintenance-signature-system'),
            'manage_options',
            'maintenance-signature-system',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'maintenance-signature-system',
            __('新增維修單', 'maintenance-signature-system'),
            __('新增維修單', 'maintenance-signature-system'),
            'manage_options',
            'maintenance-signature-system-add',
            array($this, 'admin_add_page')
        );
        
        add_submenu_page(
            'maintenance-signature-system',
            __('系統設定', 'maintenance-signature-system'),
            __('系統設定', 'maintenance-signature-system'),
            'manage_options',
            'maintenance-signature-system-settings',
            array($this, 'admin_settings_page')
        );
    }
    
    /**
     * 載入前端腳本和樣式
     */
    public function enqueue_public_scripts() {
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
            'nonce' => wp_create_nonce('mss_nonce'),
            'messages' => array(
                'signature_required' => __('請先完成簽名', 'maintenance-signature-system'),
                'success' => __('簽名提交成功！', 'maintenance-signature-system'),
                'error' => __('提交失敗，請稍後再試', 'maintenance-signature-system')
            )
        ));
    }
    
    /**
     * 載入管理後台腳本和樣式
     */
    public function enqueue_admin_scripts($hook) {
        // 只在我們的頁面載入
        if (strpos($hook, 'maintenance-signature-system') === false) {
            return;
        }
        
        wp_enqueue_script(
            'mss-admin-scripts',
            MSS_PLUGIN_URL . 'admin/js/admin-scripts.js',
            array('jquery'),
            MSS_PLUGIN_VERSION,
            true
        );
        
        wp_enqueue_style(
            'mss-admin-styles',
            MSS_PLUGIN_URL . 'admin/css/admin-styles.css',
            array(),
            MSS_PLUGIN_VERSION
        );
        
        // 本地化腳本
        wp_localize_script('mss-admin-scripts', 'mss_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mss_admin_nonce')
        ));
    }
    
    /**
     * 載入文本域
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'maintenance-signature-system',
            false,
            dirname(MSS_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * 載入依賴項
     */
    private function load_dependencies() {
        require_once MSS_PLUGIN_PATH . 'includes/class-database.php';
        require_once MSS_PLUGIN_PATH . 'includes/class-admin-menu.php';
        require_once MSS_PLUGIN_PATH . 'includes/class-customer-portal.php';
        require_once MSS_PLUGIN_PATH . 'includes/class-maintenance-system.php';
    }
    
    /**
     * 創建數據庫表
     */
    private function create_database_tables() {
        require_once MSS_PLUGIN_PATH . 'includes/class-database.php';
        MSS_Database::create_tables();
    }
    
    /**
     * 設定默認選項
     */
    private function set_default_options() {
        $default_settings = array(
            'enable_email_notifications' => true,
            'signature_canvas_width' => 400,
            'signature_canvas_height' => 200,
            'max_upload_size' => 5, // MB
            'allowed_file_types' => array('jpg', 'jpeg', 'png', 'gif'),
            'default_constructor_names' => array('張工程師', '李技師', '王師傅')
        );
        
        add_option('mss_settings', $default_settings);
        add_option('mss_db_version', '1.0.0');
    }
    
    /**
     * 檢查數據庫版本
     */
    private function check_database_version() {
        $current_version = get_option('mss_db_version', '0.0.0');
        
        if (version_compare($current_version, MSS_PLUGIN_VERSION, '<')) {
            $this->create_database_tables();
            update_option('mss_db_version', MSS_PLUGIN_VERSION);
        }
    }
    
    /**
     * 註冊設定
     */
    private function register_settings() {
        register_setting('mss_settings', 'mss_settings');
    }
    
    /**
     * 管理後台主頁面
     */
    public function admin_page() {
        include MSS_PLUGIN_PATH . 'admin/views/main-page.php';
    }
    
    /**
     * 管理後台新增頁面
     */
    public function admin_add_page() {
        include MSS_PLUGIN_PATH . 'admin/views/add-page.php';
    }
    
    /**
     * 管理後台設定頁面
     */
    public function admin_settings_page() {
        include MSS_PLUGIN_PATH . 'admin/views/settings-page.php';
    }
    
    /**
     * 處理簽名提交
     */
    public function handle_signature_submission() {
        // 驗證 nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mss_nonce')) {
            wp_die(__('Security check failed', 'maintenance-signature-system'));
        }
        
        // 處理簽名數據
        $signature_data = sanitize_text_field($_POST['signature_data']);
        $maintenance_id = intval($_POST['maintenance_id']);
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $customer_phone = sanitize_text_field($_POST['customer_phone']);
        $customer_email = sanitize_email($_POST['customer_email']);
        $satisfaction_rating = intval($_POST['satisfaction_rating']);
        $feedback = sanitize_textarea_field($_POST['feedback']);
        
        // 保存簽名數據
        $result = MSS_Database::save_signature(array(
            'maintenance_id' => $maintenance_id,
            'customer_name' => $customer_name,
            'customer_phone' => $customer_phone,
            'customer_email' => $customer_email,
            'signature_data' => $signature_data,
            'satisfaction_rating' => $satisfaction_rating,
            'feedback' => $feedback,
            'signed_at' => current_time('mysql')
        ));
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('簽名提交成功！', 'maintenance-signature-system')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('簽名提交失敗，請稍後再試', 'maintenance-signature-system')
            ));
        }
    }
    
    /**
     * 短代碼：客戶簽名表單
     */
    public function signature_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'maintenance_id' => 0
        ), $atts);
        
        if (!$atts['maintenance_id']) {
            return __('請提供維修單ID', 'maintenance-signature-system');
        }
        
        ob_start();
        include MSS_PLUGIN_PATH . 'public/templates/signature-form.php';
        return ob_get_clean();
    }
}

// 初始化插件
MaintenanceSignatureSystem::get_instance();