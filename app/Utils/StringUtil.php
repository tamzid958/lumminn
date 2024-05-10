<?php

namespace App\Utils;

class StringUtil
{
    static function generateReadableString($length = 2): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $char_length = strlen($characters);
        $random_string = '';

        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, $char_length - 1)];
        }

        $current_date = date('YmdHis');
        $random_string .= $current_date;
        return $random_string;
    }
}
