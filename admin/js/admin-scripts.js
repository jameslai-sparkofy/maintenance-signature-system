/**
 * 維修單管理系統 - 管理後台 JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // 初始化管理後台功能
        initAdminFeatures();
    });
    
    /**
     * 初始化管理後台功能
     */
    function initAdminFeatures() {
        // 初始化表單驗證
        initFormValidation();
        
        // 初始化確認對話框
        initConfirmDialogs();
        
        // 初始化工具提示
        initTooltips();
    }
    
    /**
     * 表單驗證
     */
    function initFormValidation() {
        // 新增維修單表單驗證
        $('#maintenance-form').on('submit', function(e) {
            var isValid = true;
            var errors = [];
            
            // 檢查必填欄位
            $(this).find('input[required], select[required], textarea[required]').each(function() {
                var $field = $(this);
                var value = $field.val().trim();
                
                if (!value) {
                    isValid = false;
                    $field.addClass('error');
                    errors.push($field.prev('label').text() + ' 為必填欄位');
                } else {
                    $field.removeClass('error');
                }
            });
            
            // 顯示錯誤訊息
            if (!isValid) {
                e.preventDefault();
                alert('請填寫以下必填欄位:\n' + errors.join('\n'));
                return false;
            }
            
            return true;
        });
        
        // 移除錯誤樣式當用戶開始輸入
        $('input, select, textarea').on('input change', function() {
            $(this).removeClass('error');
        });
    }
    
    /**
     * 確認對話框
     */
    function initConfirmDialogs() {
        // 刪除確認
        $('.delete-btn, .submitdelete').on('click', function(e) {
            if (!confirm('確定要刪除嗎？此操作無法復原。')) {
                e.preventDefault();
                return false;
            }
        });
        
        // 批量刪除確認
        $('.bulkactions input[type="submit"]').on('click', function(e) {
            var action = $(this).siblings('select').val();
            if (action === 'bulk_delete') {
                var checkedItems = $('input[name="maintenance_ids[]"]:checked').length;
                if (checkedItems === 0) {
                    e.preventDefault();
                    alert('請選擇要刪除的項目。');
                    return false;
                }
                
                if (!confirm('確定要刪除選定的 ' + checkedItems + ' 個項目嗎？此操作無法復原。')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
    
    /**
     * 工具提示
     */
    function initTooltips() {
        // 為有 title 屬性的元素添加工具提示
        $('[title]').each(function() {
            var $this = $(this);
            var title = $this.attr('title');
            
            if (title) {
                $this.removeAttr('title').attr('data-tooltip', title);
                
                $this.hover(
                    function() {
                        showTooltip($(this), title);
                    },
                    function() {
                        hideTooltip();
                    }
                );
            }
        });
    }
    
    /**
     * 顯示工具提示
     */
    function showTooltip($element, text) {
        var $tooltip = $('<div class="mss-tooltip">' + text + '</div>');
        $('body').append($tooltip);
        
        var offset = $element.offset();
        $tooltip.css({
            position: 'absolute',
            top: offset.top - $tooltip.outerHeight() - 5,
            left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2),
            zIndex: 9999
        });
        
        $tooltip.fadeIn(200);
    }
    
    /**
     * 隱藏工具提示
     */
    function hideTooltip() {
        $('.mss-tooltip').fadeOut(200, function() {
            $(this).remove();
        });
    }
    
    /**
     * 複製到剪貼板
     */
    function copyToClipboard(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function() {
                showNotification('已複製到剪貼板！', 'success');
            }).catch(function() {
                fallbackCopyToClipboard(text);
            });
        } else {
            fallbackCopyToClipboard(text);
        }
    }
    
    /**
     * 備用複製方法
     */
    function fallbackCopyToClipboard(text) {
        var textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.top = '0';
        textArea.style.left = '0';
        textArea.style.width = '2em';
        textArea.style.height = '2em';
        textArea.style.padding = '0';
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';
        textArea.style.background = 'transparent';
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showNotification('已複製到剪貼板！', 'success');
        } catch (err) {
            showNotification('複製失敗，請手動複製。', 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
    /**
     * 顯示通知
     */
    function showNotification(message, type) {
        var $notification = $('<div class="mss-notification mss-notification-' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        $notification.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '10px 20px',
            borderRadius: '4px',
            zIndex: 99999,
            fontSize: '14px',
            fontWeight: 'bold'
        });
        
        if (type === 'success') {
            $notification.css({
                backgroundColor: '#46b450',
                color: '#fff'
            });
        } else if (type === 'error') {
            $notification.css({
                backgroundColor: '#dc3232',
                color: '#fff'
            });
        }
        
        $notification.fadeIn(300);
        
        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // 全域函數，供其他腳本使用
    window.mssCopyToClipboard = copyToClipboard;
    window.mssShowNotification = showNotification;
    
})(jQuery);