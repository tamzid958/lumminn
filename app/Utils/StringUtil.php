<?php

namespace App\Utils;

class StringUtil
{
    static function isBanglaPhoneNumber($phoneNumber) {
        // Regular expression to match Bangla digits
        $banglaDigitsRegex = '/[০১২৩৪৫৬৭৮৯]/';
    
        // Check if the phone number contains Bangla digits
        if (preg_match($banglaDigitsRegex, $phoneNumber)) {
            return true; // Contains Bangla digits
        } else {
            return false; // Does not contain Bangla digits
        }
    }

    static function convertBanglaToEnglishPhoneNumber($phoneNumber) {

        if(!StringUtil::isBanglaPhoneNumber($phoneNumber)) return $phoneNumber;
        // Regular expression to match Bangla digits
        $banglaDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
        $englishDigits = ['0','1','2','3','4','5','6','7','8','9'];
    
        // Convert Bangla digits to English digits
        $phoneNumber = str_replace($banglaDigits, $englishDigits, $phoneNumber);
    
        return $phoneNumber;
    }

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

    static function removeCountryCode($phoneNumber)
    {
        // Check if the phone number starts with a country code
        if (strpos($phoneNumber, '+88') === 0) {
            // If it does, remove the country code
            return substr($phoneNumber, 3);
        } else {
            // If it doesn't, return the phone number as is
            return $phoneNumber;
        }
    }
}
