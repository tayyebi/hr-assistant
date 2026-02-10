<?php

namespace App\Core\Utils;

/**
 * String Utility Helper
 * Example of PSR-4 namespaced utility class
 */
class StringHelper
{
    /**
     * Convert string to camelCase
     */
    public static function toCamelCase(string $str): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str))));
    }
    
    /**
     * Convert string to PascalCase
     */
    public static function toPascalCase(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str)));
    }
    
    /**
     * Convert string to snake_case
     */
    public static function toSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($str)));
    }
    
    /**
     * Generate a random string
     */
    public static function random(int $length = 10): string
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
    }
}