<?php

/**
 * Helper de Conversão e Otimização de Imagens
 * 
 * - Converte para WebP (melhor compressão)
 * - Redimensiona mantendo proporção
 * - Gera thumbnails quadrados com crop central
 * 
 * @package App\Helpers
 */

namespace App\Helpers;

class ImageConverter
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Upload com otimização completa: redimensiona + cria thumbnail
     * 
     * @param array $file $_FILES['campo']
     * @param string $uploadDir Diretório principal
     * @param int $maxDimension Dimensão máxima da imagem principal (ex: 1200)
     * @param int $thumbSize Tamanho do thumbnail quadrado (ex: 300)
     * @return string|false Nome do arquivo ou false
     */
    public static function uploadWithThumbnail(
        array $file, 
        string $uploadDir, 
        int $maxDimension = 1200, 
        int $thumbSize = 300,
        int $quality = 85,
        int $thumbQuality = 80
    ) {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
            return false;
        }

        // Carrega imagem para GD
        $image = self::loadImage($file['tmp_name'], $ext);
        if (!$image) {
            return false;
        }

        // Gera nome único
        $uniqueName = md5(time() . rand(0, 9999)) . '.webp';
        $uploadDir = rtrim($uploadDir, '/');

        // 1. Redimensiona e salva imagem principal
        $resized = self::resize($image, $maxDimension);
        $mainPath = $uploadDir . '/' . $uniqueName;
        imagewebp($resized, $mainPath, $quality);

        // 2. Cria e salva thumbnail
        $thumb = self::createSquareThumbnail($image, $thumbSize);
        $thumbDir = $uploadDir . '/thumb';
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        imagewebp($thumb, $thumbDir . '/' . $uniqueName, $thumbQuality);

        // Limpa memória
        imagedestroy($image);
        if ($resized !== $image) {
            imagedestroy($resized);
        }
        imagedestroy($thumb);

        return $uniqueName;
    }

    /**
     * Gera thumbnail para imagem já existente
     */
    public static function generateThumbnailForExisting(string $imagePath, int $size = 300, int $quality = 80): bool
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        $ext = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $image = self::loadImage($imagePath, $ext);
        if (!$image) {
            return false;
        }

        $thumb = self::createSquareThumbnail($image, $size);

        // Determina caminho do thumb
        $dir = dirname($imagePath);
        $filename = pathinfo($imagePath, PATHINFO_FILENAME) . '.webp';
        $thumbDir = $dir . '/thumb';
        
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        $success = imagewebp($thumb, $thumbDir . '/' . $filename, $quality);

        imagedestroy($image);
        imagedestroy($thumb);

        return $success;
    }

    // =========================================================================
    // MÉTODOS PRIVADOS (Helpers Internos)
    // =========================================================================

    /**
     * Carrega imagem de qualquer formato suportado
     */
    private static function loadImage(string $path, string $ext)
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return @imagecreatefromjpeg($path);
            case 'png':
                $img = @imagecreatefrompng($path);
                if ($img) {
                    imagepalettetotruecolor($img);
                    imagealphablending($img, true);
                    imagesavealpha($img, true);
                }
                return $img;
            case 'gif':
                return @imagecreatefromgif($path);
            case 'webp':
                return @imagecreatefromwebp($path);
            default:
                return false;
        }
    }

    /**
     * Redimensiona uma imagem mantendo proporção
     */
    private static function resize($image, int $maxDimension)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Se já é menor que o máximo, retorna como está
        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $image;
        }

        // Calcula nova dimensão mantendo proporção
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = (int) floor($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int) floor($width * ($maxDimension / $height));
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $resized;
    }

    /**
     * Cria thumbnail quadrado com crop central
     */
    private static function createSquareThumbnail($image, int $size)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Determina área de crop (central)
        if ($width > $height) {
            $cropSize = $height;
            $srcX = (int) floor(($width - $height) / 2);
            $srcY = 0;
        } else {
            $cropSize = $width;
            $srcX = 0;
            $srcY = (int) floor(($height - $width) / 2);
        }

        $thumb = imagecreatetruecolor($size, $size);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $image, 0, 0, $srcX, $srcY, $size, $size, $cropSize, $cropSize);

        return $thumb;
    }
}
