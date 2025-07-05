<?php
/**
 * 客戶簽名頁面模板
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

global $mss_current_maintenance_order;

if (!$mss_current_maintenance_order) {
    wp_die('維修單資料無法載入。');
}

$maintenance_order = $mss_current_maintenance_order;
$media_files = MSS_Customer_Portal::get_maintenance_media($maintenance_order->id);

// 設定頁面標題
$page_title = sprintf('維修單簽名確認 - %s', $maintenance_order->form_number);

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
<body class="mss-signature-page">
    <div class="mss-container">
        <div class="mss-header">
            <h1>維修單客戶簽名確認</h1>
            <div class="mss-form-number">
                維修單號碼：<strong><?php echo esc_html($maintenance_order->form_number); ?></strong>
            </div>
        </div>
        
        <!-- 維修單詳情 -->
        <div class="mss-maintenance-details">
            <h2>維修單詳情</h2>
            
            <div class="mss-details-grid">
                <div class="mss-detail-item">
                    <label>維修日期</label>
                    <div class="mss-detail-value"><?php echo esc_html(MSS_Customer_Portal::format_datetime($maintenance_order->date, get_option('date_format'))); ?></div>
                </div>
                
                <div class="mss-detail-item">
                    <label>維修案場</label>
                    <div class="mss-detail-value"><?php echo esc_html($maintenance_order->location); ?></div>
                </div>
                
                <div class="mss-detail-item">
                    <label>工務姓名</label>
                    <div class="mss-detail-value"><?php echo esc_html($maintenance_order->constructor_name); ?></div>
                </div>
                
                <div class="mss-detail-item">
                    <label>維修地址</label>
                    <div class="mss-detail-value">
                        <?php 
                        $address_parts = array_filter(array(
                            $maintenance_order->building,
                            $maintenance_order->floor,
                            $maintenance_order->unit
                        ));
                        echo esc_html(implode(' ', $address_parts)) ?: '未提供';
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="mss-detail-item mss-detail-full">
                <label>問題描述</label>
                <div class="mss-detail-value mss-problem-description">
                    <?php echo nl2br(esc_html($maintenance_order->problem_description)); ?>
                </div>
            </div>
            
            <?php if (!empty($media_files)): ?>
                <div class="mss-detail-item mss-detail-full">
                    <label>相關圖片</label>
                    <div class="mss-media-gallery">
                        <?php foreach ($media_files as $media): ?>
                            <div class="mss-media-item">
                                <img src="<?php echo esc_url($media->file_path); ?>" 
                                     alt="<?php echo esc_attr($media->file_name); ?>"
                                     onclick="mssOpenImageModal(this)">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 客戶簽名區域 -->
        <div class="mss-signature-section">
            <h2>客戶簽名確認</h2>
            <p class="mss-signature-instruction">
                請您仔細檢查以上維修單內容，確認無誤後請填寫以下資訊並完成簽名：
            </p>
            
            <form id="mss-signature-form" class="mss-signature-form">
                <input type="hidden" name="maintenance_id" value="<?php echo esc_attr($maintenance_order->id); ?>">
                
                <!-- 客戶資訊 -->
                <div class="mss-form-section">
                    <h3>客戶資訊</h3>
                    
                    <div class="mss-form-row">
                        <div class="mss-form-field">
                            <label for="customer_name">客戶姓名 <span class="required">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" required>
                        </div>
                        
                        <div class="mss-form-field">
                            <label for="customer_phone">聯絡電話</label>
                            <input type="tel" id="customer_phone" name="customer_phone">
                        </div>
                    </div>
                    
                    <div class="mss-form-row">
                        <div class="mss-form-field">
                            <label for="customer_email">電子郵件</label>
                            <input type="email" id="customer_email" name="customer_email">
                        </div>
                    </div>
                </div>
                
                <!-- 簽名區域 -->
                <div class="mss-form-section">
                    <h3>電子簽名 <span class="required">*</span></h3>
                    <p class="mss-signature-note">請在下方區域內簽名：</p>
                    
                    <div class="mss-signature-container">
                        <canvas id="mss-signature-canvas" width="600" height="200"></canvas>
                        <div class="mss-signature-controls">
                            <button type="button" id="mss-clear-signature" class="mss-button mss-button-secondary">
                                清除簽名
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- 滿意度評分 -->
                <div class="mss-form-section">
                    <h3>服務滿意度評分</h3>
                    
                    <div class="mss-rating-container">
                        <label>請為本次維修服務評分：</label>
                        <div class="mss-star-rating" id="mss-star-rating">
                            <span class="mss-star" data-rating="1">★</span>
                            <span class="mss-star" data-rating="2">★</span>
                            <span class="mss-star" data-rating="3">★</span>
                            <span class="mss-star" data-rating="4">★</span>
                            <span class="mss-star" data-rating="5">★</span>
                        </div>
                        <input type="hidden" id="satisfaction_rating" name="satisfaction_rating" value="0">
                        <div class="mss-rating-text" id="mss-rating-text">請點擊星星進行評分</div>
                    </div>
                    
                    <div class="mss-form-field">
                        <label for="feedback">意見回饋（選填）</label>
                        <textarea id="feedback" name="feedback" rows="4" 
                                  placeholder="歡迎提供您對本次維修服務的意見或建議..."></textarea>
                    </div>
                </div>
                
                <!-- 提交按鈕 -->
                <div class="mss-form-actions">
                    <button type="submit" id="mss-submit-signature" class="mss-button mss-button-primary">
                        確認簽名並完成
                    </button>
                    <div class="mss-submit-note">
                        <small>提交後將無法修改，請確認所有資訊正確無誤</small>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- 圖片模態框 -->
    <div id="mss-image-modal" class="mss-modal">
        <div class="mss-modal-content">
            <span class="mss-modal-close">&times;</span>
            <img id="mss-modal-image" src="" alt="">
        </div>
    </div>
    
    <!-- 載入提示 -->
    <div id="mss-loading-overlay" class="mss-loading-overlay" style="display: none;">
        <div class="mss-loading-spinner">
            <div class="mss-spinner"></div>
            <div class="mss-loading-text">處理中，請稍候...</div>
        </div>
    </div>
    
    <?php wp_footer(); ?>
    
    <script>
    // 圖片模態框功能
    function mssOpenImageModal(img) {
        const modal = document.getElementById('mss-image-modal');
        const modalImg = document.getElementById('mss-modal-image');
        
        modal.style.display = 'block';
        modalImg.src = img.src;
        modalImg.alt = img.alt;
    }
    
    // 關閉模態框
    document.querySelector('.mss-modal-close').onclick = function() {
        document.getElementById('mss-image-modal').style.display = 'none';
    }
    
    // 點擊模態框外部關閉
    window.onclick = function(event) {
        const modal = document.getElementById('mss-image-modal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
    
    // 星級評分功能
    jQuery(document).ready(function($) {
        const stars = $('.mss-star');
        const ratingInput = $('#satisfaction_rating');
        const ratingText = $('#mss-rating-text');
        
        const ratingTexts = {
            1: '非常不滿意',
            2: '不滿意',
            3: '普通',
            4: '滿意',
            5: '非常滿意'
        };
        
        stars.on('click', function() {
            const rating = $(this).data('rating');
            ratingInput.val(rating);
            
            // 更新星星顯示
            stars.each(function(index) {
                if (index < rating) {
                    $(this).addClass('mss-star-active');
                } else {
                    $(this).removeClass('mss-star-active');
                }
            });
            
            // 更新評分文字
            ratingText.text(ratingTexts[rating]);
        });
        
        // 星星懸停效果
        stars.on('mouseenter', function() {
            const rating = $(this).data('rating');
            
            stars.each(function(index) {
                if (index < rating) {
                    $(this).addClass('mss-star-hover');
                } else {
                    $(this).removeClass('mss-star-hover');
                }
            });
        });
        
        stars.on('mouseleave', function() {
            stars.removeClass('mss-star-hover');
        });
    });
    </script>
</body>
</html>