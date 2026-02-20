<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 现代化的 Vditor 编辑器，支持所见即所得、自动 WebP 压缩与水印
 * @package Vditor4Typecho
 * @author XiNian-dada
 * @version 1.0.0
 * @link https://github.com/XiNian-dada/Vditor4Typecho
 */
class Vditor4Typecho_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Helper::addAction('vditor_upload', 'Vditor4Typecho_Action');
        // 只保留 bottom 挂载点，确保代码 100% 执行
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('Vditor4Typecho_Plugin', 'renderVditor');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array('Vditor4Typecho_Plugin', 'renderVditor');

        return _t('Vditor 插件激活成功！');
    }

    public static function deactivate()
    {
        Helper::removeAction('vditor_upload');
        return _t('Vditor 插件已禁用。');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $watermarkText = new Typecho_Widget_Helper_Form_Element_Text('watermarkText', NULL, 'My Blog', _t('水印文字'), _t('图片右下角的水印内容，留空则不加水印。'));
        $form->addInput($watermarkText);
        $quality = new Typecho_Widget_Helper_Form_Element_Text('quality', NULL, '80', _t('WebP 压缩质量'), _t('0-100 之间的整数，推荐 80。'));
        $form->addInput($quality);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

    private static function getBaseUrl() {
        return rtrim(Helper::options()->siteUrl, '/') . '/usr/plugins/Vditor4Typecho';
    }

    public static function renderVditor()
    {
        $pluginBase = self::getBaseUrl();
        $cssUrl = $pluginBase . '/dist/index.css';
        $jsUrl = $pluginBase . '/dist/index.min.js';
        $uploadUrl = Helper::security()->getIndex('/action/vditor_upload');
        
        // 【关键修复】：强制在这里输出 CSS，绕过 Typecho 无效的 header 挂载点
        echo '<link rel="stylesheet" type="text/css" href="' . $cssUrl . '" />';
        echo '<style>
            #wmd-button-bar, #wmd-preview, .wmd-prompt-dialog { display: none !important; }
            #text { display: none; }
            .vditor { border: 1px solid #d1d5da; border-radius: 3px; }
        </style>';
        
        echo '<script type="text/javascript" src="' . $jsUrl . '"></script>';
        ?>
        <script>
        $(document).ready(function() {
            var $textarea = $('#text');
            $textarea.before('<div id="vditor-container"></div>');

            var vditor = new Vditor('vditor-container', {
                cdn: '<?php echo $pluginBase; ?>', 
                height: Math.max($(window).height() - 250, 500),
                mode: 'wysiwyg', 
                cache: { enable: false },
                value: $textarea.val(),
                input: function(value) {
                    $textarea.val(value);
                },
                upload: {
                    accept: 'image/*, .webp',
                    url: '<?php echo $uploadUrl; ?>',
                    format(files, responseText) {
                        var res = JSON.parse(responseText);
                        var succMap = {};
                        if (res.code === 0) {
                            for (var key in res.data.succMap) {
                                succMap[key] = res.data.succMap[key];
                            }
                        }
                        return JSON.stringify({
                            msg: res.msg,
                            code: res.code,
                            data: {
                                errFiles: res.data.errFiles || [],
                                succMap: succMap
                            }
                        });
                    }
                }
            });

            window.vditorInstance = vditor;

            setInterval(function() {
                if (window.vditorInstance) {
                    var currentVal = window.vditorInstance.getValue();
                    if ($textarea.val() !== currentVal) {
                        $textarea.val(currentVal);
                    }
                }
            }, 3000);

            window.Typecho = window.Typecho || {};
            window.Typecho.insertFileToEditor = function (file, url, isImage) {
                var markdownStr = isImage ? '![' + file + '](' + url + ')' : '[' + file + '](' + url + ')';
                if (window.vditorInstance) {
                    window.vditorInstance.insertValue(markdownStr);
                } else {
                    $textarea.val($textarea.val() + '\n' + markdownStr);
                }
            };
        });
        </script>
        <?php
    }
}