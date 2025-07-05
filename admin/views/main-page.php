<?php
/**
 * 管理後台主頁面
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 處理批量操作
if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && isset($_POST['maintenance_ids'])) {
    $maintenance_ids = array_map('intval', $_POST['maintenance_ids']);
    foreach ($maintenance_ids as $id) {
        MSS_Database::delete_maintenance_order($id);
    }
    echo '<div class="notice notice-success is-dismissible"><p>選定的維修單已刪除。</p></div>';
}

// 獲取維修單列表
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search_term = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

if ($search_term) {
    $maintenance_orders = MSS_Database::search_maintenance_orders($search_term, $per_page, $offset);
} else {
    $maintenance_orders = MSS_Database::get_all_maintenance_orders($per_page, $offset);
}

$total_orders = MSS_Database::get_maintenance_orders_count();
$total_pages = ceil($total_orders / $per_page);

// 獲取統計數據
$stats = MSS_Database::get_maintenance_stats();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">維修單管理系統</h1>
    <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system-add'); ?>" class="page-title-action">新增維修單</a>
    
    <hr class="wp-header-end">
    
    <!-- 統計卡片 -->
    <div class="mss-stats-cards">
        <div class="mss-stat-card">
            <div class="mss-stat-number"><?php echo $stats['total']; ?></div>
            <div class="mss-stat-label">總計維修單</div>
        </div>
        <div class="mss-stat-card">
            <div class="mss-stat-number"><?php echo $stats['pending']; ?></div>
            <div class="mss-stat-label">待客戶簽名</div>
        </div>
        <div class="mss-stat-card">
            <div class="mss-stat-number"><?php echo $stats['completed']; ?></div>
            <div class="mss-stat-label">已完成</div>
        </div>
    </div>
    
    <!-- 搜索表單 -->
    <div class="mss-search-form">
        <form method="get">
            <input type="hidden" name="page" value="maintenance-signature-system">
            <p class="search-box">
                <label class="screen-reader-text" for="maintenance-search-input">搜索維修單:</label>
                <input type="search" id="maintenance-search-input" name="s" value="<?php echo esc_attr($search_term); ?>" placeholder="搜索維修單號碼、案場、工務姓名...">
                <input type="submit" id="search-submit" class="button" value="搜索">
            </p>
        </form>
    </div>
    
    <?php if (empty($maintenance_orders)): ?>
        <div class="mss-no-orders">
            <div class="mss-no-orders-icon">📋</div>
            <h2>尚未建立任何維修單</h2>
            <p>開始建立您的第一個維修單吧！</p>
            <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system-add'); ?>" class="button button-primary">新增維修單</a>
        </div>
    <?php else: ?>
        <!-- 維修單列表 -->
        <form method="post" id="maintenance-orders-form">
            <div class="tablenav top">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-top" class="screen-reader-text">選擇批量操作</label>
                    <select name="action" id="bulk-action-selector-top">
                        <option value="-1">批量操作</option>
                        <option value="bulk_delete">刪除</option>
                    </select>
                    <input type="submit" id="doaction" class="button action" value="套用">
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo sprintf('%d 個項目', $total_orders); ?></span>
                    <span class="pagination-links">
                        <?php if ($page > 1): ?>
                            <a class="first-page button" href="<?php echo add_query_arg('paged', 1); ?>">
                                <span class="screen-reader-text">第一頁</span>
                                <span aria-hidden="true">«</span>
                            </a>
                            <a class="prev-page button" href="<?php echo add_query_arg('paged', $page - 1); ?>">
                                <span class="screen-reader-text">上一頁</span>
                                <span aria-hidden="true">‹</span>
                            </a>
                        <?php endif; ?>
                        
                        <span class="screen-reader-text">目前頁面</span>
                        <span id="table-paging" class="paging-input">
                            <span class="tablenav-paging-text">
                                <?php echo sprintf('%d / %d', $page, $total_pages); ?>
                            </span>
                        </span>
                        
                        <?php if ($page < $total_pages): ?>
                            <a class="next-page button" href="<?php echo add_query_arg('paged', $page + 1); ?>">
                                <span class="screen-reader-text">下一頁</span>
                                <span aria-hidden="true">›</span>
                            </a>
                            <a class="last-page button" href="<?php echo add_query_arg('paged', $total_pages); ?>">
                                <span class="screen-reader-text">最後一頁</span>
                                <span aria-hidden="true">»</span>
                            </a>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <td id="cb" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1">全選</label>
                            <input id="cb-select-all-1" type="checkbox">
                        </td>
                        <th scope="col" class="manage-column column-form-number">維修單號碼</th>
                        <th scope="col" class="manage-column column-location">維修案場</th>
                        <th scope="col" class="manage-column column-constructor">工務姓名</th>
                        <th scope="col" class="manage-column column-address">地址</th>
                        <th scope="col" class="manage-column column-status">狀態</th>
                        <th scope="col" class="manage-column column-date">建立時間</th>
                        <th scope="col" class="manage-column column-actions">操作</th>
                    </tr>
                </thead>
                <tbody id="the-list">
                    <?php foreach ($maintenance_orders as $order): ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <label class="screen-reader-text" for="cb-select-<?php echo $order->id; ?>">
                                選擇 <?php echo esc_html($order->form_number); ?>
                            </label>
                            <input id="cb-select-<?php echo $order->id; ?>" type="checkbox" name="maintenance_ids[]" value="<?php echo $order->id; ?>">
                        </th>
                        <td class="column-form-number">
                            <strong><?php echo esc_html($order->form_number); ?></strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system-add&action=edit&id=' . $order->id); ?>">編輯</a> |
                                </span>
                                <span class="view">
                                    <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system&action=view&id=' . $order->id); ?>">查看</a> |
                                </span>
                                <span class="delete">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=maintenance-signature-system&action=delete&id=' . $order->id), 'delete_maintenance_order'); ?>" class="submitdelete">刪除</a>
                                </span>
                            </div>
                        </td>
                        <td class="column-location"><?php echo esc_html($order->location); ?></td>
                        <td class="column-constructor"><?php echo esc_html($order->constructor_name); ?></td>
                        <td class="column-address">
                            <?php 
                            $address_parts = array_filter(array($order->building, $order->floor, $order->unit));
                            echo esc_html(implode(' ', $address_parts));
                            ?>
                        </td>
                        <td class="column-status">
                            <?php if ($order->status === 'pending_customer_signature'): ?>
                                <span class="mss-status-pending">待客戶簽名</span>
                            <?php elseif ($order->status === 'completed'): ?>
                                <span class="mss-status-completed">已完成</span>
                            <?php else: ?>
                                <span class="mss-status-unknown">未知狀態</span>
                            <?php endif; ?>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at))); ?>
                        </td>
                        <td class="column-actions">
                            <div class="mss-action-buttons">
                                <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system&action=copy_link&id=' . $order->id); ?>" class="button button-small mss-copy-link-btn" data-maintenance-id="<?php echo $order->id; ?>">複製客戶連結</a>
                                <?php if ($order->status === 'completed'): ?>
                                    <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system&action=view_report&id=' . $order->id); ?>" class="button button-small">查看報告</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="tablenav bottom">
                <div class="alignleft actions bulkactions">
                    <label for="bulk-action-selector-bottom" class="screen-reader-text">選擇批量操作</label>
                    <select name="action2" id="bulk-action-selector-bottom">
                        <option value="-1">批量操作</option>
                        <option value="bulk_delete">刪除</option>
                    </select>
                    <input type="submit" id="doaction2" class="button action" value="套用">
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo sprintf('%d 個項目', $total_orders); ?></span>
                    <!-- 分頁導航重複 -->
                </div>
                <?php endif; ?>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // 複製客戶連結
    $('.mss-copy-link-btn').on('click', function(e) {
        e.preventDefault();
        
        var maintenanceId = $(this).data('maintenance-id');
        var link = window.location.protocol + '//' + window.location.host + '/wp-admin/admin.php?page=maintenance-signature-system&action=customer_signature&id=' + maintenanceId;
        
        // 複製到剪貼板
        navigator.clipboard.writeText(link).then(function() {
            alert('客戶簽名連結已複製到剪貼板！');
        }).catch(function(err) {
            console.error('複製失敗:', err);
            // 備用方法
            var textArea = document.createElement('textarea');
            textArea.value = link;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('客戶簽名連結已複製到剪貼板！');
        });
    });
    
    // 批量操作確認
    $('#maintenance-orders-form').on('submit', function(e) {
        var action = $('#bulk-action-selector-top').val();
        if (action === 'bulk_delete') {
            var checkedBoxes = $('input[name="maintenance_ids[]"]:checked');
            if (checkedBoxes.length === 0) {
                alert('請選擇要刪除的維修單。');
                e.preventDefault();
                return false;
            }
            
            if (!confirm('確定要刪除選定的維修單嗎？此操作無法復原。')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // 全選/取消全選
    $('#cb-select-all-1').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('input[name="maintenance_ids[]"]').prop('checked', isChecked);
    });
});
</script>