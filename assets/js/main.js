// Main JavaScript for Quản lý file XSCTBDVL

$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.confirm-delete').on('click', function(e) {
        if (!confirm('Bạn có chắc chắn muốn xóa?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Add loading spinner to buttons on submit
    $('form').on('submit', function() {
        var submitBtn = $(this).find('button[type="submit"]');
        if (!submitBtn.prop('disabled')) {
            var originalHtml = submitBtn.html();
            submitBtn.prop('disabled', true)
                     .data('original-html', originalHtml)
                     .html('<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...');
        }
    });
    
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // File size validation
    $('input[type="file"]').on('change', function() {
        var file = this.files[0];
        if (file) {
            var maxSize = $(this).data('max-size') || (100 * 1024 * 1024); // Default 100MB
            if (file.size > maxSize) {
                alert('File quá lớn! Kích thước tối đa cho phép: ' + formatBytes(maxSize));
                $(this).val('');
            }
        }
    });
    
    // Format bytes helper
    window.formatBytes = function(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    };
    
    // Search form enhancement
    $('form[action*="search"]').on('submit', function(e) {
        var query = $(this).find('input[name="q"]').val().trim();
        if (query.length < 2) {
            e.preventDefault();
            alert('Vui lòng nhập ít nhất 2 ký tự để tìm kiếm');
            return false;
        }
    });
    
    // Smooth scroll to top
    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            $('#scrollToTop').fadeIn();
        } else {
            $('#scrollToTop').fadeOut();
        }
    });
    
    $('#scrollToTop').click(function() {
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });
    
    // Add scroll to top button if not exists
    if ($('#scrollToTop').length === 0) {
        $('body').append('<a href="#" id="scrollToTop" class="btn btn-primary" style="display:none;position:fixed;bottom:20px;right:20px;z-index:9999;border-radius:50%;width:50px;height:50px;padding:0;"><i class="fas fa-arrow-up"></i></a>');
    }
    
    // Table row click
    $('.table-row-link').on('click', function() {
        window.location = $(this).data('href');
    });
    
    // Copy to clipboard
    $('.copy-to-clipboard').on('click', function(e) {
        e.preventDefault();
        var text = $(this).data('text');
        var input = $('<input>');
        $('body').append(input);
        input.val(text).select();
        document.execCommand('copy');
        input.remove();
        
        // Show feedback
        var originalText = $(this).html();
        $(this).html('<i class="fas fa-check me-2"></i>Đã copy!');
        setTimeout(() => {
            $(this).html(originalText);
        }, 2000);
    });
});

// Global AJAX error handler
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    if (jqxhr.status === 401) {
        alert('Phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại.');
        window.location.href = '/views/auth/login.php';
    } else if (jqxhr.status === 403) {
        alert('Bạn không có quyền thực hiện thao tác này.');
    } else if (jqxhr.status >= 500) {
        alert('Lỗi server. Vui lòng thử lại sau.');
    }
});

// Format date helper
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}

// Time ago helper
function timeAgo(dateString) {
    var date = new Date(dateString);
    var seconds = Math.floor((new Date() - date) / 1000);
    
    var interval = seconds / 31536000;
    if (interval > 1) return Math.floor(interval) + " năm trước";
    
    interval = seconds / 2592000;
    if (interval > 1) return Math.floor(interval) + " tháng trước";
    
    interval = seconds / 86400;
    if (interval > 1) return Math.floor(interval) + " ngày trước";
    
    interval = seconds / 3600;
    if (interval > 1) return Math.floor(interval) + " giờ trước";
    
    interval = seconds / 60;
    if (interval > 1) return Math.floor(interval) + " phút trước";
    
    return Math.floor(seconds) + " giây trước";
}
