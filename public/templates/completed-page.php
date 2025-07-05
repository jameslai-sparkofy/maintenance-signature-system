<?php
/**
 * 維修單完成報告頁面模板
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

global $mss_current_maintenance_order, $mss_current_signature;

if (!$mss_current_maintenance_order || !$mss_current_signature) {
    wp_die('維修單資料無法載入或尚未完成簽名。');
}

$maintenance_order = $mss_current_maintenance_order;
$signature = $mss_current_signature;
$media_files = MSS_Customer_Portal::get_maintenance_media($maintenance_order->id);

// 設定頁面標題
$page_title = sprintf('維修單完成報告 - %s', $maintenance_order->form_number);

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
<body class="mss-completed-page">
    <div class="mss-container">
        <!-- 完成狀態標題 -->
        <div class="mss-completion-summary">
            <div class="mss-completion-icon">✅</div>
            <h1>維修單已完成</h1>
            <p>感謝您的配合，維修單已順利完成簽名確認</p>
            <div class="mss-completion-details">
                <strong>維修單號碼：<?php echo esc_html($maintenance_order->form_number); ?></strong><br>
                <span>完成時間：<?php echo esc_html(MSS_Customer_Portal::format_datetime($signature->signed_at)); ?></span>
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
                    <label>維修相關圖片</label>
                    <div class="mss-media-gallery">
                        <?php foreach ($media_files as $media): ?>
                            <div class="mss-media-item">
                                <img src="<?php echo esc_url($media->file_path); ?>" 
                                     alt="<?php echo esc_attr($media->file_name); ?>"
                                     onclick="mssOpenImageModal(this)">
                                <div class="mss-media-caption"><?php echo esc_html($media->file_name); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- 客戶簽名資訊 -->
        <div class="mss-customer-signature">
            <h2>客戶簽名確認</h2>
            
            <div class="mss-signature-info-grid">
                <div class="mss-signature-details">
                    <div class="mss-detail-item">
                        <label>客戶姓名</label>
                        <div class="mss-detail-value"><?php echo esc_html($signature->customer_name); ?></div>
                    </div>
                    
                    <?php if ($signature->customer_phone): ?>
                    <div class="mss-detail-item">
                        <label>聯絡電話</label>
                        <div class="mss-detail-value"><?php echo esc_html($signature->customer_phone); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($signature->customer_email): ?>
                    <div class="mss-detail-item">
                        <label>電子郵件</label>
                        <div class="mss-detail-value"><?php echo esc_html($signature->customer_email); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mss-detail-item">
                        <label>簽名時間</label>
                        <div class="mss-detail-value"><?php echo esc_html(MSS_Customer_Portal::format_datetime($signature->signed_at)); ?></div>
                    </div>
                </div>
                
                <div class="mss-signature-display">
                    <label>客戶簽名</label>
                    <div class="mss-signature-canvas">
                        <img src="<?php echo esc_url($signature->signature_data); ?>" 
                             alt="客戶簽名" 
                             class="mss-signature-image">
                    </div>
                </div>
            </div>
            
            <!-- 滿意度評分 -->
            <div class="mss-satisfaction-section">
                <h3>服務滿意度評分</h3>
                <div class="mss-rating-display">
                    <div class="mss-stars">
                        <?php echo MSS_Customer_Portal::get_star_rating_html($signature->satisfaction_rating); ?>
                    </div>
                    <div class="mss-rating-text">
                        <?php 
                        $rating_texts = array(
                            1 => '非常不滿意',
                            2 => '不滿意', 
                            3 => '普通',
                            4 => '滿意',
                            5 => '非常滿意'
                        );
                        echo esc_html($rating_texts[$signature->satisfaction_rating] ?? '未評分');
                        ?>
                        (<?php echo $signature->satisfaction_rating; ?>/5)
                    </div>
                </div>
                
                <?php if ($signature->feedback): ?>
                <div class="mss-feedback-section">
                    <h4>客戶意見回饋</h4>
                    <div class="mss-feedback-content">
                        <?php echo nl2br(esc_html($signature->feedback)); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 操作區域 -->
        <div class="mss-actions-section">
            <h3>報告操作</h3>
            <div class="mss-action-buttons">
                <button onclick="window.print()" class="mss-button mss-button-primary">
                    🖨️ 列印報告
                </button>
                <button onclick="mssShareReport()" class="mss-button mss-button-secondary">
                    📤 分享報告
                </button>
                <button onclick="mssDownloadPDF()" class="mss-button mss-button-secondary">
                    📄 下載 PDF
                </button>
            </div>
            
            <div class="mss-completion-note">
                <p><strong>報告說明：</strong></p>
                <ul>
                    <li>此報告包含完整的維修單資訊和客戶簽名確認</li>
                    <li>報告具有法律效力，請妥善保存</li>
                    <li>如有任何問題，請聯繫維修服務提供方</li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- 圖片模態框 -->
    <div id="mss-image-modal" class="mss-modal">
        <div class="mss-modal-content">
            <span class="mss-modal-close">&times;</span>
            <img id="mss-modal-image" src="" alt="">
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
    
    // 分享報告
    function mssShareReport() {
        if (navigator.share) {
            navigator.share({
                title: '維修單完成報告 - <?php echo esc_js($maintenance_order->form_number); ?>',
                text: '維修單已完成，請查看詳細報告',
                url: window.location.href
            }).catch(console.error);
        } else {
            // 備用：複製連結
            navigator.clipboard.writeText(window.location.href).then(function() {
                alert('報告連結已複製到剪貼板！');
            }).catch(function() {
                alert('請手動複製此頁面連結分享。');
            });
        }
    }
    
    // 下載 PDF (placeholder)
    function mssDownloadPDF() {
        alert('PDF 下載功能正在開發中，目前請使用列印功能。');
    }
    
    // 列印前隱藏不需要的元素
    window.addEventListener('beforeprint', function() {
        document.querySelectorAll('.mss-actions-section').forEach(function(el) {
            el.style.display = 'none';
        });
    });
    
    window.addEventListener('afterprint', function() {
        document.querySelectorAll('.mss-actions-section').forEach(function(el) {
            el.style.display = 'block';
        });
    });
    </script>
</body>
</html>