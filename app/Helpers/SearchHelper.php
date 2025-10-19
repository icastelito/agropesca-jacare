<?php

namespace App\Helpers;

class SearchHelper
{
    /**
     * Remove acentos de uma string
     */
    public static function removeAccents(string $text): string
    {
        $unwanted = [
            'á' => 'a',
            'à' => 'a',
            'ã' => 'a',
            'â' => 'a',
            'ä' => 'a',
            'Á' => 'A',
            'À' => 'A',
            'Ã' => 'A',
            'Â' => 'A',
            'Ä' => 'A',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'ó' => 'o',
            'ò' => 'o',
            'õ' => 'o',
            'ô' => 'o',
            'ö' => 'o',
            'Ó' => 'O',
            'Ò' => 'O',
            'Õ' => 'O',
            'Ô' => 'O',
            'Ö' => 'O',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'ç' => 'c',
            'Ç' => 'C',
            'ñ' => 'n',
            'Ñ' => 'N',
        ];

        return strtr($text, $unwanted);
    }

    /**
     * Normaliza string para busca (remove acentos, lowercase, remove espaços extras)
     */
    public static function normalize(string $text): string
    {
        $text = self::removeAccents($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text); // Remove espaços duplicados
        return trim($text);
    }

    /**
     * Prepara termo de busca para LIKE (divide em palavras individuais)
     */
    public static function prepareSearchTerms(string $search): array
    {
        $normalized = self::normalize($search);
        $words = explode(' ', $normalized);
        return array_filter($words); // Remove strings vazias
    }
}
