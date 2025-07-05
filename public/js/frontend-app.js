/**
 * 前端應用程式主要 JavaScript
 * 維修單線上簽名系統
 */

// 等待 DOM 載入完成
document.addEventListener('DOMContentLoaded', function() {
    // 初始化應用程式
    initializeApp();
});

// 初始化應用程式
function initializeApp() {
    // 初始化通用功能
    initializeTooltips();
    initializeDateFields();
    initializeValidation();
    
    // 根據當前頁面初始化特定功能
    const currentPage = getCurrentPage();
    switch (currentPage) {
        case 'dashboard':
            initializeDashboard();
            break;
        case 'create':
            initializeCreateForm();
            break;
        case 'settings':
            initializeSettings();
            break;
    }
}

// 獲取當前頁面類型
function getCurrentPage() {
    const body = document.body;
    if (body.classList.contains('mss-frontend-dashboard')) return 'dashboard';
    if (body.classList.contains('mss-frontend-create')) return 'create';
    if (body.classList.contains('mss-frontend-settings')) return 'settings';
    return 'unknown';
}

// 初始化工具提示
function initializeTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

// 初始化日期欄位
function initializeDateFields() {
    const dateFields = document.querySelectorAll('input[type="date"]');
    dateFields.forEach(field => {
        if (!field.value) {
            field.value = new Date().toISOString().split('T')[0];
        }
    });
}

// 初始化表單驗證
function initializeValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(form)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
}

// 初始化儀表板
function initializeDashboard() {
    console.log('Dashboard initialized');
    
    // 載入統計數據
    loadDashboardStats();
    
    // 設置自動刷新
    setInterval(loadDashboardStats, 60000); // 每分鐘刷新一次
}

// 初始化建立表單
function initializeCreateForm() {
    console.log('Create form initialized');
    
    // 初始化圖片上傳
    initializeImageUpload();
    
    // 初始化維修單號生成
    initializeFormNumberGeneration();
}

// 初始化設定頁面
function initializeSettings() {
    console.log('Settings initialized');
    
    // 載入系統狀態
    loadSystemStatus();
}

// 載入儀表板統計
function loadDashboardStats() {
    if (typeof jQuery === 'undefined') return;
    
    jQuery.ajax({
        url: mss_frontend.ajax_url,
        type: 'POST',
        data: {
            action: 'mss_get_dashboard_stats',
            nonce: mss_frontend.nonce
        },
        success: function(response) {
            if (response.success) {
                updateDashboardStats(response.data);
            }
        },
        error: function() {
            console.error('Failed to load dashboard stats');
        }
    });
}

// 更新儀表板統計
function updateDashboardStats(stats) {
    const elements = {
        'totalOrders': stats.total_orders || 0,
        'pendingOrders': stats.pending_orders || 0,
        'completedOrders': stats.completed_orders || 0,
        'todayOrders': stats.today_orders || 0
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            animateNumber(element, elements[id]);
        }
    });
}

// 數字動畫
function animateNumber(element, targetValue) {
    const startValue = parseInt(element.textContent) || 0;
    const duration = 1000;
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
        
        element.textContent = currentValue;
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

// 初始化圖片上傳
function initializeImageUpload() {
    const fileInput = document.getElementById('maintenanceImages');
    const preview = document.getElementById('imagePreview');
    
    if (!fileInput || !preview) return;
    
    fileInput.addEventListener('change', function(e) {
        handleImageFiles(e.target.files);
    });
    
    // 拖拽上傳
    preview.addEventListener('dragover', function(e) {
        e.preventDefault();
        preview.classList.add('drag-over');
    });
    
    preview.addEventListener('dragleave', function(e) {
        e.preventDefault();
        preview.classList.remove('drag-over');
    });
    
    preview.addEventListener('drop', function(e) {
        e.preventDefault();
        preview.classList.remove('drag-over');
        handleImageFiles(e.dataTransfer.files);
    });
}

// 處理圖片檔案
function handleImageFiles(files) {
    const preview = document.getElementById('imagePreview');
    const maxFiles = 10;
    const maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if (files.length > maxFiles) {
        showError(`最多只能上傳 ${maxFiles} 張圖片`);
        return;
    }
    
    Array.from(files).forEach((file, index) => {
        if (!file.type.startsWith('image/')) {
            showError(`檔案 ${file.name} 不是有效的圖片格式`);
            return;
        }
        
        if (file.size > maxFileSize) {
            showError(`檔案 ${file.name} 大小超過 5MB 限制`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            createImagePreview(file, e.target.result, index);
        };
        reader.readAsDataURL(file);
    });
}

// 建立圖片預覽
function createImagePreview(file, dataURL, index) {
    const preview = document.getElementById('imagePreview');
    const previewItem = document.createElement('div');
    previewItem.className = 'mss-preview-item';
    
    previewItem.innerHTML = `
        <img src="${dataURL}" alt="預覽圖片 ${index + 1}" loading="lazy">
        <div class="mss-preview-overlay">
            <div class="mss-preview-actions">
                <button type="button" onclick="removeImagePreview(this)" class="mss-button mss-button-small mss-button-danger">
                    🗑️ 移除
                </button>
            </div>
        </div>
        <div class="mss-preview-info">
            <div class="mss-preview-name">${file.name}</div>
            <div class="mss-preview-size">${formatFileSize(file.size)}</div>
        </div>
    `;
    
    preview.appendChild(previewItem);
}

// 移除圖片預覽
function removeImagePreview(button) {
    const previewItem = button.closest('.mss-preview-item');
    if (previewItem) {
        previewItem.remove();
    }
}

// 格式化檔案大小
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// 初始化維修單號生成
function initializeFormNumberGeneration() {
    const formNumberInput = document.getElementById('formNumber');
    if (!formNumberInput) return;
    
    // 自動生成維修單號
    generateFormNumber();
    
    // 監聽手動輸入
    formNumberInput.addEventListener('input', function() {
        validateFormNumber(this.value);
    });
}

// 生成維修單號
function generateFormNumber() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hour = String(now.getHours()).padStart(2, '0');
    const minute = String(now.getMinutes()).padStart(2, '0');
    const random = String(Math.floor(Math.random() * 100)).padStart(2, '0');
    
    const formNumber = `MF${year}${month}${day}${hour}${minute}${random}`;
    
    const formNumberInput = document.getElementById('formNumber');
    if (formNumberInput) {
        formNumberInput.value = formNumber;
    }
    
    return formNumber;
}

// 驗證維修單號格式
function validateFormNumber(formNumber) {
    const pattern = /^MF\d{12}$/;
    const isValid = pattern.test(formNumber);
    
    const input = document.getElementById('formNumber');
    if (input) {
        input.classList.toggle('invalid', !isValid);
    }
    
    return isValid;
}

// 載入系統狀態
function loadSystemStatus() {
    if (typeof jQuery === 'undefined') return;
    
    jQuery.ajax({
        url: mss_frontend.ajax_url,
        type: 'POST',
        data: {
            action: 'mss_get_system_status',
            nonce: mss_frontend.nonce
        },
        success: function(response) {
            if (response.success) {
                updateSystemStatus(response.data);
            }
        },
        error: function() {
            console.error('Failed to load system status');
        }
    });
}

// 更新系統狀態
function updateSystemStatus(status) {
    const elements = {
        'dbVersion': status.db_version || 'Unknown',
        'lastUpdate': status.last_update || 'Unknown'
    };
    
    Object.keys(elements).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = elements[id];
        }
    });
}

// 表單驗證
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    let firstInvalidField = null;
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        const isFieldValid = value !== '';
        
        field.classList.toggle('invalid', !isFieldValid);
        
        if (!isFieldValid) {
            isValid = false;
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
        }
    });
    
    if (!isValid && firstInvalidField) {
        firstInvalidField.focus();
        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        showError(mss_frontend.messages.required_fields);
    }
    
    return isValid;
}

// 工具提示功能
function showTooltip(event) {
    const element = event.target;
    const tooltipText = element.getAttribute('data-tooltip');
    
    if (!tooltipText) return;
    
    const tooltip = document.createElement('div');
    tooltip.className = 'mss-tooltip';
    tooltip.textContent = tooltipText;
    
    document.body.appendChild(tooltip);
    
    const rect = element.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
    
    element._tooltip = tooltip;
}

function hideTooltip(event) {
    const element = event.target;
    if (element._tooltip) {
        document.body.removeChild(element._tooltip);
        element._tooltip = null;
    }
}

// 通知系統
function showNotification(message, type = 'info', duration = 3000) {
    const notification = document.createElement('div');
    notification.className = `mss-notification mss-notification-${type}`;
    notification.textContent = message;
    
    // 添加關閉按鈕
    const closeBtn = document.createElement('button');
    closeBtn.className = 'mss-notification-close';
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = () => hideNotification(notification);
    notification.appendChild(closeBtn);
    
    document.body.appendChild(notification);
    
    // 顯示動畫
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // 自動隱藏
    setTimeout(() => {
        hideNotification(notification);
    }, duration);
}

function hideNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

// 便利函數
function showSuccess(message, duration = 3000) {
    showNotification(message, 'success', duration);
}

function showError(message, duration = 5000) {
    showNotification(message, 'error', duration);
}

function showInfo(message, duration = 3000) {
    showNotification(message, 'info', duration);
}

function showWarning(message, duration = 4000) {
    showNotification(message, 'warning', duration);
}

// 網路狀態檢查
function checkNetworkStatus() {
    if (navigator.onLine) {
        return true;
    } else {
        showError('網路連接中斷，請檢查您的網路設定');
        return false;
    }
}

// 載入狀態管理
function showGlobalLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

function hideGlobalLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// 本地存儲工具
function saveToLocalStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (e) {
        console.error('Failed to save to localStorage:', e);
        return false;
    }
}

function loadFromLocalStorage(key) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : null;
    } catch (e) {
        console.error('Failed to load from localStorage:', e);
        return null;
    }
}

// 清除本地存儲
function clearLocalStorage() {
    try {
        localStorage.clear();
        showSuccess('本地資料已清除');
    } catch (e) {
        showError('清除本地資料失敗');
    }
}

// 網路監聽
window.addEventListener('online', function() {
    showSuccess('網路連接已恢復');
});

window.addEventListener('offline', function() {
    showWarning('網路連接已中斷');
});

// 防止意外離開頁面
window.addEventListener('beforeunload', function(e) {
    const forms = document.querySelectorAll('form');
    let hasUnsavedChanges = false;
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input.value.trim() !== '') {
                hasUnsavedChanges = true;
            }
        });
    });
    
    if (hasUnsavedChanges) {
        e.preventDefault();
        e.returnValue = '您有未保存的更改，確定要離開嗎？';
    }
});

// 導出公共 API
window.MSS = {
    // 通知系統
    showSuccess,
    showError,
    showInfo,
    showWarning,
    
    // 載入狀態
    showGlobalLoading,
    hideGlobalLoading,
    
    // 本地存儲
    saveToLocalStorage,
    loadFromLocalStorage,
    clearLocalStorage,
    
    // 工具函數
    generateFormNumber,
    validateFormNumber,
    formatFileSize,
    checkNetworkStatus
};