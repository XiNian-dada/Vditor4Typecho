<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Vditor4Typecho_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public function action()
    {
        $options = Helper::options();
        $pluginOpts = $options->plugin('Vditor4Typecho');
        
        $watermarkText = $pluginOpts->watermarkText;
        $quality = (int)$pluginOpts->quality ?: 80;
        $fontPath = dirname(__FILE__) . '/font.ttf'; // 字体文件路径

        $response = [
            'msg' => '',
            'code' => 0,
            'data' => [
                'errFiles' => [],
                'succMap' => []
            ]
        ];

        if (empty($_FILES['file'])) {
            $this->outputError('没有检测到上传的文件');
            return;
        }

        $files = $_FILES['file'];
        
        // Vditor 可能一次上传多个文件
        if (!is_array($files['name'])) {
            $files = [
                'name' => [$files['name']],
                'type' => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error' => [$files['error']],
                'size' => [$files['size']]
            ];
        }

        $uploadDir = __TYPECHO_ROOT_DIR__ . '/usr/uploads/' . date('Y/m') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        for ($i = 0; $i < count($files['name']); $i++) {
            $originalName = $files['name'][$i];
            $tmpName = $files['tmp_name'][$i];
            $error = $files['error'][$i];

            if ($error !== UPLOAD_ERR_OK) {
                $response['data']['errFiles'][] = $originalName;
                continue;
            }

            // 获取图像信息
            $imgInfo = getimagesize($tmpName);
            if (!$imgInfo) {
                $response['data']['errFiles'][] = $originalName;
                continue;
            }

            // 根据类型创建图像资源
            $mime = $imgInfo['mime'];
            $image = null;
            switch ($mime) {
                case 'image/jpeg': $image = imagecreatefromjpeg($tmpName); break;
                case 'image/png':  $image = imagecreatefrompng($tmpName); break;
                case 'image/gif':  $image = imagecreatefromgif($tmpName); break;
                case 'image/webp': $image = imagecreatefromwebp($tmpName); break;
                default:
                    $response['data']['errFiles'][] = $originalName;
                    continue 2;
            }

            if (!$image) {
                $response['data']['errFiles'][] = $originalName;
                continue;
            }

            // 添加水印 (如果配置了文字且存在字体文件)
            if (!empty($watermarkText) && file_exists($fontPath)) {
                $width = imagesx($image);
                $height = imagesy($image);
                $fontSize = max(12, min($width, $height) / 25); // 动态计算字号
                
                // 设置水印颜色（半透明白色带黑色阴影，保证在亮暗背景都可见）
                $white = imagecolorallocatealpha($image, 255, 255, 255, 30);
                $black = imagecolorallocatealpha($image, 0, 0, 0, 50);

                // 获取文字边界计算位置 (右下角)
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $watermarkText);
                $textWidth = $bbox[2] - $bbox[0];
                $textHeight = $bbox[1] - $bbox[7];
                
                $x = $width - $textWidth - 20;
                $y = $height - 20;

                // 绘制阴影和文字
                imagettftext($image, $fontSize, 0, $x + 2, $y + 2, $black, $fontPath, $watermarkText);
                imagettftext($image, $fontSize, 0, $x, $y, $white, $fontPath, $watermarkText);
            }

            // 统一转换为 WebP 并保存
            $newName = uniqid() . '.webp';
            $targetPath = $uploadDir . $newName;
            $relativeUrl = '/usr/uploads/' . date('Y/m') . '/' . $newName;
            $fullUrl = $options->siteUrl . ltrim($relativeUrl, '/');

            // 开启透明度处理 (主要针对 PNG 转换)
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);

            // 输出为 WebP
            if (imagewebp($image, $targetPath, $quality)) {
                $response['data']['succMap'][$originalName] = $fullUrl;
            } else {
                $response['data']['errFiles'][] = $originalName;
            }

            // 释放内存
            imagedestroy($image);
        }

        $this->outputJson($response);
    }

    private function outputError($msg)
    {
        echo json_encode([
            'msg' => $msg,
            'code' => 1,
            'data' => ['errFiles' => [], 'succMap' => []]
        ]);
        exit;
    }

    private function outputJson($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}