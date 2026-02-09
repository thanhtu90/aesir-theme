<?php
/**
 * WooCommerce Vietnam full provinces checkout
 * Works for v10+, Classic & Checkout Block
 */

add_filter( 'woocommerce_states', function( $states ) {
    $states['VN'] = array(
        'HN'  => 'Hà Nội',
        'HCM' => 'Hồ Chí Minh',
        'BD'  => 'Bình Dương',
        'BĐ'  => 'Bình Định',
        'BTh' => 'Bình Thuận',
        'BP'  => 'Bình Phước',
        'CM'  => 'Cà Mau',
        'CT'  => 'Cần Thơ',
        'CB'  => 'Cao Bằng',
        'ĐB'  => 'Điện Biên',
        'DN'  => 'Đà Nẵng',
        'ĐN'  => 'Đắk Nông',
        'ĐL'  => 'Đắk Lắk',
        'ĐTh' => 'Đồng Tháp',
        'AG'  => 'An Giang',
        'BRVT'=> 'Bà Rịa - Vũng Tàu',
        'BG'  => 'Bắc Giang',
        'BK'  => 'Bắc Kạn',
        'BL'  => 'Bạc Liêu',
        'BN'  => 'Bắc Ninh',
        'BT'  => 'Bến Tre',
        'GL'  => 'Gia Lai',
        'HG'  => 'Hà Giang',
        'HNam'=> 'Hà Nam',
        'HT'  => 'Hà Tĩnh',
        'HD'  => 'Hải Dương',
        'HP'  => 'Hải Phòng',
        'HG2' => 'Hậu Giang',
        'HB'  => 'Hòa Bình',
        'HY'  => 'Hưng Yên',
        'KH'  => 'Khánh Hòa',
        'KG'  => 'Kiên Giang',
        'KT'  => 'Kon Tum',
        'LC'  => 'Lai Châu',
        'LD'  => 'Lâm Đồng',
        'LS'  => 'Lạng Sơn',
        'LCA' => 'Lào Cai',
        'LA'  => 'Long An',
        'ND'  => 'Nam Định',
        'NA'  => 'Nghệ An',
        'NB'  => 'Ninh Bình',
        'NT'  => 'Ninh Thuận',
        'PT'  => 'Phú Thọ',
        'PY'  => 'Phú Yên',
        'QB'  => 'Quảng Bình',
        'QNam'=> 'Quảng Nam',
        'QN'  => 'Quảng Ngãi',
        'QNI' => 'Quảng Ninh',
        'QT'  => 'Quảng Trị',
        'ST'  => 'Sóc Trăng',
        'SL'  => 'Sơn La',
        'TN'  => 'Tây Ninh',
        'TB'  => 'Thái Bình',
        'TNg' => 'Thái Nguyên',
        'TH'  => 'Thanh Hóa',
        'TTH' => 'Thừa Thiên Huế',
        'TG'  => 'Tiền Giang',
        'TV'  => 'Trà Vinh',
        'TQ'  => 'Tuyên Quang',
        'VL'  => 'Vĩnh Long',
        'VP'  => 'Vĩnh Phúc',
        'YB'  => 'Yên Bái',
    );
    return $states;
});

/**
 * Force state visible & required, rename state label, postcode optional for VN
 */
add_filter( 'woocommerce_get_country_locale', function( $locale ) {

    foreach ( $locale as $country => $fields ) {
        if ( isset( $fields['state'] ) ) {
            $locale[$country]['state']['hidden']   = false;
            $locale[$country]['state']['required'] = true;
            $locale[$country]['state']['priority'] = 45;
        }
    }

    // VN specific
    if ( isset( $locale['VN'] ) ) {
        $locale['VN']['state']['label']       = 'Tỉnh / Thành phố';
        $locale['VN']['state']['placeholder'] = 'Chọn tỉnh / thành phố';
        if ( isset( $locale['VN']['postcode'] ) ) {
            $locale['VN']['postcode']['required'] = false;
        }
    }

    return $locale;
});
