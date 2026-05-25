<?php
namespace TypechoPlugin\Weiyu;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Db;
use Typecho\Plugin as TypechoPlugin;
use Helper;

/**
 *微语
 *
 * @package weiyu
 * @author Lin.
 * @link https://linyu.live
 * @version 1.0.0
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

class Plugin implements PluginInterface
{
    public static function activate()
    {
        $db = Db::get();
        $prefix = $db->getPrefix();

        $posts = "CREATE TABLE IF NOT EXISTS {$prefix}weiyu_posts (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `authorId` int(10) unsigned DEFAULT '0',
            `authorName` varchar(200) DEFAULT '',
            `authorMail` varchar(200) DEFAULT '',
            `authorUrl` varchar(200) DEFAULT '',
            `title` varchar(200) DEFAULT '',
            `content` text,
            `images` text,
            `music_cover` varchar(500) DEFAULT '',
            `music_title` varchar(200) DEFAULT '',
            `music_artist` varchar(200) DEFAULT '',
            `music_link` varchar(500) DEFAULT '',
            `video_link` varchar(500) DEFAULT '',
            `video_cover` varchar(500) DEFAULT '',
            `likes` int(10) unsigned DEFAULT '0',
            `commentsNum` int(10) unsigned DEFAULT '0',
            `status` tinyint(1) unsigned DEFAULT '1',
            `created` int(10) unsigned DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $comments = "CREATE TABLE IF NOT EXISTS {$prefix}weiyu_comments (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` int(10) unsigned DEFAULT '0',
            `parent_id` int(10) unsigned DEFAULT '0',
            `author` varchar(200) DEFAULT '',
            `mail` varchar(200) DEFAULT '',
            `url` varchar(200) DEFAULT '',
            `ip` varchar(64) DEFAULT '',
            `content` text,
            `created` int(10) unsigned DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `post_id` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $likes = "CREATE TABLE IF NOT EXISTS {$prefix}weiyu_likes (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` int(10) unsigned DEFAULT '0',
            `user_id` int(10) unsigned DEFAULT '0',
            `guest_name` varchar(200) DEFAULT '',
            `guest_mail` varchar(200) DEFAULT '',
            `ip` varchar(64) DEFAULT '',
            `created` int(10) unsigned DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `post_id` (`post_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $db->query($posts);
        $db->query($comments);
        $db->query($likes);

        // 兼容旧表升级
        $cols = $db->fetchAll($db->query("SHOW COLUMNS FROM {$prefix}weiyu_posts"));
        $colNames = array_column($cols, 'Field');
        $upgrades = [
            'authorName'   => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `authorName` varchar(200) DEFAULT ''",
            'authorMail'   => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `authorMail` varchar(200) DEFAULT ''",
            'authorUrl'    => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `authorUrl` varchar(200) DEFAULT ''",
            'status'       => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `status` tinyint(1) unsigned DEFAULT '1'",
            'music_cover'  => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `music_cover` varchar(500) DEFAULT ''",
            'music_title'  => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `music_title` varchar(200) DEFAULT ''",
            'music_artist' => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `music_artist` varchar(200) DEFAULT ''",
            'music_link'   => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `music_link` varchar(500) DEFAULT ''",
            'video_link'   => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `video_link` varchar(500) DEFAULT ''",
            'video_cover'  => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `video_cover` varchar(500) DEFAULT ''",
            'title'        => "ALTER TABLE {$prefix}weiyu_posts ADD COLUMN `title` varchar(200) DEFAULT ''",
        ];
        foreach ($upgrades as $col => $sql) {
            if (!in_array($col, $colNames)) $db->query($sql);
        }

        $likeCols = $db->fetchAll($db->query("SHOW COLUMNS FROM {$prefix}weiyu_likes"));
        $likeColNames = array_column($likeCols, 'Field');
        if (!in_array('guest_name', $likeColNames)) {
            $db->query("ALTER TABLE {$prefix}weiyu_likes ADD COLUMN `guest_name` varchar(200) DEFAULT ''");
        }
        if (!in_array('guest_mail', $likeColNames)) {
            $db->query("ALTER TABLE {$prefix}weiyu_likes ADD COLUMN `guest_mail` varchar(200) DEFAULT ''");
        }

        Helper::addRoute('weiyu_index', '/weiyu', '\TypechoPlugin\Weiyu\Page', 'render');
        Helper::addRoute('weiyu_page', '/weiyu/[page:digital]', '\TypechoPlugin\Weiyu\Page', 'render');
        Helper::addRoute('weiyu_action', '/weiyu/action', '\TypechoPlugin\Weiyu\Action', 'action');

        Helper::addPanel(3, 'Weiyu/admin.php', _t('微语管理'), _t('管理微语'), 'administrator');

        TypechoPlugin::factory('Widget_Abstract_Contents')->contentEx = ['TypechoPlugin\Weiyu\Plugin', 'shortcode'];
        TypechoPlugin::factory('Widget_Abstract_Contents')->excerptEx = ['TypechoPlugin\Weiyu\Plugin', 'shortcode'];
        TypechoPlugin::factory('Widget_Archive')->contentHandle = ['TypechoPlugin\Weiyu\Plugin', 'shortcodeHandle'];

        if (file_exists(__DIR__ . '/Action.php')) require_once __DIR__ . '/Action.php';
        if (file_exists(__DIR__ . '/Page.php')) require_once __DIR__ . '/Page.php';

        return _t('微语插件已激活，短代码：[weiyu]');
    }

    public static function deactivate()
    {
        Helper::removeRoute('weiyu_index');
        Helper::removeRoute('weiyu_page');
        Helper::removeRoute('weiyu_action');
        Helper::removePanel(3, 'Weiyu/admin.php');
        return _t('微语插件已禁用');
    }

    public static function uninstall()
    {
        $db = Db::get();
        $prefix = $db->getPrefix();
        $db->query("DROP TABLE IF EXISTS {$prefix}weiyu_posts");
        $db->query("DROP TABLE IF EXISTS {$prefix}weiyu_comments");
        $db->query("DROP TABLE IF EXISTS {$prefix}weiyu_likes");
        return _t('微语插件已卸载');
    }

    public static function config(Form $form)
    {
        $cover = new Form\Element\Text('cover', NULL, '', _t('顶图链接'));
        $form->addInput($cover);

        $coverVideo = new Form\Element\Text('coverVideo', NULL, '', _t('顶图视频链接 (mp4 格式，留空则不显示视频)'));
        $form->addInput($coverVideo);

        $videoPlayMode = new Form\Element\Radio('videoPlayMode', [
            'click' => _t('点击播放'),
            'refresh' => _t('刷新页面播放一次')
        ], 'click', _t('视频播放模式'));
        $form->addInput($videoPlayMode);

        $videoFrame = new Form\Element\Radio('videoFrame', [
            'first' => _t('第一帧'),
            'last' => _t('最后一帧')
        ], 'first', _t('视频播放结束后显示的画面'));
        $form->addInput($videoFrame);

        $allowGuestPost = new Form\Element\Radio('allowGuestPost', ['0' => _t('否'), '1' => _t('是')], '1', _t('允许游客发帖'));
        $form->addInput($allowGuestPost);
    }

    public static function personalConfig(Form $form) {}

    public static function getToken()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        if (empty($_SESSION['weiyu_token'])) {
            $_SESSION['weiyu_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['weiyu_token'];
    }

    public static function shortcode($content, $widget)
    {
        if (strpos($content, '[weiyu]') !== false) {
            ob_start();
            include __DIR__ . '/widget-weiyu.php';
            $html = ob_get_clean();
            $content = str_replace('[weiyu]', $html, $content);
        }
        return $content;
    }

    public static function shortcodeHandle($content, $widget = null)
    {
        if (is_null($content)) return;
        if (strpos($content, '[weiyu]') !== false) {
            ob_start();
            include __DIR__ . '/widget-weiyu.php';
            $html = ob_get_clean();
            $content = str_replace('[weiyu]', $html, $content);
        }
        return $content;
    }

    public static function getAvatar($mail)
    {
        $mail = strtolower(trim($mail));
        if (preg_match('/^(\d+)@qq\.com$/', $mail, $m)) {
            return 'https://q.qlogo.cn/headimg_dl?dst_uin=' . $m[1] . '&spec=100';
        }
        if (preg_match('/^(\d+)@(foxmail|qq)\.com$/', $mail, $m)) {
            return 'https://q.qlogo.cn/headimg_dl?dst_uin=' . $m[1] . '&spec=100';
        }
        return 'https://gravatar.loli.net/avatar/' . md5($mail) . '?s=200&d=identicon';
    }
}
