<?php

add_filter( 'gettext', 'custom_translate_texts', 999, 3 );
add_filter( 'ngettext', 'custom_translate_texts', 999, 3 );

add_filter('tutor_shortcode_courses_query', function($args, $atts) {
    if ( isset($atts['orderby']) && $atts['orderby'] === 'popular' ) {
        $args['meta_key'] = '_tutor_course_enrolled_users';
        $args['orderby'] = 'meta_value_num';
        $args['order'] = isset($atts['order']) ? $atts['order'] : 'DESC';
    }
    return $args;
}, 10, 2);

function custom_translate_texts( $translated, $text, $domain ) {
    $translations = array(
        // Trang sản phẩm
        'Add to cart'       => 'Thêm vào giỏ hàng',
        'Product Menu'      => 'Danh mục',
        'Product portfolio' => 'Danh mục',
        'Showing all %d results' => 'Hiển thị tất cả %d kết quả',
        'Description'       => 'Mô tả',
        'Reviews'           => 'Đánh giá',
        'Maybe you like'    => 'Có thể bạn thích',
        'HIGHLIGHTS'        => 'Nổi bật',
        'Highlights'        => 'Nổi bật',
        'Related products'  => 'Sản phẩm liên quan',

        // Cart + Checkout
        'Cart'              => 'Giỏ hàng',
		'CART'              => 'Giỏ hàng',
        'Checkout'          => 'Thanh toán',
        'Remove'            => 'Xóa',
        'View cart'         => 'Xem giỏ hàng',
		'VIEW CART'         => 'Xem giỏ hàng',
        'Subtotal'          => 'Tạm tính',
        'Billing details'   => 'Chi tiết thanh toán',
        'Have a coupon? Click here to enter your code' => 'Bạn có mã giảm giá? Nhấp vào đây để nhập mã',
        'Additional information' => 'Thông tin bổ sung',
        'Order notes (optional)' => 'Ghi chú đơn hàng (tùy chọn)',
        'Your order'        => 'Đơn hàng của bạn',
        'Product'           => 'Sản phẩm',
        'Total'             => 'Tổng cộng',
        'Direct bank transfer' => 'Chuyển khoản ngân hàng trực tiếp',
        'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.' 
            => 'Vui lòng chuyển khoản trực tiếp vào tài khoản ngân hàng của chúng tôi. Hãy sử dụng Mã đơn hàng làm nội dung chuyển khoản. Đơn hàng của bạn sẽ không được giao cho đến khi chúng tôi nhận được tiền.',
        'Cash on delivery'  => 'Thanh toán khi nhận hàng',
        'Pay with cash upon delivery.' => 'Thanh toán tiền mặt khi nhận hàng.',
        'Place order'       => 'Đặt hàng',
		'Order notes' => 'Ghi chú đơn hàng',
		'Order notes (optional)' => 'Ghi chú đơn hàng (tùy chọn)',
		'Login ' => 'Đăng nhập'
    );

    if ( isset( $translations[$text] ) ) {
        $translated = $translations[$text];
    }

    return $translated;
}


add_filter( 'woocommerce_account_menu_items', 'custom_rename_my_account_menu_items' );
function custom_rename_my_account_menu_items( $items ) {
    $items['dashboard']       = 'Bảng điều khiển';
    $items['orders']          = 'Đơn hàng';
    $items['downloads']       = 'Tải xuống';
    $items['edit-address']    = 'Địa chỉ';
    $items['edit-account']    = 'Thông tin tài khoản';
    $items['customer-logout'] = 'Đăng xuất';
    return $items;
}

// Việt hoá cột + trạng thái WooCommerce
add_filter( 'gettext', 'custom_translate_woocommerce_texts', 999, 3 );
function custom_translate_woocommerce_texts( $translated, $text, $domain ) {
    if ( $domain === 'woocommerce' ) {
        switch ( $text ) {
            case 'Order':
                $translated = 'Đơn hàng';
                break;
            case 'Date':
                $translated = 'Ngày đặt';
                break;
            case 'Status':
                $translated = 'Trạng thái';
                break;
            case 'Total':
                $translated = 'Tổng cộng';
                break;
            case 'Actions':
                $translated = 'Thao tác';
                break;
            case 'Processing':
                $translated = 'Đang xử lý';
                break;
            case 'Completed':
                $translated = 'Hoàn tất';
                break;
            case 'On hold':
                $translated = 'Tạm giữ';
                break;
            case 'Cancelled':
                $translated = 'Đã hủy';
                break;
            case 'Refunded':
                $translated = 'Đã hoàn tiền';
                break;
            case 'Failed':
                $translated = 'Thất bại';
                break;
        }
    }
    return $translated;
}


add_filter( 'gettext', 'custom_translate_order_texts', 999, 3 );
function custom_translate_order_texts( $translated, $text, $domain ) {
    if ( $domain === 'woocommerce' ) {
        switch ( $text ) {
            case 'Order':
                $translated = 'Đơn hàng';
                break;
            case 'was placed on':
                $translated = 'được đặt vào ngày';
                break;
            case 'and is currently':
                $translated = 'và hiện đang ở trạng thái';
                break;
            case 'Processing':
                $translated = 'Đang xử lý';
                break;
            case 'Order details':
                $translated = 'Chi tiết đơn hàng';
                break;
            case 'Subtotal:':
                $translated = 'Tạm tính:';
                break;
            case 'Payment method:':
                $translated = 'Phương thức thanh toán:';
                break;
            case 'Total:':
                $translated = 'Tổng cộng:';
                break;
            case 'Billing address':
                $translated = 'Địa chỉ thanh toán';
                break;
        }
    }
    return $translated;
}

add_filter( 'gettext', function( $translated, $original, $domain ) {

    // Chỉ áp dụng cho WooCommerce
    if ( $domain === 'woocommerce' ) {

        switch ( $original ) {
            case 'First name':
                return 'Tên';
            case 'Last name':
                return 'Họ';
            case 'Display name':
                return 'Tên hiển thị';
            case 'This will be how your name will be displayed in the account section and in reviews':
                return 'Tên này sẽ hiển thị trong trang tài khoản và trong phần đánh giá';
            case 'Email address':
                return 'Địa chỉ email';
            case 'Password change':
                return 'Đổi mật khẩu';
            case 'Current password (leave blank to leave unchanged)':
                return 'Mật khẩu hiện tại (để trống nếu không đổi)';
            case 'New password (leave blank to leave unchanged)':
                return 'Mật khẩu mới (để trống nếu không đổi)';
            case 'Confirm new password':
                return 'Xác nhận mật khẩu mới';
			case 'SAVE CHANGES':
                return 'Lưu thay đổi';
        }
    }

    return $translated;
}, 999, 3 );

add_filter( 'gettext', function( $translated, $original, $domain ) {

    if ( $original === 'Login / Register' ) {
        return 'Đăng nhập / Đăng ký';
    }

    return $translated;
}, 999, 3 );

add_action('woocommerce_after_single_product_summary', 'my_custom_review_block', 15);

function my_custom_review_block() {
    global $product;
    if (!$product) return;
    $product_id = $product->get_id();
    ?>
    <div id="custom-product-review" class="custom-review-form">
        <h3>ĐÁNH GIÁ SẢN PHẨM</h3>

        <?php
        $comments = get_comments(array('post_id' => $product_id, 'status' => 'approve'));
        if (!$comments) {
            echo '<p>Chưa có đánh giá nào.</p>';
        }

        $commenter = wp_get_current_commenter();
        $fields = array(
            'author' => '<p class="comment-form-author">
                <label for="author">Tên *</label>
                <input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" required />
            </p>',
            'email'  => '<p class="comment-form-email">
                <label for="email">Email *</label>
                <input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" required />
            </p>',
        );

        $args = array(
            'title_reply'         => '',
            'fields'              => $fields,
            'comment_field'       => '
                <p class="comment-form-rating">
                    <label for="rating">Đánh giá của bạn *</label>
                    <p class="stars">
                        <span>
                            <a class="star-1" data-value="1" href="#">1</a>
                            <a class="star-2" data-value="2" href="#">2</a>
                            <a class="star-3" data-value="3" href="#">3</a>
                            <a class="star-4" data-value="4" href="#">4</a>
                            <a class="star-5" data-value="5" href="#">5</a>
                        </span>
                    </p>
                    <select name="rating" id="rating" required style="display:none;">
                        <option value="">Chọn…</option>
                        <option value="5">Rất tốt</option>
                        <option value="4">Tốt</option>
                        <option value="3">Bình thường</option>
                        <option value="2">Tệ</option>
                        <option value="1">Rất tệ</option>
                    </select>
                </p>
                <p class="comment-form-comment">
                    <label for="comment">Nhận xét của bạn *</label>
                    <textarea id="comment" name="comment" cols="45" rows="6" required></textarea>
                </p>',
            'label_submit'        => 'GỬI ĐI',
            'class_submit'        => 'submit'
        );

        comment_form($args, $product_id);
        ?>
    </div>

    <style>
        .custom-review-form {
            border: 1px solid #ddd;
            padding: 20px;
            margin-top: 20px;
            background: #fff;
        }
        .custom-review-form h3 {
            margin-bottom: 15px;
            color: #d00;
            font-size: 18px;
            border-bottom: 2px solid #d00;
            padding-bottom: 5px;
        }
        .custom-review-form .stars a {
            display: inline-block;
            font-size: 24px;
            color: #ccc;
            text-decoration: none;
            margin-right: 5px;
        }
        .custom-review-form .stars a.active,
        .custom-review-form .stars a:hover,
        .custom-review-form .stars a:hover ~ a {
            color: #ffcc00;
        }
    </style>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const stars = document.querySelectorAll(".custom-review-form .stars a");
        const ratingSelect = document.getElementById("rating");

        stars.forEach(star => {
            star.addEventListener("click", function(e) {
                e.preventDefault();
                const value = this.getAttribute("data-value");

                // reset active
                stars.forEach(s => s.classList.remove("active"));
                // add active cho các sao <= value
                stars.forEach(s => {
                    if (parseInt(s.getAttribute("data-value")) <= value) {
                        s.classList.add("active");
                    }
                });

                ratingSelect.value = value;
            });
        });
    });
    </script>
    <?php
}


add_filter('woocommerce_account_menu_items', 'custom_account_menu_items');

function custom_account_menu_items($items) {
    return [
        'thong-tin-ca-nhan' => 'Thông tin cá nhân',
        'kho-voucher'       => 'Kho voucher',
        'bep-cua-toi'       => 'Bếp của tôi',
        'don-hang'          => 'Đơn hàng',
    ];
}

add_action('init', function () {
    add_rewrite_endpoint('thong-tin-ca-nhan', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('kho-voucher', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('bep-cua-toi', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('don-hang', EP_ROOT | EP_PAGES);
});

// Tạo nội dung trống cho mỗi tab (nếu cần)
add_action('woocommerce_account_thong-tin-ca-nhan_endpoint', function () {
    echo '<h3>Thông tin cá nhân</h3>';
});
add_action('woocommerce_account_kho-voucher_endpoint', function () {
    echo '<h3>Kho voucher</h3>';
});
add_action('woocommerce_account_bep-cua-toi_endpoint', function () {
    echo '<h3>Bếp của tôi</h3>';
});
add_action('woocommerce_account_don-hang_endpoint', function () {
    echo '<h3>Đơn hàng</h3>';
});

