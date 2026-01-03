<?php
/**
 * Helper de Conversão de Imagens para WebP
 * Converte PNG, JPG, JPEG para WebP com qualidade otimizada
 */

class ImageConverter {
    
    /**
     * Converte uma imagem para WebP
     * @param string $sourcePath Caminho completo do arquivo original
     * @param int $quality Qualidade (1-100), padrão 85
     * @return string|false Nome do arquivo WebP gerado ou false em caso de erro
     */
    public static function toWebp(string $sourcePath, int $quality = 85) {
        // Verifica se o arquivo existe
        if (!file_exists($sourcePath)) {
            return false;
        }

        // Detecta o tipo de imagem
        $imageInfo = getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        $mimeType = $imageInfo['mime'];

        // Cria o recurso de imagem baseado no tipo
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                // Preserva transparência
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                // Já é WebP, retorna o nome atual
                return basename($sourcePath);
            default:
                return false; // Formato não suportado
        }

        if (!$image) {
            return false;
        }

        // Gera o novo nome do arquivo (troca extensão por .webp)
        $pathInfo = pathinfo($sourcePath);
        $webpName = $pathInfo['filename'] . '.webp';
        $webpPath = $pathInfo['dirname'] . '/' . $webpName;

        // Converte para WebP
        $success = imagewebp($image, $webpPath, $quality);
        
        // Libera memória
        imagedestroy($image);

        if ($success) {
            return $webpName;
        }

        return false;
    }

    /**
     * Processa upload e converte para WebP
     * @param array $file Array $_FILES['campo']
     * @param string $uploadDir Diretório de destino
     * @return string|false Nome do arquivo WebP ou false em caso de erro
     */
    public static function uploadAndConvert(array $file, string $uploadDir, int $quality = 85) {
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Gera nome único
        $uniqueName = md5(time() . rand(0, 9999));
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Extensões permitidas
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowedExts)) {
            return false;
        }

        // Se já for WebP, apenas move
        if ($ext === 'webp') {
            $finalName = $uniqueName . '.webp';
            $destination = rtrim($uploadDir, '/') . '/' . $finalName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                return $finalName;
            }
            return false;
        }

        // Move temporariamente para converter
        $tempName = $uniqueName . '.' . $ext;
        $tempPath = rtrim($uploadDir, '/') . '/' . $tempName;
        
        if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
            return false;
        }

        // Converte para WebP
        $webpName = self::toWebp($tempPath, $quality);
        
        if ($webpName) {
            // Remove o arquivo original
            @unlink($tempPath);
            return $webpName;
        }

        // Se falhou a conversão, mantém o original
        return $tempName;
    }
}
