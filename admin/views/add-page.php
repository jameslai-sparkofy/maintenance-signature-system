<?php
/**
 * 新增/編輯維修單頁面
 */

// 防止直接訪問
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']);
$maintenance_order = null;

if ($is_edit) {
    $maintenance_id = intval($_GET['id']);
    $maintenance_order = MSS_Database::get_maintenance_order($maintenance_id);
    
    if (!$maintenance_order) {
        wp_die('維修單不存在。');
    }
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_maintenance_order'])) {
    // 驗證 nonce
    if (!wp_verify_nonce($_POST['mss_nonce'], 'mss_add_maintenance_order')) {
        wp_die('安全檢查失敗。');
    }
    
    // 收集表單數據
    $form_data = array(
        'form_number' => sanitize_text_field($_POST['form_number']),
        'date' => sanitize_text_field($_POST['date']),
        'location' => sanitize_text_field($_POST['location']),
        'constructor_name' => sanitize_text_field($_POST['constructor_name']),
        'building' => sanitize_text_field($_POST['building']),
        'floor' => sanitize_text_field($_POST['floor']),
        'unit' => sanitize_text_field($_POST['unit']),
        'problem_description' => sanitize_textarea_field($_POST['problem_description'])
    );
    
    // 驗證必填欄位
    $required_fields = array('form_number', 'date', 'location', 'constructor_name', 'problem_description');
    $errors = array();
    
    foreach ($required_fields as $field) {
        if (empty($form_data[$field])) {
            $errors[] = sprintf('「%s」為必填欄位。', $field);
        }
    }
    
    if (empty($errors)) {
        if ($is_edit) {
            // 更新維修單
            $result = MSS_Database::update_maintenance_order($maintenance_id, $form_data);
        } else {
            // 創建新維修單
            $result = MSS_Database::create_maintenance_order($form_data);
        }
        
        if ($result) {
            $maintenance_id = $is_edit ? $maintenance_id : $result;
            
            // 處理文件上傳
            if (!empty($_FILES['maintenance_images']['name'][0])) {
                $uploaded_files = $_FILES['maintenance_images'];
                $upload_dir = wp_upload_dir();
                
                for ($i = 0; $i < count($uploaded_files['name']); $i++) {
                    if ($uploaded_files['error'][$i] === UPLOAD_ERR_OK) {
                        $file_info = array(
                            'name' => $uploaded_files['name'][$i],
                            'type' => $uploaded_files['type'][$i],
                            'tmp_name' => $uploaded_files['tmp_name'][$i],
                            'error' => $uploaded_files['error'][$i],
                            'size' => $uploaded_files['size'][$i]
                        );
                        
                        $uploaded_file = wp_handle_upload($file_info, array('test_form' => false));
                        
                        if (!isset($uploaded_file['error'])) {
                            MSS_Database::save_media_file(array(
                                'maintenance_id' => $maintenance_id,
                                'file_name' => basename($uploaded_file['file']),
                                'file_path' => $uploaded_file['url'],
                                'file_type' => $uploaded_file['type'],
                                'file_size' => $uploaded_files['size'][$i]
                            ));
                        }
                    }
                }
            }
            
            $message = $is_edit ? '維修單更新成功！' : '維修單建立成功！';
            echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
            
            if (!$is_edit) {
                // 重新導向到編輯頁面
                echo '<script>window.location.href = "' . admin_url('admin.php?page=maintenance-signature-system-add&action=edit&id=' . $maintenance_id) . '";</script>';
            }
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>操作失敗，請稍後再試。</p></div>';
        }
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . implode('<br>', $errors) . '</p></div>';
    }
}

// 獲取工務姓名列表
$constructors = MSS_Database::get_constructors();

// 自動生成維修單號碼（僅限新增）
if (!$is_edit) {
    $auto_form_number = 'MF' . date('YmdHi') . rand(10, 99);
} else {
    $auto_form_number = $maintenance_order->form_number;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? '編輯維修單' : '新增維修單'; ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <div id="col-container" class="wp-clearfix">
        <div id="col-left">
            <div class="col-wrap">
                <div class="form-wrap">
                    <form method="post" enctype="multipart/form-data" id="maintenance-form">
                        <?php wp_nonce_field('mss_add_maintenance_order', 'mss_nonce'); ?>
                        
                        <!-- 基本資訊 -->
                        <div class="mss-form-section">
                            <h3>基本資訊</h3>
                            
                            <div class="mss-form-row">
                                <div class="mss-form-field">
                                    <label for="form_number">維修單號碼 <span class="required">*</span></label>
                                    <input type="text" id="form_number" name="form_number" 
                                           value="<?php echo esc_attr($maintenance_order ? $maintenance_order->form_number : $auto_form_number); ?>" 
                                           required>
                                    <p class="description">維修單的唯一識別號碼</p>
                                </div>
                                
                                <div class="mss-form-field">
                                    <label for="date">維修日期 <span class="required">*</span></label>
                                    <input type="date" id="date" name="date" 
                                           value="<?php echo esc_attr($maintenance_order ? $maintenance_order->date : date('Y-m-d')); ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="mss-form-row">
                                <div class="mss-form-field">
                                    <label for="location">維修案場 <span class="required">*</span></label>
                                    <input type="text" id="location" name="location" 
                                           value="<?php echo esc_attr($maintenance_order ? $maintenance_order->location : ''); ?>" 
                                           required>
                                    <p class="description">維修的案場或項目名稱</p>
                                </div>
                                
                                <div class="mss-form-field">
                                    <label for="constructor_name">工務姓名 <span class="required">*</span></label>
                                    <select id="constructor_name" name="constructor_name" required>
                                        <option value="">請選擇工務姓名</option>
                                        <?php foreach ($constructors as $constructor): ?>
                                            <option value="<?php echo esc_attr($constructor->name); ?>" 
                                                    <?php selected($maintenance_order ? $maintenance_order->constructor_name : '', $constructor->name); ?>>
                                                <?php echo esc_html($constructor->name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">
                                        <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system-settings'); ?>">管理工務姓名</a>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mss-form-row">
                                <div class="mss-form-field">
                                    <label for="building">維修棟別</label>
                                    <input type="text" id="building" name="building" 
                                           value="<?php echo esc_attr($maintenance_order ? $maintenance_order->building : ''); ?>" 
                                           placeholder="例：A棟、B棟">
                                </div>
                                
                                <div class="mss-form-field">
                                    <label for="floor">樓層</label>
                                    <input type="text" id="floor" name="floor" 
                                           value="<?php echo esc_attr($maintenance_order ? $maintenance_order->floor : ''); ?>" 
                                           placeholder="例：1F、2F、B1">
                                </div>
                            </div>
                            
                            <div class="mss-form-row">
                                <div class="mss-form-field">
                                    <label for="unit">戶別</label>
                                    <input type="text" id="unit" name="unit" 
                                           value="<?php echo esc_attr($maintenance_order ? $maintenance_order->unit : ''); ?>" 
                                           placeholder="例：101、102">
                                </div>
                            </div>
                        </div>
                        
                        <!-- 問題描述 -->
                        <div class="mss-form-section">
                            <h3>問題描述</h3>
                            
                            <div class="mss-form-field">
                                <label for="problem_description">詳細問題描述 <span class="required">*</span></label>
                                <textarea id="problem_description" name="problem_description" rows="6" required><?php echo esc_textarea($maintenance_order ? $maintenance_order->problem_description : ''); ?></textarea>
                                <p class="description">請詳細描述維修問題的狀況</p>
                            </div>
                        </div>
                        
                        <!-- 圖片上傳 -->
                        <div class="mss-form-section">
                            <h3>相關圖片</h3>
                            
                            <div class="mss-form-field">
                                <label for="maintenance_images">上傳圖片</label>
                                <input type="file" id="maintenance_images" name="maintenance_images[]" 
                                       accept="image/*" multiple>
                                <p class="description">可上傳多張圖片，支援 JPG、PNG、GIF 格式</p>
                                
                                <div id="image-preview" class="mss-image-preview"></div>
                                
                                <?php if ($is_edit): ?>
                                    <!-- 顯示已上傳的圖片 -->
                                    <div class="mss-uploaded-images">
                                        <h4>已上傳的圖片</h4>
                                        <?php
                                        $media_files = MSS_Database::get_media_files_by_maintenance_id($maintenance_id);
                                        if ($media_files):
                                        ?>
                                            <div class="mss-media-grid">
                                                <?php foreach ($media_files as $media): ?>
                                                    <div class="mss-media-item">
                                                        <img src="<?php echo esc_url($media->file_path); ?>" 
                                                             alt="<?php echo esc_attr($media->file_name); ?>">
                                                        <div class="mss-media-actions">
                                                            <button type="button" class="button button-small mss-delete-media" 
                                                                    data-media-id="<?php echo $media->id; ?>">刪除</button>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <p>尚未上傳任何圖片</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- 提交按鈕 -->
                        <div class="mss-form-actions">
                            <input type="submit" name="submit_maintenance_order" 
                                   class="button button-primary" 
                                   value="<?php echo $is_edit ? '更新維修單' : '建立維修單'; ?>">
                            
                            <a href="<?php echo admin_url('admin.php?page=maintenance-signature-system'); ?>" 
                               class="button">取消</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div id="col-right">
            <div class="col-wrap">
                <?php if ($is_edit): ?>
                    <!-- 維修單資訊 -->
                    <div class="mss-info-box">
                        <h3>維修單資訊</h3>
                        <table class="mss-info-table">
                            <tr>
                                <td><strong>維修單ID:</strong></td>
                                <td><?php echo $maintenance_order->id; ?></td>
                            </tr>
                            <tr>
                                <td><strong>建立時間:</strong></td>
                                <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($maintenance_order->created_at)); ?></td>
                            </tr>
                            <tr>
                                <td><strong>狀態:</strong></td>
                                <td>
                                    <?php if ($maintenance_order->status === 'pending_customer_signature'): ?>
                                        <span class="mss-status-pending">待客戶簽名</span>
                                    <?php elseif ($maintenance_order->status === 'completed'): ?>
                                        <span class="mss-status-completed">已完成</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- 客戶簽名連結 -->
                    <div class="mss-info-box">
                        <h3>客戶簽名</h3>
                        <p>客戶簽名連結：</p>
                        <div class="mss-link-box">
                            <input type="text" id="customer-signature-link" 
                                   value="<?php echo esc_url(home_url('/?maintenance_signature=' . $maintenance_order->id)); ?>" 
                                   readonly>
                            <button type="button" class="button" id="copy-link-btn">複製連結</button>
                        </div>
                        <p class="description">將此連結傳送給客戶進行簽名確認</p>
                    </div>
                    
                    <?php if ($maintenance_order->status === 'completed'): ?>
                        <!-- 客戶簽名資訊 -->
                        <?php
                        $signature = MSS_Database::get_signature_by_maintenance_id($maintenance_id);
                        if ($signature):
                        ?>
                            <div class="mss-info-box">
                                <h3>客戶簽名資訊</h3>
                                <table class="mss-info-table">
                                    <tr>
                                        <td><strong>客戶姓名:</strong></td>
                                        <td><?php echo esc_html($signature->customer_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>聯絡電話:</strong></td>
                                        <td><?php echo esc_html($signature->customer_phone); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>電子郵件:</strong></td>
                                        <td><?php echo esc_html($signature->customer_email); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>滿意度評分:</strong></td>
                                        <td><?php echo str_repeat('★', $signature->satisfaction_rating) . str_repeat('☆', 5 - $signature->satisfaction_rating); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>簽名時間:</strong></td>
                                        <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($signature->signed_at)); ?></td>
                                    </tr>
                                </table>
                                
                                <?php if ($signature->feedback): ?>
                                    <h4>客戶意見回饋</h4>
                                    <p><?php echo esc_html($signature->feedback); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- 建立維修單說明 -->
                    <div class="mss-info-box">
                        <h3>使用說明</h3>
                        <ol>
                            <li>填寫基本資訊，所有標示 * 的欄位都是必填的</li>
                            <li>詳細描述維修問題狀況</li>
                            <li>上傳相關圖片（可選）</li>
                            <li>點擊「建立維修單」完成</li>
                            <li>建立後系統會自動生成客戶簽名連結</li>
                        </ol>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 圖片預覽
    $('#maintenance_images').on('change', function(e) {
        var files = e.target.files;
        var preview = $('#image-preview');
        preview.empty();
        
        if (files.length > 0) {
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var img = $('<div class="mss-preview-item"><img src="' + e.target.result + '" alt="預覽圖片"><div class="mss-preview-name">' + file.name + '</div></div>');
                        preview.append(img);
                    };
                    reader.readAsDataURL(file);
                }
            }
        }
    });
    
    // 複製連結
    $('#copy-link-btn').on('click', function() {
        var linkInput = $('#customer-signature-link');
        linkInput.select();
        document.execCommand('copy');
        
        $(this).text('已複製！').prop('disabled', true);
        setTimeout(function() {
            $('#copy-link-btn').text('複製連結').prop('disabled', false);
        }, 2000);
    });
    
    // 刪除媒體文件
    $('.mss-delete-media').on('click', function() {
        if (confirm('確定要刪除這張圖片嗎？')) {
            var mediaId = $(this).data('media-id');
            var mediaItem = $(this).closest('.mss-media-item');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'mss_delete_media',
                    media_id: mediaId,
                    nonce: '<?php echo wp_create_nonce('mss_delete_media'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        mediaItem.fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert('刪除失敗：' + response.data);
                    }
                },
                error: function() {
                    alert('刪除失敗，請稍後再試。');
                }
            });
        }
    });
});
</script>