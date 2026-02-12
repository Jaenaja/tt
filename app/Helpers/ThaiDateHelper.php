<?php

namespace App\Helpers;

use Carbon\Carbon;

class ThaiDateHelper
{
    /**
     * อักษรย่อเดือนภาษาไทย
     */
    private static $thaiMonths = [
        1 => 'ม.ค.',
        2 => 'ก.พ.',
        3 => 'มี.ค.',
        4 => 'เม.ย.',
        5 => 'พ.ค.',
        6 => 'มิ.ย.',
        7 => 'ก.ค.',
        8 => 'ส.ค.',
        9 => 'ก.ย.',
        10 => 'ต.ค.',
        11 => 'พ.ย.',
        12 => 'ธ.ค.'
    ];

    /**
     * ชื่อเดือนภาษาไทยแบบเต็ม
     */
    private static $thaiMonthsFull = [
        1 => 'มกราคม',
        2 => 'กุมภาพันธ์',
        3 => 'มีนาคม',
        4 => 'เมษายน',
        5 => 'พฤษภาคม',
        6 => 'มิถุนายน',
        7 => 'กรกฎาคม',
        8 => 'สิงหาคม',
        9 => 'กันยายน',
        10 => 'ตุลาคม',
        11 => 'พฤศจิกายน',
        12 => 'ธันวาคม'
    ];

    /**
     * แปลงวันที่เป็นรูปแบบไทยแบบสั้น
     * เช่น: 11 มี.ค. 66
     * 
     * @param Carbon|string|null $date
     * @return string
     */
    public static function formatShort($date)
    {
        if (!$date)
            return '';

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        $day = $carbon->day;
        $month = self::$thaiMonths[$carbon->month];
        $year = ($carbon->year + 543) % 100; // แปลงเป็น พ.ศ. 2 หลัก

        return sprintf('%d %s %02d', $day, $month, $year);
    }

    /**
     * แปลงวันที่เป็นรูปแบบไทยแบบเต็ม
     * เช่น: 11 มีนาคม 2566
     * 
     * @param Carbon|string|null $date
     * @return string
     */
    public static function formatFull($date)
    {
        if (!$date)
            return '';

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        $day = $carbon->day;
        $month = self::$thaiMonthsFull[$carbon->month];
        $year = $carbon->year + 543; // พ.ศ. 4 หลัก

        return sprintf('%d %s %d', $day, $month, $year);
    }

    /**
     * แปลงวันที่เป็นรูปแบบ d/m/yy (ไทย)
     * เช่น: 11/3/66
     * 
     * @param Carbon|string|null $date
     * @return string
     */
    public static function formatSlash($date)
    {
        if (!$date)
            return '';

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        $day = $carbon->day;
        $month = $carbon->month;
        $year = ($carbon->year + 543) % 100; // พ.ศ. 2 หลัก

        return sprintf('%d/%d/%02d', $day, $month, $year);
    }

    /**
     * แปลงวันที่เป็นรูปแบบ dd/mm/yy (ไทย)
     * เช่น: 11/03/66
     * 
     * @param Carbon|string|null $date
     * @return string
     */
    public static function formatSlashPadded($date)
    {
        if (!$date)
            return '';

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        $day = $carbon->day;
        $month = $carbon->month;
        $year = ($carbon->year + 543) % 100; // พ.ศ. 2 หลัก

        return sprintf('%02d/%02d/%02d', $day, $month, $year);
    }

    /**
     * แปลงวันที่ไทย (d/m/yy) เป็น Carbon object
     * เช่น: 11/3/66 -> Carbon object (2023-03-11)
     * 
     * @param string $thaiDate รูปแบบ d/m/yy
     * @return Carbon|null
     */
    public static function parseSlash($thaiDate)
    {
        if (!$thaiDate)
            return null;

        $parts = explode('/', $thaiDate);
        if (count($parts) !== 3)
            return null;

        $day = (int) $parts[0];
        $month = (int) $parts[1];
        $year = (int) $parts[2];

        // แปลง พ.ศ. 2 หลัก เป็น ค.ศ. 4 หลัก
        $fullYear = 2500 + $year; // พ.ศ. 4 หลัก
        $gregorianYear = $fullYear - 543; // ค.ศ.

        return Carbon::create($gregorianYear, $month, $day);
    }

    /**
     * แปลงวันที่ไทย (d/m/yyyy) เป็น Carbon object
     * เช่น: 11/3/2566 -> Carbon object (2023-03-11)
     * 
     * @param string $thaiDate รูปแบบ d/m/yyyy
     * @return Carbon|null
     */
    public static function parseSlashFull($thaiDate)
    {
        if (!$thaiDate)
            return null;

        $parts = explode('/', $thaiDate);
        if (count($parts) !== 3)
            return null;

        $day = (int) $parts[0];
        $month = (int) $parts[1];
        $buddhistYear = (int) $parts[2];

        // แปลง พ.ศ. เป็น ค.ศ.
        $gregorianYear = $buddhistYear - 543;

        return Carbon::create($gregorianYear, $month, $day);
    }

    /**
     * แสดงวันที่แบบเทียบกับวันนี้
     * เช่น: 2 วันที่แล้ว, เมื่อวาน, วันนี้, พรุ่งนี้
     * 
     * @param Carbon|string|null $date
     * @return string
     */
    public static function diffForHumans($date)
    {
        if (!$date)
            return '';

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        // ตั้งค่าภาษาไทย
        $diff = $carbon->diffForHumans();

        // แปลเป็นภาษาไทย
        $translations = [
            'second' => 'วินาที',
            'seconds' => 'วินาที',
            'minute' => 'นาที',
            'minutes' => 'นาที',
            'hour' => 'ชั่วโมง',
            'hours' => 'ชั่วโมง',
            'day' => 'วัน',
            'days' => 'วัน',
            'week' => 'สัปดาห์',
            'weeks' => 'สัปดาห์',
            'month' => 'เดือน',
            'months' => 'เดือน',
            'year' => 'ปี',
            'years' => 'ปี',
            'ago' => 'ที่แล้ว',
            'from now' => 'จากนี้',
            'before' => 'ก่อน',
            'after' => 'หลัง',
        ];

        foreach ($translations as $en => $th) {
            $diff = str_replace($en, $th, $diff);
        }

        return $diff;
    }

    /**
     * ตรวจสอบว่าวันที่อยู่ในอดีตหรือไม่
     * 
     * @param Carbon|string|null $date
     * @return bool
     */
    public static function isPast($date)
    {
        if (!$date)
            return false;

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->isPast();
    }

    /**
     * ตรวจสอบว่าวันที่อยู่ในอนาคตหรือไม่
     * 
     * @param Carbon|string|null $date
     * @return bool
     */
    public static function isFuture($date)
    {
        if (!$date)
            return false;

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->isFuture();
    }

    /**
     * ตรวจสอบว่าวันที่เป็นวันนี้หรือไม่
     * 
     * @param Carbon|string|null $date
     * @return bool
     */
    public static function isToday($date)
    {
        if (!$date)
            return false;

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->isToday();
    }

    /**
     * แสดงวันและเวลาแบบไทย
     * เช่น: 11 มี.ค. 66 เวลา 14:30 น.
     * 
     * @param Carbon|string|null $datetime
     * @return string
     */
    public static function formatDateTime($datetime)
    {
        if (!$datetime)
            return '';

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);

        $date = self::formatShort($carbon);
        $time = $carbon->format('H:i');

        return sprintf('%s เวลา %s น.', $date, $time);
    }

    /**
     * แปลงวันที่เป็นรูปแบบสำหรับ database (Y-m-d)
     * 
     * @param Carbon|string|null $date
     * @return string|null
     */
    public static function toDatabase($date)
    {
        if (!$date)
            return null;

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $carbon->format('Y-m-d');
    }

    /**
     * แปลงวันที่และเวลาเป็นรูปแบบสำหรับ database (Y-m-d H:i:s)
     * 
     * @param Carbon|string|null $datetime
     * @return string|null
     */
    public static function toDatabaseDateTime($datetime)
    {
        if (!$datetime)
            return null;

        $carbon = $datetime instanceof Carbon ? $datetime : Carbon::parse($datetime);
        return $carbon->format('Y-m-d H:i:s');
    }

    /**
     * รับวันที่ปัจจุบันในรูปแบบไทย
     * 
     * @param string $format 'short'|'full'|'slash'
     * @return string
     */
    public static function now($format = 'short')
    {
        $now = Carbon::now();

        switch ($format) {
            case 'full':
                return self::formatFull($now);
            case 'slash':
                return self::formatSlash($now);
            default:
                return self::formatShort($now);
        }
    }
}