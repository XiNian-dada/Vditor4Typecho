# Vditor4Typecho

🚀 **将 Typecho 原生的 Markdown 编辑器替换为现代化的 [Vditor](https://github.com/Vanessa219/vditor)。**

Vditor4Typecho 是一个为 Typecho 深度定制的富文本/Markdown 编辑器插件。它不仅带来了所见即所得的流畅写作体验，还在底层完全接管了 Typecho 的附件系统，实现了**图片自动 WebP 压缩**与**自定义文字水印**。

## ✨ 核心特性

- **所见即所得 (WYSIWYG)：** 告别割裂的双栏预览，像使用 Word 一样编写 Markdown。
- **100% 本地化加载：** 彻底抛弃 CDN 依赖。内置完整的 Vditor 静态资源，无惧浏览器防跟踪策略或网络波动，加载速度极快。
- **无缝接管原生机制：**
  - 支持直接拖拽/粘贴图片上传。
  - 完美兼容 Typecho 原生的“自动保存草稿”功能。
  - 接管原生附件列表，点击附件自动插入到 Vditor 光标位置。
- **图片自动优化 (Built-in)：**
  - **自动转换为 WebP 格式：** 显著减小图片体积，提升网站加载速度。
  - **自动添加文字水印：** 保护你的原创图片版权（需配置字体文件）。

## 📦 安装与配置

### 1. 下载与安装

1. 点击项目的 **[Releases](#)** 页面，下载最新版本的 `.zip` 压缩包（包含编译好的 `dist` 静态资源）。
2. 将压缩包解压，并确保文件夹名称为 `Vditor4Typecho`。
3. 将该文件夹上传到你 Typecho 网站的 `usr/plugins/` 目录下。
4. 登录 Typecho 后台，在“控制台 -> 插件”中启用 `Vditor4Typecho`。


### 2. 水印功能配置（必看）

如果你需要开启图片加水印功能，请执行以下步骤：

1. 准备一个支持中文的 `.ttf` 字体文件（例如思源黑体、微软雅黑等，默认自带苹方字体）。
2. 将该字体文件重命名为 **`font.ttf`**。
3. 将 `font.ttf` 上传到插件的根目录（即 `usr/plugins/Vditor4Typecho/` 下，与 `Plugin.php` 同级）。
4. 在 Typecho 后台的插件设置中，填写你想要的“水印文字”。

*(如果不放置字体文件，插件将自动跳过加水印步骤，但依然会执行 WebP 压缩。)*

## 🛠️ 环境要求

- **Typecho:** 1.3.0 或更高版本 1.2.x未测试
- **PHP:** 7.2 或更高版本
- **PHP 扩展:** 必须开启 `gd` 扩展，并且编译时需包含对 WebP (`WebP Support`) 和 Freetype (`FreeType Support`) 的支持。

## 👨‍💻 开发者

- **作者:** [XiNian-dada](https://github.com/XiNian-dada)

## 📄 开源协议

本项目基于 [MIT License](LICENSE) 开源。
内置的 Vditor 静态资源版权归原作者所有。