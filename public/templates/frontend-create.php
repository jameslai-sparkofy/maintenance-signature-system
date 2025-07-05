<?php
/**
 * 前端新增維修單頁面
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

// 設定頁面標題
$page_title = '新增維修單 - 工務管理系統';

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
<body class="mss-frontend-create">
    <!-- 頂部導航 -->
    <div class="mss-navbar">
        <div class="mss-navbar-brand">
            <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>">← 工務管理系統</a>
        </div>
        <div class="mss-navbar-menu">
            <button class="mss-menu-toggle" onclick="toggleMenu()">☰</button>
            <div class="mss-navbar-links" id="navbarLinks">
                <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>">儀表板</a>
                <a href="<?php echo MSS_Frontend_Portal::get_create_url(); ?>" class="active">新增維修單</a>
                <a href="<?php echo MSS_Frontend_Portal::get_settings_url(); ?>">系統設定</a>
            </div>
        </div>
    </div>

    <div class="mss-container">
        <div class="mss-page-header">
            <h1>📋 新增維修單</h1>
            <p>填寫維修單資訊並上傳相關照片</p>
        </div>

        <form id="createMaintenanceForm" class="mss-create-form" enctype="multipart/form-data">
            <!-- 基本資訊 -->
            <div class="mss-form-section">
                <h2>📝 基本資訊</h2>
                
                <div class="mss-form-row">
                    <div class="mss-form-field">
                        <label for="formNumber">維修單號碼 <span class="required">*</span></label>
                        <input type="text" id="formNumber" name="form_number" required>
                        <button type="button" onclick="generateFormNumber()" class="mss-button mss-button-small">自動生成</button>
                    </div>
                </div>
                
                <div class="mss-form-row">
                    <div class="mss-form-field">
                        <label for="date">維修日期 <span class="required">*</span></label>
                        <input type="date" id="date" name="date" required>
                    </div>
                    
                    <div class="mss-form-field">
                        <label for="constructorName">工務姓名 <span class="required">*</span></label>
                        <select id="constructorName" name="constructor_name" required>
                            <option value="">請選擇工務姓名</option>
                        </select>
                        <button type="button" onclick="refreshConstructors()" class="mss-button mss-button-small">🔄 重新整理</button>
                    </div>
                </div>
                
                <div class="mss-form-field">
                    <label for="location">維修案場 <span class="required">*</span></label>
                    <input type="text" id="location" name="location" placeholder="請輸入維修案場名稱" required>
                </div>
            </div>

            <!-- 地址資訊 -->
            <div class="mss-form-section">
                <h2>📍 地址資訊</h2>
                
                <div class="mss-form-row">
                    <div class="mss-form-field">
                        <label for="building">維修棟別</label>
                        <input type="text" id="building" name="building" placeholder="例：A棟、B棟">
                    </div>
                    
                    <div class="mss-form-field">
                        <label for="floor">樓層</label>
                        <input type="text" id="floor" name="floor" placeholder="例：1F、2F、B1">
                    </div>
                </div>
                
                <div class="mss-form-field">
                    <label for="unit">戶別</label>
                    <input type="text" id="unit" name="unit" placeholder="例：101、102">
                </div>
            </div>

            <!-- 問題描述 -->
            <div class="mss-form-section">
                <h2>🔧 問題描述</h2>
                
                <div class="mss-form-field">
                    <label for="problemDescription">詳細問題描述 <span class="required">*</span></label>
                    <textarea id="problemDescription" name="problem_description" rows="6" placeholder="請詳細描述維修問題的狀況..." required></textarea>
                </div>
            </div>

            <!-- 圖片上傳 -->
            <div class="mss-form-section">
                <h2>📷 相關圖片</h2>
                
                <div class="mss-form-field">
                    <label for="maintenanceImages">上傳圖片</label>
                    
                    <!-- 相機拍照按鈕 -->
                    <div class="mss-camera-controls">
                        <button type="button" onclick="openCamera()" class="mss-button mss-button-camera">
                            📸 拍照
                        </button>
                        <button type="button" onclick="openGallery()" class="mss-button mss-button-gallery">
                            🖼️ 選擇照片
                        </button>
                    </div>
                    
                    <!-- 隱藏的文件輸入 -->
                    <input type="file" id="maintenanceImages" name="maintenance_images[]" 
                           accept="image/*" multiple capture="environment" style="display: none;">
                    
                    <div class="mss-upload-info">
                        支援多張圖片上傳，格式：JPG、PNG、GIF
                    </div>
                    
                    <!-- 圖片預覽區 -->
                    <div id="imagePreview" class="mss-image-preview"></div>
                </div>
            </div>

            <!-- 提交按鈕 -->
            <div class="mss-form-actions">
                <button type="submit" id="submitBtn" class="mss-button mss-button-primary mss-button-large">
                    ✅ 建立維修單
                </button>
                <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>" class="mss-button mss-button-secondary mss-button-large">
                    取消
                </a>
            </div>
        </form>
    </div>

    <!-- 成功模態框 -->
    <div id="successModal" class="mss-modal">
        <div class="mss-modal-content">
            <div class="mss-modal-header">
                <h3>✅ 維修單建立成功！</h3>
            </div>
            <div class="mss-modal-body">
                <div class="mss-success-info">
                    <p><strong>維修單號碼：</strong><span id="successFormNumber"></span></p>
                    <p><strong>客戶簽名連結：</strong></p>
                    <div class="mss-link-container">
                        <input type="text" id="customerLink" readonly>
                        <button onclick="copyLink()" class="mss-button mss-button-small">複製連結</button>
                    </div>
                    <div class="mss-qr-code" id="qrCode"></div>
                </div>
                <div class="mss-modal-actions">
                    <button onclick="shareLink()" class="mss-button mss-button-primary">📤 分享連結</button>
                    <button onclick="createAnother()" class="mss-button mss-button-secondary">➕ 建立下一個</button>
                    <a href="<?php echo MSS_Frontend_Portal::get_dashboard_url(); ?>" class="mss-button mss-button-outline">回到首頁</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 載入提示 -->
    <div id="loadingOverlay" class="mss-loading-overlay" style="display: none;">
        <div class="mss-loading-spinner">
            <div class="mss-spinner"></div>
            <div class="mss-loading-text">建立中...</div>
        </div>
    </div>

    <?php wp_footer(); ?>

    <script>
    // 頁面載入時初始化
    document.addEventListener('DOMContentLoaded', function() {
        // 設定當前日期
        document.getElementById('date').value = new Date().toISOString().split('T')[0];
        
        // 自動生成維修單號碼
        generateFormNumber();
        
        // 載入工務人員列表
        loadConstructors();
        
        // 初始化圖片上傳
        initImageUpload();
    });

    // 自動生成維修單號碼
    function generateFormNumber() {
        const now = new Date();
        const dateString = now.getFullYear().toString() + 
                          (now.getMonth() + 1).toString().padStart(2, '0') + 
                          now.getDate().toString().padStart(2, '0');
        const timeString = now.getHours().toString().padStart(2, '0') + 
                          now.getMinutes().toString().padStart(2, '0');
        const randomString = Math.floor(Math.random() * 100).toString().padStart(2, '0');
        
        document.getElementById('formNumber').value = `MF${dateString}${timeString}${randomString}`;
    }

    // 載入工務人員列表
    function loadConstructors() {
        jQuery.ajax({
            url: mss_frontend.ajax_url,
            type: 'POST',
            data: {
                action: 'mss_manage_constructors',
                nonce: mss_frontend.nonce,
                constructor_action: 'get'
            },
            success: function(response) {
                if (response.success) {
                    const select = document.getElementById('constructorName');
                    select.innerHTML = '<option value="">請選擇工務姓名</option>';
                    
                    response.data.constructors.forEach(function(constructor) {
                        const option = document.createElement('option');
                        option.value = constructor.name;
                        option.textContent = constructor.name;
                        select.appendChild(option);
                    });
                }
            }
        });
    }

    // 重新整理工務人員列表
    function refreshConstructors() {
        loadConstructors();
        showSuccess('工務人員列表已更新');
    }

    // 初始化圖片上傳
    function initImageUpload() {
        const input = document.getElementById('maintenanceImages');
        
        input.addEventListener('change', function(e) {
            handleFileSelect(e.target.files);
        });
    }

    // 開啟相機
    function openCamera() {
        const input = document.getElementById('maintenanceImages');
        input.setAttribute('capture', 'environment');
        input.click();
    }

    // 開啟相簿
    function openGallery() {
        const input = document.getElementById('maintenanceImages');
        input.removeAttribute('capture');
        input.click();
    }

    // 處理檔案選擇
    function handleFileSelect(files) {
        const preview = document.getElementById('imagePreview');
        
        Array.from(files).forEach(function(file, index) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'mss-preview-item';
                    previewItem.innerHTML = `
                        <img src="${e.target.result}" alt="預覽圖片 ${index + 1}">
                        <div class="mss-preview-actions">
                            <button type="button" onclick="removePreview(this)" class="mss-button mss-button-small mss-button-danger">移除</button>
                        </div>
                        <div class="mss-preview-name">${file.name}</div>
                    `;
                    preview.appendChild(previewItem);
                };
                
                reader.readAsDataURL(file);
            }
        });
    }

    // 移除預覽圖片
    function removePreview(button) {
        const previewItem = button.closest('.mss-preview-item');
        previewItem.remove();
        
        // 重新設定檔案輸入
        document.getElementById('maintenanceImages').value = '';
    }

    // 表單提交
    document.getElementById('createMaintenanceForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // 驗證表單
        if (!validateForm()) {
            return;
        }
        
        // 顯示載入狀態
        showLoading();
        
        // 收集表單數據
        const formData = new FormData(this);
        formData.append('action', 'mss_create_maintenance_order');
        formData.append('nonce', mss_frontend.nonce);
        
        // 提交到服務器
        jQuery.ajax({
            url: mss_frontend.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    showSuccessModal(response.data);
                } else {
                    showError(response.data.message || '建立失敗，請稍後再試');
                }
            },
            error: function() {
                hideLoading();
                showError('網路錯誤，請檢查連接後再試');
            }
        });
    });

    // 表單驗證
    function validateForm() {
        const requiredFields = [
            'form_number',
            'date', 
            'location',
            'constructor_name',
            'problem_description'
        ];
        
        let isValid = true;
        let firstError = null;
        
        requiredFields.forEach(function(fieldName) {
            const field = document.querySelector(`[name="${fieldName}"]`);
            const value = field.value.trim();
            
            if (!value) {
                field.classList.add('error');
                isValid = false;
                
                if (!firstError) {
                    firstError = field;
                }
            } else {
                field.classList.remove('error');
            }
        });
        
        if (!isValid) {
            showError('請填寫所有必填欄位');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
        
        return isValid;
    }

    // 顯示成功模態框
    function showSuccessModal(data) {
        document.getElementById('successFormNumber').textContent = data.form_number || '未知';
        document.getElementById('customerLink').value = data.customer_link || '';
        
        // 生成QR碼（這裡可以整合QR碼庫）
        // generateQRCode(data.customer_link);
        
        document.getElementById('successModal').style.display = 'block';
    }

    // 複製連結
    function copyLink() {
        const linkInput = document.getElementById('customerLink');
        linkInput.select();
        linkInput.setSelectionRange(0, 99999);
        
        navigator.clipboard.writeText(linkInput.value).then(function() {
            showSuccess('客戶簽名連結已複製到剪貼板！');
        }).catch(function() {
            // 備用方法
            document.execCommand('copy');
            showSuccess('客戶簽名連結已複製到剪貼板！');
        });
    }

    // 分享連結
    function shareLink() {
        const link = document.getElementById('customerLink').value;
        const formNumber = document.getElementById('successFormNumber').textContent;
        
        if (navigator.share) {
            navigator.share({
                title: `維修單 ${formNumber} - 客戶簽名`,
                text: '請點擊連結查看維修單詳情並完成簽名',
                url: link
            }).catch(console.error);
        } else {
            copyLink();
        }
    }

    // 建立下一個維修單
    function createAnother() {
        document.getElementById('successModal').style.display = 'none';
        
        // 重置表單
        document.getElementById('createMaintenanceForm').reset();
        document.getElementById('imagePreview').innerHTML = '';
        
        // 重新設定日期和生成號碼
        document.getElementById('date').value = new Date().toISOString().split('T')[0];
        generateFormNumber();
        
        // 滾動到頂部
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        showSuccess('可以建立下一個維修單了！');
    }

    // 選單切換
    function toggleMenu() {
        const links = document.getElementById('navbarLinks');
        links.classList.toggle('active');
    }

    // 工具函數
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').textContent = '建立中...';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('submitBtn').disabled = false;
        document.getElementById('submitBtn').textContent = '✅ 建立維修單';
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

    // 移除錯誤樣式
    document.querySelectorAll('input, select, textarea').forEach(function(field) {
        field.addEventListener('input', function() {
            this.classList.remove('error');
        });
    });
    </script>
</body>
</html>