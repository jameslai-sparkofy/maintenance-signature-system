<?php
/**
 * 前端工務人員儀表板
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 設定頁面標題
$page_title = '工務管理系統 - 儀表板';

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo esc_html($page_title); ?></title>
    <?php wp_head(); ?>
</head>
<body class="mss-frontend-dashboard">
    <!-- 頂部導航 -->
    <div class="mss-navbar">
        <div class="mss-navbar-brand">
            <h1>🔧 工務管理系統</h1>
        </div>
        <div class="mss-navbar-menu">
            <button class="mss-menu-toggle" onclick="toggleMenu()">☰</button>
            <div class="mss-navbar-links" id="navbarLinks">
                <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>" class="active">儀表板</a>
                <a href="<?php echo MSS_Frontend_Portal::get_create_url(); ?>">新增維修單</a>
                <a href="<?php echo MSS_Frontend_Portal::get_settings_url(); ?>">系統設定</a>
            </div>
        </div>
    </div>

    <div class="mss-container">
        <!-- 統計卡片 -->
        <div class="mss-stats-section">
            <h2>系統概覽</h2>
            <div class="mss-stats-grid" id="statsGrid">
                <div class="mss-stat-card">
                    <div class="mss-stat-icon">📋</div>
                    <div class="mss-stat-number" id="totalOrders">-</div>
                    <div class="mss-stat-label">總維修單</div>
                </div>
                <div class="mss-stat-card">
                    <div class="mss-stat-icon">⏳</div>
                    <div class="mss-stat-number" id="pendingOrders">-</div>
                    <div class="mss-stat-label">待簽名</div>
                </div>
                <div class="mss-stat-card">
                    <div class="mss-stat-icon">✅</div>
                    <div class="mss-stat-number" id="completedOrders">-</div>
                    <div class="mss-stat-label">已完成</div>
                </div>
                <div class="mss-stat-card">
                    <div class="mss-stat-icon">📱</div>
                    <div class="mss-stat-number" id="todayOrders">-</div>
                    <div class="mss-stat-label">今日新增</div>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="mss-quick-actions">
            <h2>快速操作</h2>
            <div class="mss-action-grid">
                <a href="<?php echo MSS_Frontend_Portal::get_create_url(); ?>" class="mss-action-card">
                    <div class="mss-action-icon">➕</div>
                    <div class="mss-action-title">新增維修單</div>
                    <div class="mss-action-desc">建立新的維修單並上傳照片</div>
                </a>
                <div class="mss-action-card" onclick="showSearchModal()">
                    <div class="mss-action-icon">🔍</div>
                    <div class="mss-action-title">搜尋維修單</div>
                    <div class="mss-action-desc">查找現有的維修單</div>
                </div>
                <a href="<?php echo MSS_Frontend_Portal::get_settings_url(); ?>" class="mss-action-card">
                    <div class="mss-action-icon">⚙️</div>
                    <div class="mss-action-title">系統設定</div>
                    <div class="mss-action-desc">管理工務人員和系統設定</div>
                </div>
                <div class="mss-action-card" onclick="refreshData()">
                    <div class="mss-action-icon">🔄</div>
                    <div class="mss-action-title">重新整理</div>
                    <div class="mss-action-desc">更新最新的系統數據</div>
                </div>
            </div>
        </div>

        <!-- 最近的維修單 -->
        <div class="mss-recent-orders">
            <div class="mss-section-header">
                <h2>最近的維修單</h2>
                <button class="mss-button mss-button-secondary" onclick="loadOrders(1)">查看全部</button>
            </div>
            
            <div class="mss-search-box" style="display: none;" id="searchBox">
                <input type="text" id="searchInput" placeholder="搜尋維修單號碼、案場、工務姓名...">
                <button onclick="searchOrders()" class="mss-button mss-button-primary">搜尋</button>
                <button onclick="hideSearch()" class="mss-button mss-button-secondary">取消</button>
            </div>
            
            <div class="mss-orders-list" id="ordersList">
                <div class="mss-loading">載入中...</div>
            </div>
            
            <div class="mss-pagination" id="pagination" style="display: none;"></div>
        </div>
    </div>

    <!-- 搜索模態框 -->
    <div id="searchModal" class="mss-modal">
        <div class="mss-modal-content">
            <div class="mss-modal-header">
                <h3>搜尋維修單</h3>
                <span class="mss-modal-close" onclick="hideSearchModal()">&times;</span>
            </div>
            <div class="mss-modal-body">
                <input type="text" id="modalSearchInput" placeholder="輸入維修單號碼、案場名稱或工務姓名">
                <button onclick="performSearch()" class="mss-button mss-button-primary">搜尋</button>
            </div>
        </div>
    </div>

    <!-- 維修單詳情模態框 -->
    <div id="orderModal" class="mss-modal">
        <div class="mss-modal-content mss-modal-large">
            <div class="mss-modal-header">
                <h3>維修單詳情</h3>
                <span class="mss-modal-close" onclick="hideOrderModal()">&times;</span>
            </div>
            <div class="mss-modal-body" id="orderModalBody">
                <!-- 動態載入維修單詳情 -->
            </div>
        </div>
    </div>

    <!-- 載入提示 -->
    <div id="loadingOverlay" class="mss-loading-overlay" style="display: none;">
        <div class="mss-loading-spinner">
            <div class="mss-spinner"></div>
            <div class="mss-loading-text">載入中...</div>
        </div>
    </div>

    <?php wp_footer(); ?>

    <script>
    // 全局變數
    let currentPage = 1;
    let currentSearch = '';
    let orders = [];

    // 頁面載入時初始化
    document.addEventListener('DOMContentLoaded', function() {
        loadStats();
        loadOrders(1);
    });

    // 載入統計數據
    function loadStats() {
        // 這裡可以添加 AJAX 請求來獲取實際統計數據
        // 暫時顯示模擬數據
        setTimeout(() => {
            document.getElementById('totalOrders').textContent = '0';
            document.getElementById('pendingOrders').textContent = '0';
            document.getElementById('completedOrders').textContent = '0';
            document.getElementById('todayOrders').textContent = '0';
        }, 500);
    }

    // 載入維修單列表
    function loadOrders(page, search = '') {
        currentPage = page;
        currentSearch = search;
        
        showLoading();
        
        jQuery.ajax({
            url: mss_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'mss_get_maintenance_orders',
                nonce: mss_frontend.nonce,
                page: page,
                search: search
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    displayOrders(response.data.orders);
                    displayPagination(response.data.pagination);
                    updateStats(response.data.orders);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                hideLoading();
                showError('載入失敗，請檢查網路連接');
            }
        });
    }

    // 顯示維修單列表
    function displayOrders(ordersList) {
        const container = document.getElementById('ordersList');
        
        if (ordersList.length === 0) {
            container.innerHTML = `
                <div class="mss-no-orders">
                    <div class="mss-no-orders-icon">📋</div>
                    <h3>尚未建立任何維修單</h3>
                    <p>點擊「新增維修單」開始建立第一個維修單</p>
                    <a href="${mss_frontend.home_url}/?mss_create=1" class="mss-button mss-button-primary">新增維修單</a>
                </div>
            `;
            return;
        }
        
        let html = '';
        ordersList.forEach(order => {
            const statusClass = order.status === 'completed' ? 'completed' : 'pending';
            html += `
                <div class="mss-order-card" onclick="showOrderDetails(${order.id})">
                    <div class="mss-order-header">
                        <div class="mss-order-number">${order.form_number}</div>
                        <div class="mss-order-status mss-status-${statusClass}">${order.status_text}</div>
                    </div>
                    <div class="mss-order-content">
                        <div class="mss-order-info">
                            <div><strong>案場：</strong>${order.location}</div>
                            <div><strong>工務：</strong>${order.constructor_name}</div>
                            <div><strong>地址：</strong>${[order.building, order.floor, order.unit].filter(Boolean).join(' ') || '未填寫'}</div>
                        </div>
                        <div class="mss-order-date">${order.formatted_date}</div>
                    </div>
                    <div class="mss-order-actions">
                        <button onclick="event.stopPropagation(); copyCustomerLink('${order.customer_link}')" class="mss-button mss-button-small">複製客戶連結</button>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        orders = ordersList;
    }

    // 顯示分頁
    function displayPagination(pagination) {
        const container = document.getElementById('pagination');
        
        if (pagination.total_pages <= 1) {
            container.style.display = 'none';
            return;
        }
        
        container.style.display = 'block';
        
        let html = '';
        
        // 上一頁
        if (pagination.current_page > 1) {
            html += `<button onclick="loadOrders(${pagination.current_page - 1}, '${currentSearch}')" class="mss-pagination-btn">上一頁</button>`;
        }
        
        // 頁碼
        for (let i = 1; i <= pagination.total_pages; i++) {
            const activeClass = i === pagination.current_page ? 'active' : '';
            html += `<button onclick="loadOrders(${i}, '${currentSearch}')" class="mss-pagination-btn ${activeClass}">${i}</button>`;
        }
        
        // 下一頁
        if (pagination.current_page < pagination.total_pages) {
            html += `<button onclick="loadOrders(${pagination.current_page + 1}, '${currentSearch}')" class="mss-pagination-btn">下一頁</button>`;
        }
        
        container.innerHTML = html;
    }

    // 更新統計數據
    function updateStats(ordersList) {
        const total = ordersList.length;
        const pending = ordersList.filter(order => order.status === 'pending_customer_signature').length;
        const completed = ordersList.filter(order => order.status === 'completed').length;
        
        // 這裡應該從服務器獲取實際的統計數據
        // 暫時使用當前頁面的數據作為示例
    }

    // 搜尋功能
    function showSearchModal() {
        document.getElementById('searchModal').style.display = 'block';
        document.getElementById('modalSearchInput').focus();
    }

    function hideSearchModal() {
        document.getElementById('searchModal').style.display = 'none';
    }

    function performSearch() {
        const searchTerm = document.getElementById('modalSearchInput').value.trim();
        if (searchTerm) {
            loadOrders(1, searchTerm);
            hideSearchModal();
            document.getElementById('searchBox').style.display = 'block';
            document.getElementById('searchInput').value = searchTerm;
        }
    }

    function searchOrders() {
        const searchTerm = document.getElementById('searchInput').value.trim();
        loadOrders(1, searchTerm);
    }

    function hideSearch() {
        document.getElementById('searchBox').style.display = 'none';
        document.getElementById('searchInput').value = '';
        if (currentSearch) {
            loadOrders(1, '');
        }
    }

    // 複製客戶連結
    function copyCustomerLink(link) {
        navigator.clipboard.writeText(link).then(function() {
            showSuccess('客戶簽名連結已複製到剪貼板！');
        }).catch(function() {
            // 備用方法
            const textArea = document.createElement('textarea');
            textArea.value = link;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showSuccess('客戶簽名連結已複製到剪貼板！');
        });
    }

    // 顯示維修單詳情
    function showOrderDetails(orderId) {
        // 這裡可以添加顯示維修單詳情的功能
        showSuccess('維修單詳情功能開發中...');
    }

    function hideOrderModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    // 重新整理數據
    function refreshData() {
        loadStats();
        loadOrders(currentPage, currentSearch);
        showSuccess('數據已更新！');
    }

    // 選單切換（手機版）
    function toggleMenu() {
        const links = document.getElementById('navbarLinks');
        links.classList.toggle('active');
    }

    // 工具函數
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    function showSuccess(message) {
        showNotification(message, 'success');
    }

    function showError(message) {
        showNotification(message, 'error');
    }

    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `mss-notification mss-notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    // Enter 鍵搜尋
    document.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (document.getElementById('modalSearchInput') === document.activeElement) {
                performSearch();
            } else if (document.getElementById('searchInput') === document.activeElement) {
                searchOrders();
            }
        }
    });
    </script>
</body>
</html>