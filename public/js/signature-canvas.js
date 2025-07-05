/**
 * 維修單簽名畫布功能
 */

(function($) {
    'use strict';
    
    let isDrawing = false;
    let hasSignature = false;
    let canvas, ctx;
    
    $(document).ready(function() {
        initSignatureCanvas();
        initForm();
    });
    
    /**
     * 初始化簽名畫布
     */
    function initSignatureCanvas() {
        canvas = document.getElementById('mss-signature-canvas');
        if (!canvas) return;
        
        ctx = canvas.getContext('2d');
        
        // 設定畫布樣式
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        // 設定畫布背景為白色
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // 繪製簽名提示線
        drawSignatureLine();
        
        // 鼠標事件
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // 觸控設備事件
        canvas.addEventListener('touchstart', handleTouch);
        canvas.addEventListener('touchmove', handleTouch);
        canvas.addEventListener('touchend', stopDrawing);
        
        // 防止頁面滾動
        canvas.addEventListener('touchstart', preventDefault);
        canvas.addEventListener('touchmove', preventDefault);
        
        // 清除簽名按鈕
        $('#mss-clear-signature').on('click', clearSignature);
    }
    
    /**
     * 繪製簽名提示線
     */
    function drawSignatureLine() {
        ctx.save();
        ctx.strokeStyle = '#e0e0e0';
        ctx.lineWidth = 1;
        ctx.setLineDash([5, 5]);
        
        const y = canvas.height - 30;
        ctx.beginPath();
        ctx.moveTo(50, y);
        ctx.lineTo(canvas.width - 50, y);
        ctx.stroke();
        
        // 添加提示文字
        ctx.font = '14px Arial';
        ctx.fillStyle = '#999999';
        ctx.textAlign = 'center';
        ctx.fillText('請在此處簽名', canvas.width / 2, y + 20);
        
        ctx.restore();
    }
    
    /**
     * 開始繪製
     */
    function startDrawing(e) {
        isDrawing = true;
        hasSignature = true;
        
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ctx.beginPath();
        ctx.moveTo(x, y);
    }
    
    /**
     * 繪製
     */
    function draw(e) {
        if (!isDrawing) return;
        
        const rect = canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        ctx.lineTo(x, y);
        ctx.stroke();
    }
    
    /**
     * 停止繪製
     */
    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            ctx.beginPath();
        }
    }
    
    /**
     * 處理觸控事件
     */
    function handleTouch(e) {
        e.preventDefault();
        
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent(
            e.type === 'touchstart' ? 'mousedown' :
            e.type === 'touchmove' ? 'mousemove' :
            'mouseup',
            {
                clientX: touch.clientX,
                clientY: touch.clientY
            }
        );
        
        canvas.dispatchEvent(mouseEvent);
    }
    
    /**
     * 防止默認行為
     */
    function preventDefault(e) {
        e.preventDefault();
    }
    
    /**
     * 清除簽名
     */
    function clearSignature() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // 重新設定背景
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        // 重新繪製提示線
        drawSignatureLine();
        
        hasSignature = false;
    }
    
    /**
     * 獲取簽名數據
     */
    function getSignatureData() {
        if (!hasSignature) {
            return null;
        }
        
        return canvas.toDataURL('image/png');
    }
    
    /**
     * 初始化表單
     */
    function initForm() {
        $('#mss-signature-form').on('submit', function(e) {
            e.preventDefault();
            submitSignature();
        });
    }
    
    /**
     * 提交簽名
     */
    function submitSignature() {
        // 驗證必填欄位
        const customerName = $('#customer_name').val().trim();
        if (!customerName) {
            showMessage(mss_ajax.messages.customer_name_required, 'error');
            $('#customer_name').focus();
            return;
        }
        
        // 驗證簽名
        if (!hasSignature) {
            showMessage(mss_ajax.messages.signature_required, 'error');
            return;
        }
        
        // 確認提交
        if (!confirm(mss_ajax.messages.confirm_submit)) {
            return;
        }
        
        // 顯示載入狀態
        showLoading(true);
        
        // 收集表單數據
        const formData = {
            action: 'mss_handle_signature',
            nonce: mss_ajax.nonce,
            maintenance_id: $('input[name="maintenance_id"]').val(),
            customer_name: customerName,
            customer_phone: $('#customer_phone').val().trim(),
            customer_email: $('#customer_email').val().trim(),
            signature_data: getSignatureData(),
            satisfaction_rating: $('#satisfaction_rating').val(),
            feedback: $('#feedback').val().trim()
        };
        
        // 提交到服務器
        $.ajax({
            url: mss_ajax.ajax_url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                showLoading(false);
                
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    
                    // 跳轉到完成頁面
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1500);
                } else {
                    showMessage(response.data.message || mss_ajax.messages.error, 'error');
                }
            },
            error: function(xhr, status, error) {
                showLoading(false);
                showMessage(mss_ajax.messages.error, 'error');
                console.error('AJAX Error:', error);
            }
        });
    }
    
    /**
     * 顯示訊息
     */
    function showMessage(message, type) {
        // 移除現有訊息
        $('.mss-message').remove();
        
        // 創建新訊息
        const messageClass = type === 'error' ? 'mss-message-error' : 'mss-message-success';
        const messageHtml = `
            <div class="mss-message ${messageClass}">
                <p>${message}</p>
                <button type="button" class="mss-message-close">&times;</button>
            </div>
        `;
        
        // 插入到頁面頂部
        $('.mss-container').prepend(messageHtml);
        
        // 自動隱藏成功訊息
        if (type === 'success') {
            setTimeout(function() {
                $('.mss-message').fadeOut();
            }, 3000);
        }
        
        // 關閉按鈕事件
        $('.mss-message-close').on('click', function() {
            $(this).parent().fadeOut();
        });
        
        // 滾動到訊息位置
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    }
    
    /**
     * 顯示/隱藏載入狀態
     */
    function showLoading(show) {
        if (show) {
            $('#mss-loading-overlay').show();
            $('#mss-submit-signature').prop('disabled', true).text('處理中...');
        } else {
            $('#mss-loading-overlay').hide();
            $('#mss-submit-signature').prop('disabled', false).text('確認簽名並完成');
        }
    }
    
    /**
     * 響應式畫布調整
     */
    function resizeCanvas() {
        if (!canvas) return;
        
        const container = canvas.parentElement;
        const containerWidth = container.clientWidth;
        
        if (containerWidth < 600) {
            canvas.width = containerWidth - 20;
            canvas.height = Math.min(200, containerWidth * 0.4);
        } else {
            canvas.width = 600;
            canvas.height = 200;
        }
        
        // 重新初始化畫布
        ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        
        drawSignatureLine();
        hasSignature = false;
    }
    
    // 監聽窗口大小變化
    $(window).on('resize', resizeCanvas);
    
    // 頁面載入完成後調整畫布大小
    $(window).on('load', resizeCanvas);
    
})(jQuery);