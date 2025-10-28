<?php

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'Y-m-d H:i:s') {
        return $date ? date($format, strtotime($date)) : null;
    }
}

if (!function_exists('formatDateId')) {
    function formatDateId($date) {
        return $date ? date('d-m-Y H:i', strtotime($date)) : null;
    }
}

if (!function_exists('successResponse')) {
    function successResponse($data = null, $message = 'Success', $code = 200) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

if (!function_exists('errorResponse')) {
    function errorResponse($message = 'Error', $errors = null, $code = 400) {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}

if (!function_exists('generateToken')) {
    function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('cleanPhone')) {
    function cleanPhone($phone) {
        return preg_replace('/[^0-9]/', '', $phone);
    }
}

if (!function_exists('maskEmail')) {
    function maskEmail($email) {
        if (!$email) return null;

        $parts = explode('@', $email);
        $name = $parts[0];
        $length = strlen($name);

        if ($length <= 3) {
            $masked = substr($name, 0, 1) . '**';
        } else {
            $masked = substr($name, 0, 2) . str_repeat('*', $length - 4) . substr($name, -2);
        }

        return $masked . '@' . $parts[1];
    }
}

if (!function_exists('fileSizeFormat')) {
    function fileSizeFormat($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}

if (!function_exists('rupiah')) {
    function rupiah($amount) {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

if (!function_exists('percentage')) {
    function percentage($part, $total, $precision = 2) {
        if ($total == 0) return 0;
        return round(($part / $total) * 100, $precision);
    }
}

if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) return $text;
        return substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('randomColor')) {
    function randomColor() {
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}

if (!function_exists('getInitials')) {
    function getInitials($name) {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}

if (!function_exists('isActiveRoute')) {
    function isActiveRoute($route, $output = 'active') {
        return request()->routeIs($route) ? $output : '';
    }
}

if (!function_exists('avatarUrl')) {
    function avatarUrl($name, $size = 100) {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=' . $size;
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;

        $periods = ['detik', 'menit', 'jam', 'hari', 'minggu', 'bulan', 'tahun'];
        $lengths = [60, 60, 24, 7, 4.35, 12];

        for ($i = 0; $difference >= $lengths[$i] && $i < count($lengths); $i++) {
            $difference /= $lengths[$i];
        }

        $difference = round($difference);
        return $difference . ' ' . $periods[$i] . ' yang lalu';
    }
}

if (!function_exists('arrayToOptions')) {
    function arrayToOptions($array, $selected = null) {
        $html = '';
        foreach ($array as $key => $value) {
            $isSelected = ($selected == $key) ? 'selected' : '';
            $html .= "<option value='{$key}' {$isSelected}>{$value}</option>";
        }
        return $html;
    }
}
