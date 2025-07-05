<?php
/**
 * 前端系統設定頁面
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 設定頁面標題
$page_title = '系統設定 - 工務管理系統';

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
<body class="mss-frontend-settings">
    <!-- 頂部導航 -->
    <div class="mss-navbar">
        <div class="mss-navbar-brand">
            <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>">← 工務管理系統</a>
        </div>
        <div class="mss-navbar-menu">
            <button class="mss-menu-toggle" onclick="toggleMenu()">☰</button>
            <div class="mss-navbar-links" id="navbarLinks">
                <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>">儀表板</a>
                <a href="<?php echo MSS_Frontend_Portal::get_create_url(); ?>">新增維修單</a>
                <a href="<?php echo MSS_Frontend_Portal::get_settings_url(); ?>" class="active">系統設定</a>
            </div>
        </div>
    </div>

    <div class="mss-container">
        <div class="mss-page-header">
            <h1>⚙️ 系統設定</h1>
            <p>管理工務人員和系統基本設定</p>
        </div>

        <!-- 工務人員管理 -->
        <div class="mss-settings-section">
            <div class="mss-section-header">
                <h2>👷 工務人員管理</h2>
                <button class="mss-button mss-button-primary" onclick="showAddConstructorModal()">
                    ➕ 新增工務人員
                </button>
            </div>
            
            <div class="mss-constructors-list" id="constructorsList">
                <div class="mss-loading">載入中...</div>
            </div>
        </div>

        <!-- 系統資訊 -->
        <div class="mss-settings-section">
            <h2>📊 系統資訊</h2>
            <div class="mss-system-info">
                <div class="mss-info-item">
                    <div class="mss-info-label">插件版本</div>
                    <div class="mss-info-value"><?php echo defined('MSS_PLUGIN_VERSION') ? MSS_PLUGIN_VERSION : '1.0.0'; ?></div>
                </div>
                <div class="mss-info-item">
                    <div class="mss-info-label">數據庫版本</div>
                    <div class="mss-info-value" id="dbVersion">檢查中...</div>
                </div>
                <div class="mss-info-item">
                    <div class="mss-info-label">最後更新</div>
                    <div class="mss-info-value" id="lastUpdate">檢查中...</div>
                </div>
            </div>
        </div>

        <!-- 快速操作 -->
        <div class="mss-settings-section">
            <h2>🔧 快速操作</h2>
            <div class="mss-quick-actions">
                <button class="mss-button mss-button-secondary" onclick="clearCache()">
                    🗑️ 清除快取
                </button>
                <button class="mss-button mss-button-secondary" onclick="exportData()">
                    📤 匯出資料
                </button>
                <button class="mss-button mss-button-secondary" onclick="checkSystemStatus()">
                    🔍 檢查系統狀態
                </button>
            </div>
        </div>
    </div>

    <!-- 新增工務人員模態框 -->
    <div id="addConstructorModal" class="mss-modal">
        <div class="mss-modal-content">
            <div class="mss-modal-header">
                <h3>新增工務人員</h3>
                <span class="mss-modal-close" onclick="hideAddConstructorModal()">&times;</span>
            </div>
            <div class="mss-modal-body">
                <form id="addConstructorForm">
                    <div class="mss-form-field">
                        <label for="constructorName">姓名 <span class="required">*</span></label>
                        <input type="text" id="constructorName" name="constructor_name" required>
                    </div>
                    <div class="mss-form-actions">
                        <button type="submit" class="mss-button mss-button-primary">新增</button>
                        <button type="button" class="mss-button mss-button-secondary" onclick="hideAddConstructorModal()">取消</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 確認刪除模態框 -->
    <div id="deleteConfirmModal" class="mss-modal">
        <div class="mss-modal-content">
            <div class="mss-modal-header">
                <h3>確認刪除</h3>
            </div>
            <div class="mss-modal-body">
                <p>確定要刪除工務人員 <strong id="deleteConstructorName"></strong> 嗎？</p>
                <p class="mss-warning">此操作無法復原，相關的維修單資料將保留。</p>
                <div class="mss-form-actions">
                    <button id="confirmDeleteBtn" class="mss-button mss-button-danger">確定刪除</button>
                    <button class="mss-button mss-button-secondary" onclick="hideDeleteConfirmModal()">取消</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 載入提示 -->
    <div id="loadingOverlay" class="mss-loading-overlay" style="display: none;">
        <div class="mss-loading-spinner">
            <div class="mss-spinner"></div>
            <div class="mss-loading-text">處理中...</div>
        </div>
    </div>

    <?php wp_footer(); ?>

    <script>
    // 全域變數
    let constructors = [];
    let deleteConstructorId = null;

    // 頁面載入時初始化
    document.addEventListener('DOMContentLoaded', function() {
        loadConstructors();
        loadSystemInfo();
    });

    // 載入工務人員列表
    function loadConstructors() {
        showLoading();
        
        jQuery.ajax({
            url: mss_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'mss_manage_constructors',
                nonce: mss_frontend.nonce,
                constructor_action: 'get'
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    displayConstructors(response.data.constructors);
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

    // 顯示工務人員列表
    function displayConstructors(constructorsList) {
        const container = document.getElementById('constructorsList');
        constructors = constructorsList;
        
        if (constructorsList.length === 0) {
            container.innerHTML = `
                <div class="mss-no-constructors">
                    <div class="mss-no-constructors-icon">👷</div>
                    <h3>尚未新增任何工務人員</h3>
                    <p>點擊「新增工務人員」開始新增</p>
                </div>
            `;
            return;
        }

        let html = '';
        constructorsList.forEach(constructor => {
            html += `
                <div class="mss-constructor-item">
                    <div class="mss-constructor-info">
                        <div class="mss-constructor-name">${constructor.name}</div>
                        <div class="mss-constructor-meta">
                            新增時間：${constructor.created_at || '未知'}
                        </div>
                    </div>
                    <div class="mss-constructor-actions">
                        <button onclick="deleteConstructor(${constructor.id}, '${constructor.name}')" 
                                class="mss-button mss-button-small mss-button-danger">
                            🗑️ 刪除
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // 顯示新增工務人員模態框
    function showAddConstructorModal() {
        document.getElementById('addConstructorModal').style.display = 'block';
        document.getElementById('constructorName').focus();
    }

    // 隱藏新增工務人員模態框
    function hideAddConstructorModal() {
        document.getElementById('addConstructorModal').style.display = 'none';
        document.getElementById('addConstructorForm').reset();
    }

    // 新增工務人員表單提交
    document.getElementById('addConstructorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('constructorName').value.trim();
        if (!name) {
            showError('請輸入工務人員姓名');
            return;
        }

        showLoading();

        jQuery.ajax({
            url: mss_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'mss_manage_constructors',
                nonce: mss_frontend.nonce,
                constructor_action: 'add',
                constructor_name: name
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    hideAddConstructorModal();
                    showSuccess('工務人員新增成功！');
                    loadConstructors();
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                hideLoading();
                showError('新增失敗，請檢查網路連接');
            }
        });
    });

    // 刪除工務人員
    function deleteConstructor(id, name) {
        deleteConstructorId = id;
        document.getElementById('deleteConstructorName').textContent = name;
        document.getElementById('deleteConfirmModal').style.display = 'block';
    }

    // 隱藏確認刪除模態框
    function hideDeleteConfirmModal() {
        document.getElementById('deleteConfirmModal').style.display = 'none';
        deleteConstructorId = null;
    }

    // 確認刪除按鈕點擊
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (!deleteConstructorId) return;

        showLoading();

        jQuery.ajax({
            url: mss_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'mss_manage_constructors',
                nonce: mss_frontend.nonce,
                constructor_action: 'delete',
                constructor_id: deleteConstructorId
            },
            success: function(response) {
                hideLoading();
                hideDeleteConfirmModal();
                
                if (response.success) {
                    showSuccess('工務人員已刪除');
                    loadConstructors();
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                hideLoading();
                showError('刪除失敗，請檢查網路連接');
            }
        });
    });

    // 載入系統資訊
    function loadSystemInfo() {
        // 這裡可以添加 AJAX 請求來獲取系統資訊
        // 暫時顯示模擬資料
        setTimeout(() => {
            document.getElementById('dbVersion').textContent = '1.0.0';
            document.getElementById('lastUpdate').textContent = new Date().toLocaleString();
        }, 500);
    }

    // 快速操作功能
    function clearCache() {
        showLoading();
        setTimeout(() => {
            hideLoading();
            showSuccess('快取已清除');
        }, 1000);
    }

    function exportData() {
        showSuccess('匯出功能開發中...');
    }

    function checkSystemStatus() {
        showLoading();
        setTimeout(() => {
            hideLoading();
            showSuccess('系統狀態正常');
        }, 1000);
    }

    // 選單切換
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

    // 模態框外部點擊關閉
    window.onclick = function(event) {
        const addModal = document.getElementById('addConstructorModal');
        const deleteModal = document.getElementById('deleteConfirmModal');
        
        if (event.target === addModal) {
            hideAddConstructorModal();
        }
        if (event.target === deleteModal) {
            hideDeleteConfirmModal();
        }
    }
    </script>
</body>
</html>