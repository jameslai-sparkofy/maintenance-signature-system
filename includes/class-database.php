<?php
/**
 * 數據庫管理類別
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

class MSS_Database {
    
    /**
     * 創建數據庫表
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // 維修單表
        $maintenance_orders_table = $wpdb->prefix . 'mss_maintenance_orders';
        $maintenance_orders_sql = "CREATE TABLE $maintenance_orders_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            form_number varchar(50) NOT NULL,
            date date NOT NULL,
            location varchar(255) NOT NULL,
            constructor_name varchar(100) NOT NULL,
            building varchar(100) DEFAULT '',
            floor varchar(50) DEFAULT '',
            unit varchar(50) DEFAULT '',
            problem_description text NOT NULL,
            status varchar(50) DEFAULT 'pending_customer_signature',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY form_number (form_number),
            KEY constructor_name (constructor_name),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // 客戶簽名表
        $signatures_table = $wpdb->prefix . 'mss_maintenance_signatures';
        $signatures_sql = "CREATE TABLE $signatures_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            maintenance_id mediumint(9) NOT NULL,
            customer_name varchar(100) NOT NULL,
            customer_phone varchar(20) DEFAULT '',
            customer_email varchar(100) DEFAULT '',
            signature_data longtext NOT NULL,
            satisfaction_rating tinyint(1) DEFAULT 0,
            feedback text DEFAULT '',
            signed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY maintenance_id (maintenance_id),
            KEY customer_name (customer_name),
            KEY signed_at (signed_at),
            FOREIGN KEY (maintenance_id) REFERENCES $maintenance_orders_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // 媒體文件表
        $media_table = $wpdb->prefix . 'mss_maintenance_media';
        $media_sql = "CREATE TABLE $media_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            maintenance_id mediumint(9) NOT NULL,
            file_name varchar(255) NOT NULL,
            file_path varchar(500) NOT NULL,
            file_type varchar(50) NOT NULL,
            file_size int(11) NOT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY maintenance_id (maintenance_id),
            KEY file_type (file_type),
            KEY uploaded_at (uploaded_at),
            FOREIGN KEY (maintenance_id) REFERENCES $maintenance_orders_table(id) ON DELETE CASCADE
        ) $charset_collate;";
        
        // 工務姓名管理表
        $constructors_table = $wpdb->prefix . 'mss_constructors';
        $constructors_sql = "CREATE TABLE $constructors_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY name (name),
            KEY is_active (is_active)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($maintenance_orders_sql);
        dbDelta($signatures_sql);
        dbDelta($media_sql);
        dbDelta($constructors_sql);
        
        // 插入默認工務姓名
        self::insert_default_constructors();
    }
    
    /**
     * 插入默認工務姓名
     */
    private static function insert_default_constructors() {
        global $wpdb;
        
        $constructors_table = $wpdb->prefix . 'mss_constructors';
        
        $default_constructors = array(
            '張工程師',
            '李技師',
            '王師傅',
            '陳師傅',
            '林技師'
        );
        
        foreach ($default_constructors as $name) {
            $wpdb->insert(
                $constructors_table,
                array(
                    'name' => $name,
                    'is_active' => 1
                ),
                array('%s', '%d')
            );
        }
    }
    
    /**
     * 創建維修單
     */
    public static function create_maintenance_order($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'form_number' => sanitize_text_field($data['form_number']),
                'date' => sanitize_text_field($data['date']),
                'location' => sanitize_text_field($data['location']),
                'constructor_name' => sanitize_text_field($data['constructor_name']),
                'building' => sanitize_text_field($data['building']),
                'floor' => sanitize_text_field($data['floor']),
                'unit' => sanitize_text_field($data['unit']),
                'problem_description' => sanitize_textarea_field($data['problem_description']),
                'status' => 'pending_customer_signature'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * 獲取維修單
     */
    public static function get_maintenance_order($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $id
        );
        
        return $wpdb->get_row($query);
    }
    
    /**
     * 獲取所有維修單
     */
    public static function get_all_maintenance_orders($limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $limit,
            $offset
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * 更新維修單
     */
    public static function update_maintenance_order($id, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $update_data = array();
        $update_format = array();
        
        $allowed_fields = array(
            'form_number', 'date', 'location', 'constructor_name',
            'building', 'floor', 'unit', 'problem_description', 'status'
        );
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                if ($field === 'problem_description') {
                    $update_data[$field] = sanitize_textarea_field($data[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($data[$field]);
                }
                $update_format[] = '%s';
            }
        }
        
        if (empty($update_data)) {
            return false;
        }
        
        $result = $wpdb->update(
            $table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * 刪除維修單
     */
    public static function delete_maintenance_order($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * 保存客戶簽名
     */
    public static function save_signature($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_signatures';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'maintenance_id' => intval($data['maintenance_id']),
                'customer_name' => sanitize_text_field($data['customer_name']),
                'customer_phone' => sanitize_text_field($data['customer_phone']),
                'customer_email' => sanitize_email($data['customer_email']),
                'signature_data' => wp_kses_post($data['signature_data']),
                'satisfaction_rating' => intval($data['satisfaction_rating']),
                'feedback' => sanitize_textarea_field($data['feedback']),
                'signed_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result) {
            // 更新維修單狀態為已完成
            self::update_maintenance_order($data['maintenance_id'], array('status' => 'completed'));
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * 獲取簽名資料
     */
    public static function get_signature_by_maintenance_id($maintenance_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_signatures';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE maintenance_id = %d",
            $maintenance_id
        );
        
        return $wpdb->get_row($query);
    }
    
    /**
     * 保存媒體文件
     */
    public static function save_media_file($data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_media';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'maintenance_id' => intval($data['maintenance_id']),
                'file_name' => sanitize_file_name($data['file_name']),
                'file_path' => sanitize_text_field($data['file_path']),
                'file_type' => sanitize_text_field($data['file_type']),
                'file_size' => intval($data['file_size']),
                'uploaded_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%d', '%s')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * 獲取維修單的媒體文件
     */
    public static function get_media_files_by_maintenance_id($maintenance_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_media';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE maintenance_id = %d ORDER BY uploaded_at ASC",
            $maintenance_id
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * 刪除媒體文件
     */
    public static function delete_media_file($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_media';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * 獲取所有工務姓名
     */
    public static function get_constructors() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_constructors';
        
        $query = "SELECT * FROM $table_name WHERE is_active = 1 ORDER BY name ASC";
        
        return $wpdb->get_results($query);
    }
    
    /**
     * 添加工務姓名
     */
    public static function add_constructor($name) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_constructors';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($name),
                'is_active' => 1
            ),
            array('%s', '%d')
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * 刪除工務姓名
     */
    public static function delete_constructor($id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_constructors';
        
        $result = $wpdb->update(
            $table_name,
            array('is_active' => 0),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * 獲取維修單統計數據
     */
    public static function get_maintenance_stats() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $pending_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'pending_customer_signature'");
        $completed_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'completed'");
        
        return array(
            'total' => intval($total_orders),
            'pending' => intval($pending_orders),
            'completed' => intval($completed_orders)
        );
    }
    
    /**
     * 搜索維修單
     */
    public static function search_maintenance_orders($search_term, $limit = 20, $offset = 0) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        $search_term = '%' . $wpdb->esc_like($search_term) . '%';
        
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
             WHERE form_number LIKE %s 
             OR location LIKE %s 
             OR constructor_name LIKE %s 
             OR building LIKE %s 
             OR unit LIKE %s 
             OR problem_description LIKE %s
             ORDER BY created_at DESC 
             LIMIT %d OFFSET %d",
            $search_term, $search_term, $search_term, $search_term, $search_term, $search_term, $limit, $offset
        );
        
        return $wpdb->get_results($query);
    }
    
    /**
     * 獲取維修單總數
     */
    public static function get_maintenance_orders_count() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'mss_maintenance_orders';
        
        return intval($wpdb->get_var("SELECT COUNT(*) FROM $table_name"));
    }
}