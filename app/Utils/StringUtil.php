<?php

namespace App\Utils;

use MirazMac\BanglaString\Translator\BijoyToAvro\Translator;

class StringUtil
{
    static function convertBanglaToEnglishPhoneNumber($phoneNumber)
    {

        if (!StringUtil::isBanglaPhoneNumber($phoneNumber)) return $phoneNumber;
        // Regular expression to match Bangla digits
        $banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        // Convert Bangla digits to English digits
        $phoneNumber = str_replace($banglaDigits, $englishDigits, $phoneNumber);

        return $phoneNumber;
    }

    static function isBanglaPhoneNumber($phoneNumber)
    {
        // Regular expression to match Bangla digits
        $banglaDigitsRegex = '/[০১২৩৪৫৬৭৮৯]/';

        // Check if the phone number contains Bangla digits
        if (preg_match($banglaDigitsRegex, $phoneNumber)) {
            return true; // Contains Bangla digits
        } else {
            return false; // Does not contain Bangla digits
        }
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

    public static function unicodeToBijoy(string $srcString): string
    {
        $translator = new Translator();
        $english = [];
        $pointer = -100;
        $temp = "";

        // Iterate through the string and collect English words
        $length = strlen($srcString);
        for ($i = 0; $i < $length; $i++) {
            $char = $srcString[$i];
            $asciiValue = ord($char);

            if ($asciiValue < 128 && $asciiValue != 32) {
                if ($pointer + 1 == $i) {
                    $temp .= $char;
                } else {
                    $temp = $char;
                }
                $pointer = $i;
                $english[$pointer - strlen($temp) + 1] = $temp;
            }
        }

        $start = 0;
        $returnString = '';
        foreach ($english as $key => $value) {
            $end = $key;
            $bengaliSegment = substr($srcString, $start, $end - $start);
            if (!empty($bengaliSegment)) {
                $translatedSegment = $translator->translate($bengaliSegment);
                $returnString .= $translatedSegment;
            }
            $returnString .= $value;
            $start = $end + strlen($value);
        }

        // If there are any remaining Bengali segments after the last English word
        if ($start < $length) {
            $remainingSegment = substr($srcString, $start);
            if (!empty($remainingSegment)) {
                $returnString .= $translator->translate($remainingSegment);
            }
        }

        // If no segments were added, translate the entire string
        if (empty($returnString)) {
            $returnString = $translator->translate($srcString);
        }

        return $returnString;
    }

}
