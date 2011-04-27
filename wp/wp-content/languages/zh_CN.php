<?php

define( 'ZH_CN_PACK_OPTIONS_VERSION' , 3 );

function zh_cn_language_pack_backend_register_settings() {
    add_option( 'zh_cn_language_pack_enable_backend_style_modifications', 1 );
    add_option( 'zh_cn_language_pack_enable_chinese_fake_oembed', 1 );
    
    register_setting( 'zh-cn-language-pack-general-settings',
                      'zh_cn_language_pack_enable_backend_style_modifications' );
    register_setting( 'zh-cn-language-pack-general-settings',
                      'zh_cn_language_pack_enable_chinese_fake_oembed' );

    delete_option( 'zh_cn_language_pack_options_version' ); // TODO 请不要忘记在 3.1 以后移除本行
}

function zh_cn_language_pack_backend_create_menu() {
    add_options_page( '中文本地化选项', '中文本地化', 'administrator', 'zh-cn-language-pack-settings', 
                      'zh_cn_language_pack_settings_page' );
}

function zh_cn_language_pack_contextual_help() {
    add_contextual_help('settings_page_zh-cn-language-pack-settings',
        '<p>在这里对 WordPress 官方中文语言包进行自定义。</p>' .
        '<p><strong>后台样式优化</strong> - 开启后可以令后台显示中文更加美观，它不会影响到您站点前台的样式。默认开启。</p>' .
        '<p><strong>中文视频网站视频自动嵌入</strong> - 允许您以在文章添加视频播放页面网址的方式，简单地插入优酷网、56.com 和土豆网视频。默认开启。<br />当前支持的站点、样例 URL 和参数如下：</p>' .
        '<ul>' .
        '   <li><em>优酷网</em> - 如 <code>http://v.youku.com/v_show/id_XMjQxMjc1MDIw.html</code> - 宽 480px，高 400px</li>' .
        '   <li><em>56.com</em> - 如 <code>http://www.56.com/u21/v_NTgxMzE4NDI.html</code> - 宽 480px，高 395px</li>' .
        '   <li><em>土豆网</em> - 如 <code>http://www.tudou.com/programs/view/o9tsm_CL5As/</code> - 宽 480px，高 400px</li>' .
        '</ul>' .
        '<p>您只需在文章另起一段，写入形如上述的播放页面链接。在文章显示时，WordPress 将自动替换这些链接为相应视频播放器。需要您特别注意的是，请不要为 URL 设置超链接，且该 URL 本身必须独立成段。' .
        '<p><strong>更多信息：</strong></p>' .
        '<p>若您发现任何文字上的错误，或有任何意见、建议，欢迎访问下列页面进行回报 ——<br />' .
        '<a href="http://cn.wordpress.org/contact/" target="_blank">WordPress China “联系”页面</a> - 不过，需要您注意的是，并不是所有问题都会被回答。</p>'
    );
}

function zh_cn_language_pack_settings_page() {
    ?><div class="wrap">
<h2>中文本地化选项</h2>

<form method="post" action="options.php">
    <h3 class="title">调整设置</h3>
    <p>对中文语言包进行自定义。</p>
    <?php settings_fields( 'zh-cn-language-pack-general-settings' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">后台样式优化</th>
            <td>
                <label for="zh_cn_language_pack_enable_backend_style_modifications"><input type="checkbox" id="zh_cn_language_pack_enable_backend_style_modifications" name="zh_cn_language_pack_enable_backend_style_modifications" value="1"<?php checked('1', get_option('zh_cn_language_pack_enable_backend_style_modifications')); ?> /> 对后台样式进行优化。</label>
                <br />
                <span class="description">
                    优化控制板以及登录页面的字体样式。此操作不会影响到您的博客前台。
                </span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">中国视频网站视频自动嵌入</th>
            <td>
                <label for="zh_cn_language_pack_enable_chinese_fake_oembed"><input type="checkbox" id="zh_cn_language_pack_enable_chinese_fake_oembed" name="zh_cn_language_pack_enable_chinese_fake_oembed" value="1"<?php checked('1', get_option('zh_cn_language_pack_enable_chinese_fake_oembed')); ?> /> 自动从 URL 嵌入中国视频网站上的视频。</label>
                <br />
                <span class="description">
                    WordPress 核心程序的 oEmbed 功能无法嵌入中国视频网站的视频，因为中国视频网站大多不提供 oEmbed 服务。本选项启用后，程序将采用固定方式，在显示文章时自动将 URL 替换成相应的 Flash 视频嵌入代码。用法、支持的站点、样例 URL 格式及其嵌入大小，请见页面上方“帮助”选项卡。（试验功能。需要 <code>preg_replace()</code> 函数。在视频网站做出调整时可能出现问题。功能按照视频网站提供的嵌入代码编写，可能破坏您页面的 HTML / XHTML 标准性，可能破坏页面宽度。请慎用。）
                </span>
            </td>
        </tr>
    </table>
    
    <p class="submit">
        <input type="submit" class="button-primary" value="保存设置" />
    </p>
</form>

<h3 class="title">翻译纠错、使用中文提交 bug、简单免费技术支持</h3>
<p>请点击页面上方的“帮助”以获取联系信息。</p>

</div><?php
}

function zh_cn_language_pack_substitute_chinese_video_urls( $content ) {
    $schema = array('/^<p>http:\/\/v\.youku\.com\/v_show\/id_([a-z0-9_=]+)\.html((\?|#|&).*?)*?\s*<\/p>\s*$/im' => '<p><embed src="http://player.youku.com/player.php/sid/$1/v.swf" quality="high" width="480" height="400" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed></p>',
                    '/^<p>http:\/\/www\.56\.com\/[a-z0-9]+\/v_([a-z0-9_]+)\.html((\?|#|&).*?)*?\s*<\/p>\s*$/im' => '<p><embed src="http://player.56.com/v_$1.swf" type="application/x-shockwave-flash" width="480" height="395" allowNetworking="all" allowScriptAccess="always"></embed></p>',
                    '/^<p>http:\/\/www\.tudou\.com\/programs\/view\/([a-z0-9_]+)[\/]?((\?|#|&).*?)*?\s*<\/p>\s*$/im' => '<p><embed src="http://www.tudou.com/v/$1/v.swf" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque" width="480" height="400"></embed></p>');

    foreach ( $schema as $pattern => $replacement ) {
        $content = preg_replace( $pattern, $replacement, $content );
    }
    
    return $content;
}
    

function zh_cn_language_pack_backend_style_modify() {
    echo <<<EOF
<style type="text/css" media="screen">
    body { font: 13px "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif,"新宋体","宋体"; }
    #adminmenu .wp-submenu a { font-size: 11.5px; }
    #adminmenu a.menu-top { font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif,"Microsoft YaHei Bold","Microsoft YaHei","微软雅黑","WenQuanYi Zen Hei","文泉驿正黑","WenQuanYi Micro Hei","文泉驿微米黑","黑体"; }
    h1#site-heading span { font-family:  Georgia,"Times New Roman","Bitstream Charter",Times,serif,"Microsoft YaHei Bold","Microsoft YaHei","微软雅黑","WenQuanYi Zen Hei","文泉驿正黑","WenQuanYi Micro Hei","文泉驿微米黑","黑体"; }
    .form-table td { font-size: 12px; }
    #footer, #footer a, #footer p { font-size: 13px; font-style: normal; }
    #screen-meta a.show-settings { font-size: 12px; }
    #favorite-actions a { font-size: 12px; }
    .postbox p, .postbox ul, .postbox ol, .postbox blockquote, #wp-version-message { font-size: 13px; }
    #dashboard_right_now p.sub { font-size: 14px; font-style: normal; }
    .row-actions { font-size: 12px; }
    .widefat td, .widefat th, .widefat td p, .widefat td ol, .widefat td ul { font-size: 13px; }
    .submit input, .button, input.button, .button-primary, input.button-primary, .button-secondary, input.button-secondary, .button-highlighted, input.button-highlighted, #postcustomstuff .submit input { font-size: 12px !important; }
    .subsubsub { font-size: 12px; }
    #wpcontent select { font-size: 12px; }
    form.upgrade .hint { font-style: normal; font-weight: bold; font-size: 100% }
    #poststuff .inside, #poststuff .inside p { font-size: 12px; line-height: 112% }
    .tablenav .displaying-num { font-size: 12px; font-style: normal; }
    p.help, p.description, span.description, .form-wrap { font-size: 13px; }
    .widget .widget-inside, .widget .widget-description { font-size: 12px; }
    .appearance_page_custom-header #upload-form p label { font-size: 12px; }
    .wp_themeSkin .mceMenu span.mceText, .wp_themeSkin .mceMenu .mcePreview { font-size: 12px; }
    form .forgetmenot label { font-size: 12px; }
    .wrap h2 { font: normal 24px/35px Georgia,"Times New Roman","Bitstream Charter",Times,serif,"Microsoft YaHei Bold","Microsoft YaHei","微软雅黑","WenQuanYi Zen Hei","文泉驿正黑","WenQuanYi Micro Hei","文泉驿微米黑","黑体"; }
    .howto { font-style: normal; }
    p.help, p.description, span.description, .form-wrap p { font-style: normal; }
    .inline-edit-row fieldset span.title, .inline-edit-row fieldset span.checkbox-title { font-style: normal; }
    #edithead .inside, #edithead .inside input { font-size: 12px; }
    h2 .nav-tab { font: normal 24px/35px Georgia,"Times New Roman","Bitstream Charter",Times,serif,"Microsoft YaHei Bold","Microsoft YaHei","微软雅黑","WenQuanYi Zen Hei","文泉驿正黑","WenQuanYi Micro Hei","文泉驿微米黑","黑体"; }
    em { font-style: normal; }
    .menu-name-label span, .auto-add-pages label { font-size: 12px; }
    #dashboard_quick_press #media-buttons { font-size: 12px; }
    p.install-help { font-style: normal; }
    .inline-edit-row fieldset ul.cat-checklist label, .inline-edit-row .catshow, .inline-edit-row .cathide, .inline-edit-row #bulk-titles div { font-size: 12px; }
    #the-comment-list .comment-item p.row-actions { font-size: 12px; }
    #utc-time, #local-time { font-style: normal; }
</style>

EOF;
}

function zh_cn_language_pack_login_screen_style_modify() {
    echo <<<EOF
<style type="text/css" media="screen">
    body { font: 13px "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif,"新宋体","宋体"; }
    .submit input, .button, input.button, .button-primary, input.button-primary, .button-secondary, input.button-secondary, .button-highlighted, input.button-highlighted, #postcustomstuff .submit input { font-size: 12px !important; }
</style>

EOF;
}

add_action( 'admin_init', 'zh_cn_language_pack_backend_register_settings' );

if ( is_admin() ) {
    add_action( 'admin_menu', 'zh_cn_language_pack_backend_create_menu' );
    add_action( 'admin_head-settings_page_zh-cn-language-pack-settings', 'zh_cn_language_pack_contextual_help' );
}

if ( get_option('zh_cn_language_pack_enable_backend_style_modifications') == 1 ) {
    add_action( 'admin_head', 'zh_cn_language_pack_backend_style_modify' );
    add_action( 'login_head', 'zh_cn_language_pack_login_screen_style_modify' );
}

if ( get_option('zh_cn_language_pack_enable_chinese_fake_oembed') == 1 ) {
    add_filter( 'the_content', 'zh_cn_language_pack_substitute_chinese_video_urls' );
}

?>
