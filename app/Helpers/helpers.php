<?php

use App\Helpers\ThaiDateHelper;

/**
 * Helper functions สำหรับการจัดการวันที่แบบไทย
 * ใช้งานได้ทั้ง Controller, Blade Template
 */

if (!function_exists('thai_date')) {
    /**
     * แปลงวันที่เป็นรูปแบบไทยแบบสั้น
     * เช่น: 11 มี.ค. 66
     * 
     * @param mixed $date
     * @return string
     */
    function thai_date($date)
    {
        return ThaiDateHelper::formatShort($date);
    }
}

if (!function_exists('thai_date_full')) {
    /**
     * แปลงวันที่เป็นรูปแบบไทยแบบเต็ม
     * เช่น: 11 มีนาคม 2566
     * 
     * @param mixed $date
     * @return string
     */
    function thai_date_full($date)
    {
        return ThaiDateHelper::formatFull($date);
    }
}

if (!function_exists('thai_date_slash')) {
    /**
     * แปลงวันที่เป็นรูปแบบ d/m/yy (ไทย)
     * เช่น: 11/3/66
     * 
     * @param mixed $date
     * @return string
     */
    function thai_date_slash($date)
    {
        return ThaiDateHelper::formatSlash($date);
    }
}

if (!function_exists('thai_datetime')) {
    /**
     * แสดงวันและเวลาแบบไทย
     * เช่น: 11 มี.ค. 66 เวลา 14:30 น.
     * 
     * @param mixed $datetime
     * @return string
     */
    function thai_datetime($datetime)
    {
        return ThaiDateHelper::formatDateTime($datetime);
    }
}

if (!function_exists('parse_thai_date')) {
    /**
     * แปลงวันที่ไทย (d/m/yy) เป็น Carbon object
     * 
     * @param string $thaiDate
     * @return \Carbon\Carbon|null
     */
    function parse_thai_date($thaiDate)
    {
        return ThaiDateHelper::parseSlash($thaiDate);
    }
}

if (!function_exists('thai_diff_humans')) {
    /**
     * แสดงวันที่แบบเทียบกับวันนี้
     * เช่น: 2 วันที่แล้ว
     * 
     * @param mixed $date
     * @return string
     */
    function thai_diff_humans($date)
    {
        return ThaiDateHelper::diffForHumans($date);
    }
}