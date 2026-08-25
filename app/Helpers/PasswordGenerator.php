<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class PasswordGenerator
{
    /**
     * Generate a secure random password
     *
     * @param int $length Password length (minimum 8)
     * @return string Generated password
     */
    public static function generate(int $length = 12): string
    {
        // Ensure minimum length for security
        $length = max(8, $length);

        // Define character sets
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '!@#$%&*';

        // Ensure at least one character from each set
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

        // Fill the rest with random characters from all sets
        $allChars = $lowercase . $uppercase . $numbers . $specialChars;
        $remainingLength = $length - 4;

        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password to randomize character positions
        $passwordArray = str_split($password);
        shuffle($passwordArray);

        return implode('', $passwordArray);
    }

    /**
     * Generate a memorable password with words and numbers
     *
     * @return string Generated password
     */
    public static function generateMemorable(): string
    {
        $words = ['Campus', 'Student', 'Faculty', 'Secure', 'Access', 'System', 'Portal', 'Account'];
        $word = $words[array_rand($words)];
        $number = random_int(100, 999);
        $special = ['!', '@', '#', '$', '%', '&', '*'][array_rand(['!', '@', '#', '$', '%', '&', '*'])];

        return $word . $number . $special;
    }
}
