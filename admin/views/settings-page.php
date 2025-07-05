<?php
/**
 * 系統設定頁面
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 處理工務姓名管理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_constructor']) && wp_verify_nonce($_POST['mss_settings_nonce'], 'mss_settings')) {
        $constructor_name = sanitize_text_field($_POST['constructor_name']);
        if (!empty($constructor_name)) {
            $result = MSS_Database::add_constructor($constructor_name);
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>工務姓名新增成功！</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>工務姓名新增失敗，可能已存在相同姓名。</p></div>';
            }
        }
    }
    
    if (isset($_POST['delete_constructor']) && wp_verify_nonce($_POST['mss_settings_nonce'], 'mss_settings')) {
        $constructor_id = intval($_POST['constructor_id']);
        if ($constructor_id > 0) {
            $result = MSS_Database::delete_constructor($constructor_id);
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>工務姓名已刪除。</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>刪除失敗，請稍後再試。</p></div>';
            }
        }
    }
}

// 獲取當前工務姓名列表
$constructors = MSS_Database::get_constructors();

// 獲取系統統計
$stats = MSS_Database::get_maintenance_stats();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">系統設定</h1>
    <hr class="wp-header-end">
    
    <!-- 系統概覽 -->
    <div class="mss-settings-overview">
        <h2>系統概覽</h2>
        <div class="mss-stats-grid">
            <div class="mss-stat-item">
                <div class="mss-stat-number"><?php echo $stats['total']; ?></div>
                <div class="mss-stat-label">總維修單數</div>
            </div>
            <div class="mss-stat-item">
                <div class="mss-stat-number"><?php echo $stats['pending']; ?></div>
                <div class="mss-stat-label">待簽名</div>
            </div>
            <div class="mss-stat-item">
                <div class="mss-stat-number"><?php echo $stats['completed']; ?></div>
                <div class="mss-stat-label">已完成</div>
            </div>
            <div class="mss-stat-item">
                <div class="mss-stat-number"><?php echo count($constructors); ?></div>
                <div class="mss-stat-label">工務人員數</div>
            </div>
        </div>
    </div>
    
    <!-- 工務姓名管理 -->
    <div class="mss-settings-section">
        <h2>工務姓名管理</h2>
        <p class="description">管理系統中的工務人員姓名，這些姓名會出現在維修單建立表單的下拉選單中。</p>
        
        <!-- 新增工務姓名 -->
        <div class="mss-add-constructor">
            <form method="post" class="mss-inline-form">
                <?php wp_nonce_field('mss_settings', 'mss_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="constructor_name">新增工務姓名</label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="constructor_name" 
                                   name="constructor_name" 
                                   class="regular-text" 
                                   placeholder="請輸入工務姓名"
                                   required>
                            <input type="submit" 
                                   name="add_constructor" 
                                   class="button button-primary" 
                                   value="新增">
                            <p class="description">例如：張工程師、李技師、王師傅</p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        
        <!-- 現有工務姓名列表 -->
        <div class="mss-constructors-list">
            <h3>現有工務人員</h3>
            
            <?php if (empty($constructors)): ?>
                <div class="mss-no-constructors">
                    <p>尚未新增任何工務人員姓名。</p>
                    <p>請使用上方表單新增第一個工務人員。</p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column">工務姓名</th>
                            <th scope="col" class="manage-column">建立時間</th>
                            <th scope="col" class="manage-column">狀態</th>
                            <th scope="col" class="manage-column">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($constructors as $constructor): ?>
                        <tr>
                            <td class="column-name">
                                <strong><?php echo esc_html($constructor->name); ?></strong>
                            </td>
                            <td class="column-date">
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($constructor->created_at))); ?>
                            </td>
                            <td class="column-status">
                                <?php if ($constructor->is_active): ?>
                                    <span class="mss-status-active">啟用中</span>
                                <?php else: ?>
                                    <span class="mss-status-inactive">已停用</span>
                                <?php endif; ?>
                            </td>
                            <td class="column-actions">
                                <form method="post" style="display: inline;" 
                                      onsubmit="return confirm('確定要刪除「<?php echo esc_js($constructor->name); ?>」嗎？');">
                                    <?php wp_nonce_field('mss_settings', 'mss_settings_nonce'); ?>
                                    <input type="hidden" name="constructor_id" value="<?php echo $constructor->id; ?>">
                                    <input type="submit" 
                                           name="delete_constructor" 
                                           class="button button-link-delete" 
                                           value="刪除">
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- 系統資訊 -->
    <div class="mss-settings-section">
        <h2>系統資訊</h2>
        <table class="form-table">
            <tr>
                <th scope="row">插件版本</th>
                <td><?php echo MSS_PLUGIN_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row">WordPress 版本</th>
                <td><?php echo get_bloginfo('version'); ?></td>
            </tr>
            <tr>
                <th scope="row">PHP 版本</th>
                <td><?php echo PHP_VERSION; ?></td>
            </tr>
            <tr>
                <th scope="row">資料庫版本</th>
                <td><?php echo get_option('mss_db_version', '未知'); ?></td>
            </tr>
            <tr>
                <th scope="row">安裝時間</th>
                <td>
                    <?php 
                    $install_time = get_option('mss_activation_time');
                    if ($install_time) {
                        echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $install_time);
                    } else {
                        echo '未知';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- 使用說明 -->
    <div class="mss-settings-section">
        <h2>使用說明</h2>
        <div class="mss-help-content">
            <h3>建立維修單</h3>
            <ol>
                <li>進入「維修單系統 > 新增維修單」</li>
                <li>填寫基本資訊（維修單號碼會自動生成）</li>
                <li>選擇工務姓名（從上方管理的姓名列表）</li>
                <li>詳細描述維修問題</li>
                <li>上傳相關圖片（可選）</li>
                <li>點擊「建立維修單」完成</li>
            </ol>
            
            <h3>客戶簽名流程</h3>
            <ol>
                <li>在維修單列表中點擊「複製客戶連結」</li>
                <li>將連結傳送給客戶（簡訊、郵件等）</li>
                <li>客戶點擊連結查看維修單詳情</li>
                <li>客戶填寫資訊並完成電子簽名</li>
                <li>客戶提交後系統狀態自動更新為「已完成」</li>
                <li>可在後台查看完整的簽名報告</li>
            </ol>
            
            <h3>系統特色</h3>
            <ul>
                <li>📱 響應式設計，支援手機、平板、電腦</li>
                <li>🖼️ 支援多張圖片上傳和預覽</li>
                <li>✍️ HTML5 電子簽名功能</li>
                <li>⭐ 客戶滿意度評分系統</li>
                <li>📊 完整的統計和報告功能</li>
                <li>🔒 安全的資料處理和存儲</li>
            </ul>
        </div>
    </div>
    
    <!-- 支援資訊 -->
    <div class="mss-settings-section">
        <h2>技術支援</h2>
        <p>如需技術支援或有任何問題，請聯繫：</p>
        <ul>
            <li>📧 電子郵件：support@example.com</li>
            <li>📖 使用文檔：查看插件目錄中的 README.md</li>
            <li>🐛 問題回報：GitHub Issues</li>
        </ul>
    </div>
</div>

<style>
.mss-settings-overview {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.mss-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.mss-stat-item {
    text-align: center;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
}

.mss-stat-number {
    font-size: 2em;
    font-weight: bold;
    color: #0073aa;
}

.mss-stat-label {
    margin-top: 5px;
    color: #666;
    font-size: 0.9em;
}

.mss-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.mss-settings-section h2 {
    margin-top: 0;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.mss-inline-form {
    margin-bottom: 20px;
}

.mss-no-constructors {
    padding: 20px;
    text-align: center;
    color: #666;
    background: #f9f9f9;
    border-radius: 4px;
}

.mss-status-active {
    color: #46b450;
    font-weight: bold;
}

.mss-status-inactive {
    color: #999;
}

.mss-help-content h3 {
    color: #0073aa;
    margin-top: 25px;
}

.mss-help-content ol, .mss-help-content ul {
    margin-left: 20px;
}

.mss-help-content li {
    margin-bottom: 5px;
}
</style>