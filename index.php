<?php
require __DIR__ . '/bootstrap.php';

// ===== wendang: 先定义目录，后面 POST 处理要用 =====
$wendangDir = __DIR__ . '/wendang/data';
if (!is_dir($wendangDir)) mkdir($wendangDir, 0777, true);

// 读取点击数据
$featureClicks = [];
$siteClicks = [];
$fcFile = $wendangDir . '/feature_clicks.json';
$scFile = $wendangDir . '/site_clicks.json';
if (file_exists($fcFile)) { $d = json_decode(file_get_contents($fcFile), true); if (is_array($d)) $featureClicks = $d; }
if (file_exists($scFile)) { $d = json_decode(file_get_contents($scFile), true); if (is_array($d)) $siteClicks = $d; }

// ===== Handle recording clicks =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'record_click') {
    header('Content-Type: application/json');
    $url = $_POST['url'] ?? '';
    $type = $_POST['type'] ?? 'site';
    if (empty($url)) { echo json_encode(['success' => false]); exit; }

    $clickFile = $wendangDir . '/' . $type . '_clicks.json';
    $clicks = [];
    if (file_exists($clickFile)) {
        $d = json_decode(file_get_contents($clickFile), true);
        if (is_array($d)) $clicks = $d;
    }
    $clicks[$url] = ($clicks[$url] ?? 0) + 1;
    file_put_contents($clickFile, json_encode($clicks, JSON_UNESCAPED_UNICODE), LOCK_EX);
    echo json_encode(['success' => true, 'clicks' => $clicks[$url]]);
    exit;
}

// ===== Handle fetching site info =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'fetch_site_info') {
    header('Content-Type: application/json');
    $url = $_POST['url'] ?? '';
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'error' => '无效URL']); exit;
    }

    $title = ''; $desc = '';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $html = curl_exec($ch);
    curl_close($ch);

    if ($html) {
        if (preg_match('/<title[^>]*>([^<]*)<\/title>/si', $html, $m)) {
            $title = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
        // 用 chr 拼接正则，彻底避开引号地狱
        $q = chr(34) . chr(39); // '"\'
        $p1 = '/<meta[^>]+name=[' . $q . ']description[' . $q . '][^>]+content=[' . $q . ']([^' . $q . ']+)[' . $q . '][^>]*>/si';
        $p2 = '/<meta[^>]+content=[' . $q . ']([^' . $q . ']+)[' . $q . '][^>]+name=[' . $q . ']description[' . $q . '][^>]*>/si';
        if (preg_match($p1, $html, $m) || preg_match($p2, $html, $m)) {
            $desc = trim(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }
    }

    echo json_encode(['success' => true, 'title' => $title, 'desc' => $desc]);
    exit;
}

// ===== Handle adding tool via POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_tool') {
    header('Content-Type: application/json');
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '🔧');
    $cat = trim($_POST['category'] ?? 'tool');
    $code = $_POST['code'] ?? '';
    $targetDir = trim($_POST['target_dir'] ?? 'generator');
    if ($targetDir !== 'generator' && $targetDir !== 'feature') $targetDir = 'generator';
    if (empty($name)) { echo json_encode(['success' => false, 'error' => '名称不能为空']); exit; }
    if (empty($code)) { echo json_encode(['success' => false, 'error' => '源代码不能为空']); exit; }

    $userDir = preg_replace('/[^a-zA-Z0-9_-]/', '_', trim($_POST['dir_name'] ?? ''));
    $dirName = $userDir ?: ('u_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $name));
    $dirName = trim($dirName, '_');
    if (strlen($dirName) < 2) $dirName = 'u_tool_' . time();
    $baseDirName = $dirName; $counter = 1;
    while (is_dir(__DIR__ . '/' . $targetDir . '/' . $dirName)) { $dirName = $baseDirName . '_' . $counter++; }

    $dirPath = __DIR__ . '/' . $targetDir . '/' . $dirName;
    if (!mkdir($dirPath, 0755, true)) { echo json_encode(['success' => false, 'error' => '创建目录失败']); exit; }

    $fileContent = "<?php\n";
    $fileContent .= "// @name " . str_replace("\n", "", $name) . "\n";
    $fileContent .= "// @icon " . str_replace("\n", "", $icon) . "\n";
    $fileContent .= "// @category " . str_replace("\n", "", $cat) . "\n";
    $fileContent .= "// @desc 自定义工具\n";
    $fileContent .= "?>\n" . $code;

    if (file_put_contents($dirPath . '/index.php', $fileContent) === false) {
        echo json_encode(['success' => false, 'error' => '写入文件失败']); exit;
    }
    echo json_encode(['success' => true, 'dir' => $dirName]); exit;
}

// ===== Handle deleting tool via POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_tool') {
    header('Content-Type: application/json');
    $dir = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['dir'] ?? '');
    if (empty($dir) || strpos($dir, 'u_') !== 0) {
        echo json_encode(['success' => false, 'error' => '无权删除']); exit;
    }
    $dirPath = __DIR__ . '/generator/' . $dir;
    if (!is_dir($dirPath)) { echo json_encode(['success' => false, 'error' => '工具不存在']); exit; }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $fileinfo) { $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink'); $todo($fileinfo->getRealPath()); }
    rmdir($dirPath);
    echo json_encode(['success' => true]); exit;
}

// ===== Handle editing tool via POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_tool') {
    header('Content-Type: application/json');
    $dir = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['dir'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '🔧');
    $code = $_POST['code'] ?? '';
    if (empty($dir) || strpos($dir, 'u_') !== 0) { echo json_encode(['success' => false, 'error' => '无权修改']); exit; }
    if (empty($name)) { echo json_encode(['success' => false, 'error' => '名称不能为空']); exit; }
    $file = __DIR__ . '/generator/' . $dir . '/index.php';
    if (!file_exists($file)) { echo json_encode(['success' => false, 'error' => '文件不存在']); exit; }
    $old = file_get_contents($file);
    preg_match('/@category\s+(.+)/', $old, $m);
    $cat = $m ? trim($m[1]) : 'tool';
    $content = "<?php\n// @name " . str_replace("\n", "", $name) . "\n// @icon " . str_replace("\n", "", $icon) . "\n// @category " . str_replace("\n", "", $cat) . "\n// @desc 自定义工具\n?>\n" . $code;
    if (file_put_contents($file, $content) === false) { echo json_encode(['success' => false, 'error' => '写入失败']); exit; }
    echo json_encode(['success' => true]); exit;
}

// ===== Scan tools for change detection =====
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'scan_tools') {
    header('Content-Type: application/json');
    $gens = scanGenerators(__DIR__ . '/generator');
    echo json_encode(['hash' => md5(json_encode($gens)), 'count' => count($gens)]);
    exit;
}

$funcs = scanModules();

function scanGenerators($baseDir) {
    $gens = [];
    if (!is_dir($baseDir)) return $gens;
    foreach (glob($baseDir . '/*', GLOB_ONLYDIR) as $dir) {
        $name = basename($dir);
        if ($name === 'data') continue;
        $file = $dir . '/index.php';
        if (!file_exists($file)) continue;
        $content = file_get_contents($file);
        $config = ['dir' => $name];
        if (preg_match('/@name\s+(.+)/', $content, $m)) $config['name'] = trim($m[1]);
        if (preg_match('/@desc\s+(.+)/', $content, $m)) $config['desc'] = trim($m[1]);
        if (preg_match('/@icon\s+(.+)/', $content, $m)) $config['icon'] = trim($m[1]);
        if (preg_match('/@category\s+(.+)/', $content, $m)) $config['cat'] = trim($m[1]);
        if (empty($config['name'])) $config['name'] = $name;
        if (empty($config['desc'])) $config['desc'] = '';
        if (empty($config['icon'])) $config['icon'] = '⚙️';
        if (empty($config['cat'])) $config['cat'] = 'tool';
        $config['type'] = 'generator';
        $gens[] = $config;
    }
    return $gens;
}
$gens = scanGenerators(__DIR__ . '/generator');

$modules = [];
foreach ($funcs as $f) {
    if (($f['type'] ?? '') === 'video') {
        $modules[] = ['name'=>$f['name'],'desc'=>$f['desc'],'icon'=>$f['icon'],'cat'=>'media','type'=>'module','dir'=>$f['dir'],'url'=>$f['dir'].'/'];
    }
}

$allTools = array_merge($gens, $modules);

// Auto-collect categories from generators that aren't in catsDef
foreach ($gens as $g) {
    $ck = $g['cat'] ?? 'other';
    if (!isset($catsDef[$ck])) {
        $catsDef[$ck] = ['name' => $ck, 'order' => 99];
    }
}

$catsDef = [
    'hot'=>['name'=>'热门应用','order'=>1], 'feature'=>['name'=>'特色应用','order'=>2],
    'query'=>['name'=>'查询应用','order'=>3], 'smart'=>['name'=>'智能应用','order'=>4],
    'doc'=>['name'=>'文档应用','order'=>5], 'image'=>['name'=>'图片应用','order'=>6],
    'text'=>['name'=>'文字应用','order'=>7], 'encode'=>['name'=>'编码工具','order'=>8],
    'calc'=>['name'=>'计算工具','order'=>9], 'life'=>['name'=>'生活助手','order'=>10],
    'tool'=>['name'=>'便捷工具','order'=>11], 'security'=>['name'=>'安全工具','order'=>12],
    'random'=>['name'=>'趣味工具','order'=>13], 'color'=>['name'=>'色彩工具','order'=>14],
    'convert'=>['name'=>'单位转换','order'=>15], 'device'=>['name'=>'设备工具','order'=>16],
    'media'=>['name'=>'媒体工具','order'=>17], 'dev'=>['name'=>'开发工具','order'=>18],
    'other'=>['name'=>'其他工具','order'=>19],
];

$apiSitesDefault = [
    ['name'=>'月影Api','url'=>'https://linyu.live/gongju/api'],
    ['name'=>'糖豆子','url'=>'http://api.tangdouz.com/'],
    ['name'=>'API Store','url'=>'https://apis.jxcxin.cn/'],
    ['name'=>'Abeim','url'=>'http://res.abeim.cn/api/'],
    ['name'=>'筱初API','url'=>'https://api.xcboke.cn/'],
    ['name'=>'零艺客','url'=>'https://api.lykep.com/'],
    ['name'=>'Uomg','url'=>'https://api.uomg.com/'],
    ['name'=>'易连数据','url'=>'https://www.yuanxiapi.cn/'],
    ['name'=>'苏画API','url'=>'https://api.qemao.com/'],
    ['name'=>'流星云','url'=>'https://liuxingw.com/api/'],
    ['name'=>'恋酱API','url'=>'http://api.cmvip.cn/'],
    ['name'=>'问情免费','url'=>'https://free.wqwlkj.cn/'],
    ['name'=>'桑帛云','url'=>'https://api.lolimi.cn/'],
    ['name'=>'山河API','url'=>'https://api.shanhe.kim/'],
    ['name'=>'希速云','url'=>'https://api.sdbj.top/'],
    ['name'=>'小小API','url'=>'https://xxapi.cn/'],
];

// 从 wendang 读取特色功能数据
$featureTools = [];
$featuresFile = $wendangDir . '/features.json';
if (file_exists($featuresFile)) {
    $d = json_decode(file_get_contents($featuresFile), true);
    if (is_array($d)) $featureTools = $d;
}

function wRead($dir, $key, $default) {
    $file = $dir . '/' . $key . '.json';
    $useDefault = false;
    if (!file_exists($file)) {
        $useDefault = true;
    } else {
        $content = file_get_contents($file);
        if ($content === false || strlen(trim($content)) === 0) {
            $useDefault = true;
        } else {
            $d = json_decode($content, true);
            if ($d !== null && is_array($d)) return $d;
            $useDefault = true;
        }
    }
    if ($useDefault) {
        $json = json_encode($default, JSON_UNESCAPED_UNICODE);
        @file_put_contents($file, $json, LOCK_EX);
    }
    return is_array($default) ? $default : (array)$default;
}

$wendangApiSites = wRead($wendangDir, 'api_sites', $apiSitesDefault);

// 读取 api/data/apis.json 作为额外 API 数据源
$apisJsonFile = __DIR__ . '/data/apis.json';
$apisJsonData = [];
if (file_exists($apisJsonFile)) {
    $d = json_decode(file_get_contents($apisJsonFile), true);
    if (is_array($d)) $apisJsonData = $d;
}

// 合并 apis.json 到 wendangApiSites（去重）
if ($apisJsonData) {
    $existingUrls = array_column($wendangApiSites, 'url');
    foreach ($apisJsonData as $api) {
        if (!in_array($api['url'] ?? '', $existingUrls)) {
            $wendangApiSites[] = $api;
        }
    }
}

$wendangSites    = wRead($wendangDir, 'sites', []);
$wendangSiteCats = wRead($wendangDir, 'site_cats', new stdClass());
$wendangFavs     = wRead($wendangDir, 'favs', new stdClass());
$wendangFeatureCats = wRead($wendangDir, 'feature_cats', new stdClass());
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<title>青空工具箱</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;-webkit-touch-callout:none}
:root{
  --primary:#4099FF;--primary-light:#e8f4ff;--bg:#FFFFFF;--card:#FFFFFF;--text:#333333;--muted:#999999;
  --border:#e8e8e8;--shadow-sm:0 1px 4px rgba(0,0,0,.04);--radius-sm:10px;--radius-md:14px;--radius-lg:18px;
}
html,body{height:100%;overflow:hidden;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;background:#FFFFFF;color:var(--text);font-size:16px;-webkit-font-smoothing:antialiased}
input,textarea,[contenteditable]{-webkit-user-select:auto!important;user-select:auto!important}
img{-webkit-touch-callout:none!important;-webkit-user-select:none!important;pointer-events:auto}
#viewport{width:100%;height:100%;overflow:hidden;position:relative}
#pages{display:flex;width:400%;height:100%;transition:transform .35s cubic-bezier(.32,.72,0,1)}
.page{width:25%;height:100%;overflow-y:auto;overflow-x:hidden;position:relative;-webkit-overflow-scrolling:touch;padding-bottom:calc(64px + env(safe-area-inset-bottom))
}

/* Topbar */
.topbar{position:sticky;top:0;z-index:50;padding:12px 16px;background:#FFFFFF;border-bottom:1px solid #f0f0f0}
.search-row{display:flex;align-items:center;gap:10px;justify-content:center}
.search-box{flex:1;max-width:600px;background:#F0F4FA;border-radius:12px;padding:8px 12px;display:flex;align-items:center;gap:10px;transition:all .2s;border:none}
.search-box .avatar{margin-left:auto;width:32px;height:32px;border:2px solid #e0e0e0;flex-shrink:0}
.search-box:focus-within{box-shadow:0 0 0 3px rgba(64,153,255,.08)}
.search-box input{flex:1;border:none;background:transparent;font-size:16px;outline:none;color:var(--text);font-weight:500}
.search-box input::placeholder{color:#bbb;font-weight:400}
.search-box svg{width:18px;height:18px;color:#bbb;flex-shrink:0}
.action-btn{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;color:var(--muted);background:#F0F2F5;transition:all .15s}
.action-btn:active{transform:scale(.92);background:#e0e2e5}
.action-btn svg{width:20px;height:20px}
.avatar{width:32px;height:32px;border-radius:50%;overflow:hidden;cursor:pointer;flex-shrink:0;box-shadow:none;border:2px solid #fff;background:#f3f4f8;position:relative;display:flex;align-items:center;justify-content:center;z-index:10}
.avatar img{width:100%;height:100%;object-fit:cover;display:block;pointer-events:none}
.search-box .avatar{width:28px;height:28px;border:2px solid #e0e0e0}
.login-dot{position:absolute;bottom:-3px;right:-3px;width:10px;height:10px;border-radius:50%;background:#10b981;border:2px solid #fff;display:none;z-index:2}
.login-dot.show{display:block}

/* Tool pills */
.tool-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.tool-pill{display:flex;flex-direction:row;align-items:center;gap:6px;cursor:pointer;padding:8px 12px;border-radius:8px;background:#fff;transition:all .15s;border:1px solid #eee;text-align:left;min-width:0}
.tool-pill:active{transform:scale(.96);background:#f8f9fa}
.tool-pill-icon{width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;overflow:hidden}
.tool-pill-icon svg,.tool-pill-icon img{width:20px;height:20px;object-fit:contain}
.tool-pill-name{font-size:13px;font-weight:500;color:var(--text);line-height:1.3;flex:1;min-width:0;word-break:break-all;}

/* Category */
.cat-section{background:transparent;margin:0 12px 12px;border-radius:0;overflow:visible;box-shadow:none;border:none}
.cat-header{padding:12px 0;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;transition:background .15s}
.cat-header:active{background:transparent}
.cat-title{font-size:16px;font-weight:700;color:var(--text);display:flex;align-items:center;gap:8px;letter-spacing:-.2px}
.cat-badge{font-size:12px;color:var(--primary);font-weight:600;margin-left:6px;background:var(--primary-light);padding:2px 8px;border-radius:10px}
.cat-arrow{width:20px;height:20px;color:#ccc;transition:transform .3s cubic-bezier(.4,0,.2,1);flex-shrink:0}
.cat-arrow.open{transform:rotate(180deg)}
.cat-body{display:none;padding:0 0 8px}
.cat-body.open{display:block;animation:fadeIn .25s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:none}}

/* API grid */
.api-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:10px 12px}
.api-grid-item{background:transparent;border-radius:0;padding:14px 8px;display:flex;flex-direction:column;align-items:center;gap:8px;text-decoration:none;color:inherit;transition:all .15s;border:none;border-bottom:1px solid #f0f0f0;box-shadow:none;cursor:pointer;text-align:center}
.api-grid-item:active{transform:scale(.96);background:#fafbfc}
.api-grid-item .favicon{width:44px;height:44px;border-radius:12px;flex-shrink:0;background:#f3f4f8;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.api-grid-item .favicon img{width:100%;height:100%;object-fit:cover;display:block}
.api-grid-item .name{font-size:12px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%}

/* API list */
.api-list{padding:10px 12px}
.api-card{background:transparent;border-radius:0;padding:14px 16px;margin-bottom:0;display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;transition:all .15s;border:none;border-bottom:1px solid #f0f0f0;box-shadow:none}
.api-card:active{background:#f8f9fa}
.api-card .favicon{width:40px;height:40px;border-radius:10px;flex-shrink:0;background:#f3f4f8;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.api-card .favicon img{width:100%;height:100%;object-fit:cover;display:block}
.api-card .info{flex:1;min-width:0}
.api-card .name{font-size:15px;font-weight:700;color:var(--text);letter-spacing:-.2px}
.api-card .url{font-size:12px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:3px;font-weight:500}
.api-card .arrow{width:18px;height:18px;color:#d1d5db;flex-shrink:0}

.fav-letter{position:absolute;inset:0;width:100%;height:100%;border-radius:inherit;z-index:1;object-fit:cover}

/* Feature & Repo specific layout */
#featureContent .site-item .info,
#siteContent .site-item .info { flex:1;min-width:0;display:flex;align-items:center;gap:8px }
#featureContent .site-item .name,
#siteContent .site-item .name { font-size:14px;font-weight:600;color:#333;flex:none;width:7em;min-width:7em;text-align:left;line-height:1.4;max-height:2.8em;overflow:hidden;word-break:break-all }
#featureContent .site-item .click-count,
#siteContent .site-item .click-count { font-size:11px;color:#999;font-weight:500;white-space:nowrap;flex-shrink:0 }
#featureContent .site-item .sub,
#siteContent .site-item .sub { display:none }

/* View toggle */
.view-toggle-btn{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;cursor:pointer;border:1px solid #eee;background:#fff;color:var(--primary);transition:all .15s;flex-shrink:0}
.view-toggle-btn:active{transform:scale(.92)}
.view-toggle-btn svg{width:18px;height:18px}

/* Site */
.site-layout{display:flex;height:calc(100vh - 110px);position:relative;background:#FFFFFF}
.sidebar{width:90px;min-width:90px;background:#FFFFFF;overflow-y:auto;padding:8px 0;border-right:1px solid #eee}
.sidebar-item{padding:14px 8px;font-size:12px;font-weight:600;color:#999;cursor:pointer;border-left:3px solid transparent;transition:all .15s;text-align:center;line-height:1.3}
.sidebar-item.active{color:var(--primary);background:linear-gradient(90deg,var(--primary-light) 0%,rgba(232,244,255,0.5) 60%,transparent 100%);border-left-color:var(--primary)}
.sidebar-item:active{background:#f5f5f5}
.sidebar-sub-wrap{display:none;background:#f8f9fc}
.sidebar-sub-wrap.show{display:block;animation:fadeIn .2s ease}
.sidebar-sub{padding:10px 8px 10px 20px;font-size:11px;color:#999;cursor:pointer;transition:all .15s;position:relative}
.sidebar-sub.active{color:var(--primary);background:linear-gradient(90deg,var(--primary-light) 0%,rgba(232,244,255,0.4) 70%,transparent 100%);font-weight:700}
.sidebar-sub::before{content:"";position:absolute;left:12px;top:50%;transform:translateY(-50%);width:4px;height:4px;border-radius:50%;background:currentColor;opacity:.4}
.site-content{flex:1;overflow-y:auto;padding:16px 0 10px;background:#FFFFFF}
.site-item{background:transparent;border-radius:0;padding:14px 16px;margin-bottom:0;display:flex;align-items:center;gap:12px;text-decoration:none;color:inherit;transition:all .15s;border:none;border-bottom:1px solid #f0f0f0;box-shadow:none}
.site-item:active{background:#f8f9fa}
.site-item .favicon{width:40px;height:40px;border-radius:10px;flex-shrink:0;background:#f3f4f8;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative}
.site-item .favicon img{width:100%;height:100%;object-fit:cover;display:block}
.site-item .info{flex:1;min-width:0}
.site-item .name{font-size:14px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text)}
.site-item .sub{font-size:12px;color:var(--muted);margin-top:3px;font-weight:500}

/* Repo/Fav Tabs */
.repo-tabs{display:flex;gap:8px;padding:8px 16px;background:#FFFFFF;border-bottom:1px solid #f0f0f0}
.repo-tab{flex:1;text-align:center;padding:8px 0;font-size:13px;font-weight:600;color:#666;border-radius:8px;cursor:pointer;transition:all .15s;position:relative;overflow:hidden;background:transparent}
.repo-tab::after{content:"";position:absolute;inset:0;background:var(--primary);opacity:0;transition:opacity .15s;border-radius:8px;z-index:-1}
.repo-tab:active::after{opacity:.15}
.repo-tab:active{color:var(--primary);transform:scale(.98)}
.repo-tab.active{color:var(--primary);font-weight:700;background:transparent}

/* FAB */
.fab{position:fixed;bottom:calc(80px + env(safe-area-inset-bottom));right:16px;width:48px;height:48px;border-radius:12px;background:var(--primary);color:#fff;border:none;cursor:pointer;display:none;align-items:center;justify-content:center;z-index:50;font-size:24px;font-weight:300;box-shadow:0 4px 14px rgba(64,153,255,.35);transition:transform .2s}
.fab:active{transform:scale(.92)}
.fab.show{display:flex}

/* Bottom sheet */
.bottom-sheet-overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:200;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(2px)}
.bottom-sheet-overlay.show{opacity:1;pointer-events:auto}
.bottom-sheet{position:fixed;bottom:0;left:0;right:0;background:#fff;border-radius:22px 22px 0 0;z-index:201;transform:translateY(100%);transition:transform .35s cubic-bezier(.32,.72,0,1);padding:18px 18px calc(24px + env(safe-area-inset-bottom));max-height:55vh;overflow-y:auto;box-shadow:0 -4px 24px rgba(0,0,0,.08)}
.bottom-sheet.show{transform:translateY(0)}
.sheet-handle{width:36px;height:4px;background:#e5e7eb;border-radius:2px;margin:0 auto 14px}
.sheet-title{font-size:16px;font-weight:800;margin-bottom:4px;letter-spacing:-.3px}
.sheet-desc{font-size:12px;color:var(--muted);margin-bottom:14px;line-height:1.4}
.sheet-btn-row{display:flex;gap:10px;margin-top:14px}
.sheet-btn{flex:1;padding:12px;border:none;border-radius:var(--radius-md);font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .15s}
/* Sort options */
.sort-option{display:flex;align-items:center;gap:12px;padding:14px 16px;border-radius:var(--radius-md);cursor:pointer;transition:all .15s;font-size:15px;font-weight:600;color:#333}
.sort-option:active{background:#f5f5f5}
.sort-check{width:20px;height:20px;border-radius:50%;border:2px solid #ddd;display:flex;align-items:center;justify-content:center;color:#fff;transition:all .15s;flex-shrink:0}
.sort-check.active{background:var(--primary);border-color:var(--primary)}
.sort-check svg{width:12px;height:12px;opacity:0}
.sort-check.active svg{opacity:1}
.sheet-btn:active{transform:scale(.98);opacity:.9}
.sheet-btn-edit{background:var(--primary-light);color:var(--primary);border:1px solid rgba(74,108,247,.15)}
.sheet-btn-danger{background:#fef2f2;color:#b91c1c;border:1px solid #fee2e2}

/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:300;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(2px)}
.modal-overlay.show{opacity:1;pointer-events:auto}
.modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#fff;border-radius:var(--radius-lg);z-index:301;width:90%;max-width:380px;padding:24px 20px 28px;opacity:0;pointer-events:none;transition:all .3s cubic-bezier(.32,.72,0,1);box-shadow:var(--shadow-sm)}
.modal-overlay.show .modal{opacity:1;transform:translate(-50%,-50%) scale(1);pointer-events:auto}
.modal-title{font-size:17px;font-weight:800;margin-bottom:16px;text-align:center;letter-spacing:-.3px}
.modal-field{margin-bottom:12px}
.modal-field label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:4px}
.modal-field input,.modal-field select,.modal-field textarea{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:var(--radius-md);font-size:14px;outline:none;background:#f9fafb;transition:all .2s;color:var(--text);font-family:inherit}
.modal-field input:focus,.modal-field select:focus,.modal-field textarea:focus{border-color:rgba(64,153,255,.3);background:#fff;box-shadow:0 0 0 3px rgba(64,153,255,.08)}
.modal-field textarea{min-height:120px;resize:vertical;line-height:1.5}
.modal-submit{width:100%;padding:12px;border:none;background:var(--primary);color:#fff;border-radius:var(--radius-md);font-size:15px;font-weight:700;cursor:pointer;margin-top:6px;transition:all .15s;box-shadow:0 4px 12px rgba(64,153,255,.25)}
.modal-submit:active{transform:scale(.98);opacity:.9}
.modal-close{position:absolute;top:14px;right:14px;width:30px;height:30px;border-radius:50%;border:none;background:#f3f4f6;cursor:pointer;font-size:16px;color:#6b7280;display:flex;align-items:center;justify-content:center;transition:all .15s}
.modal-close:active{background:#e5e7eb;transform:scale(.9)}

/* Login modal */
.login-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.25);z-index:400;opacity:0;pointer-events:none;transition:opacity .3s;backdrop-filter:blur(2px)}
.login-modal-overlay.show{opacity:1;pointer-events:auto}
.login-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) scale(.95);background:#fff;border-radius:var(--radius-lg);z-index:401;width:90%;max-width:340px;padding:24px 20px 28px;opacity:0;pointer-events:none;transition:all .3s cubic-bezier(.32,.72,0,1);box-shadow:var(--shadow-sm)}
.login-modal-overlay.show .login-modal{opacity:1;transform:translate(-50%,-50%) scale(1);pointer-events:auto}
.login-title{font-size:17px;font-weight:800;margin-bottom:4px;text-align:center;letter-spacing:-.3px}
.login-sub{font-size:12px;color:var(--muted);text-align:center;margin-bottom:18px}
.login-field{margin-bottom:12px}
.login-field input{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:var(--radius-md);font-size:14px;outline:none;background:#f9fafb;transition:all .2s;color:var(--text)}
.login-field input:focus{border-color:rgba(64,153,255,.3);background:#fff;box-shadow:0 0 0 3px rgba(64,153,255,.08)}
.login-submit{width:100%;padding:12px;border:none;background:var(--primary);color:#fff;border-radius:var(--radius-md);font-size:15px;font-weight:700;cursor:pointer;margin-top:6px;transition:all .15s;box-shadow:0 4px 12px rgba(64,153,255,.25)}
.login-submit:active{transform:scale(.98);opacity(.9)}

/* Settings */
.toggle-wrap{display:flex;align-items:center;justify-content:space-between;padding:8px 0}
.toggle-label{font-size:14px;font-weight:600}
.toggle-switch{position:relative;display:inline-block;width:44px;height:24px;cursor:pointer;flex-shrink:0}
.toggle-switch input{opacity:0;width:0;height:0}
.toggle-slider{position:absolute;inset:0;background:#e5e7eb;border-radius:24px;transition:background .2s}
.toggle-slider::after{content:"";position:absolute;height:20px;width:20px;left:2px;bottom:2px;background:#fff;border-radius:50%;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.15)}
.toggle-switch input:checked+.toggle-slider{background:var(--primary)}
.toggle-switch input:checked+.toggle-slider::after{transform:translateX(20px)}

/* Bottom nav */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;height:calc(64px + env(safe-area-inset-bottom));padding-bottom:env(safe-area-inset-bottom);background:#FFFFFF;border-top:1px solid #f0f0f0;box-shadow:0 -2px 8px rgba(0,0,0,0.03);display:flex;justify-content:space-around;align-items:center;z-index:100;border-radius:20px 20px 0 0}
.nav-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;color:#999;padding:4px 0;width:60px;border:none;background:none;cursor:pointer;font-size:10px;font-weight:600;transition:all .2s;position:relative}
.nav-btn.active{color:var(--primary)}
.nav-btn svg{width:22px;height:22px;transition:transform .2s}
.nav-btn:active svg{transform:scale(.92)}
.nav-dot{display:none}

/* Empty + Toast */
.empty{text-align:center;padding:80px 24px;color:#d1d5db}
.empty svg{width:64px;height:64px;color:#e5e7eb;margin-bottom:12px}
.empty .text{font-size:14px;font-weight:700;color:#9ca3af}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(31,41,55,.9);color:#fff;padding:10px 18px;border-radius:var(--radius-md);z-index:1000;font-size:13px;pointer-events:none;font-weight:600;backdrop-filter:blur(4px);box-shadow:var(--shadow-sm)}

.search-hidden{display:none!important}
.search-highlight{box-shadow:0 0 0 2px var(--primary);background:#f0f7ff!important}
::-webkit-scrollbar{width:0;height:0}
</style>
</head>
<body>
<div id="toast"></div>

<!-- Login Modal -->
<div class="login-modal-overlay" id="loginOverlay" onclick="if(event.target===this)closeLoginModal()">
<div class="login-modal">
<div class="login-title" id="loginTitle">登录</div>
<div class="login-sub" id="loginSub">请输入密码以管理内容</div>
<div class="login-field"><input type="text" id="loginUser" value="admin" readonly style="background:#f3f4f6;color:#9ca3af"></div>
<div class="login-field" id="loginPassWrap"><input type="password" id="loginPass" placeholder="密码" onkeydown="if(event.key==='Enter')doLogin()"></div>
<button class="login-submit" id="loginSubmitBtn" onclick="doLogin()">登录</button>
</div>
</div>

<!-- Add Tool Modal -->
<div class="modal-overlay" id="addToolModal" onclick="if(event.target===this)closeAddToolModal()">
<div class="modal">
<button class="modal-close" onclick="closeAddToolModal()">&times;</button>
<div class="modal-title" id="addToolModalTitle">添加功能</div>
<div class="modal-field"><label>图标代码</label><input type="text" id="toolIcon" placeholder="emoji 或 SVG 代码"></div>
<div class="modal-field"><input type="text" id="toolName" placeholder="功能标题"></div>

<!-- Home specific -->
<div id="homeFields">
    <div class="modal-field"><label>目录名称</label><input type="text" id="toolDirName" placeholder="英文目录名，如: my_tool"></div>
    <div class="modal-field"><label>分类</label><select id="toolCatHome"></select></div>
    <div class="modal-field"><label>源代码 (HTML/CSS/JS)</label><textarea id="toolCode" placeholder="<!DOCTYPE html>... 输入完整HTML代码"></textarea></div>
</div>

<!-- Feature specific -->
<div id="featureFields" style="display:none">
    <div class="modal-field"><label>介绍</label><input type="text" id="toolDesc" placeholder="功能简短介绍"></div>
    <div class="modal-field"><input type="url" id="toolUrl" placeholder="功能URL" onblur="fetchSiteInfo('feature')"></div>
    <div class="modal-field">
    <select id="toolCatFeature">
    <option value="">主分类</option>
    <option value="__new__">+ 新建主分类</option>
    </select>
    </div>
    <div class="modal-field" id="featureNewCatField" style="display:none"><input type="text" id="featureNewCatName" placeholder="新主分类名称"></div>
    <div class="modal-field">
    <select id="toolSubCat">
    <option value="">子分类</option>
    <option value="__new__">+ 新建子分类</option>
    </select>
    </div>
    <div class="modal-field" id="featureNewSubField" style="display:none"><input type="text" id="featureNewSubName" placeholder="新子分类名称"></div>
</div>

<button class="modal-submit" onclick="submitAddTool()">提交</button>
</div>
</div>

<!-- Add API Modal -->
<div class="modal-overlay" id="apiModal" onclick="if(event.target===this)closeApiModal()">
<div class="modal">
<button class="modal-close" onclick="closeApiModal()">&times;</button>
<div class="modal-title">添加API网站</div>
<div class="modal-field"><label>图标代码</label><input type="text" id="apiIcon" placeholder="emoji 或 SVG 代码"></div>
<div class="modal-field"><input type="url" id="apiUrl" placeholder="网站URL" onblur="fetchApiFavicon()"></div>
<div class="modal-field"><input type="text" id="apiName" placeholder="网站标题"></div>
<button class="modal-submit" onclick="addApiSite()">提交</button>
</div>
</div>

<!-- Edit API Modal -->
<div class="modal-overlay" id="editApiModal" onclick="if(event.target===this)closeEditApiModal()">
<div class="modal">
<button class="modal-close" onclick="closeEditApiModal()">&times;</button>
<div class="modal-title">编辑 API</div>
<div class="modal-field"><label>图标代码</label><input type="text" id="editApiIcon" placeholder="emoji 或 SVG 代码"></div>
<div class="modal-field"><label>网站名称</label><input type="text" id="editApiName" placeholder="网站名称"></div>
<div class="modal-field"><label>网站网址</label><input type="url" id="editApiUrl" placeholder="https://..."></div>
<div class="sheet-btn-row">
<button class="sheet-btn sheet-btn-danger" onclick="deleteEditApi()">删除</button>
<button class="sheet-btn sheet-btn-edit" onclick="saveEditApi()">修改</button>
</div>
</div>
</div>

<!-- Edit Feature Modal -->
<div class="modal-overlay" id="editFeatureModal" onclick="if(event.target===this)closeEditFeatureModal()">
<div class="modal">
<button class="modal-close" onclick="closeEditFeatureModal()">&times;</button>
<div class="modal-title">编辑特色功能</div>
<div class="modal-field"><label>图标代码</label><input type="text" id="editFeatureIcon" placeholder="emoji 或 SVG 代码"></div>
<div class="modal-field"><input type="url" id="editFeatureUrl" placeholder="功能URL"></div>
<div class="modal-field"><input type="text" id="editFeatureName" placeholder="功能标题"></div>
<div class="modal-field"><input type="text" id="editFeatureDesc" placeholder="功能介绍"></div>
<div class="sheet-btn-row">
<button class="sheet-btn sheet-btn-danger" onclick="deleteEditFeature()">删除</button>
<button class="sheet-btn sheet-btn-edit" onclick="saveEditFeature()">修改</button>
</div>
</div>
</div>

<!-- Edit Site Modal -->
<div class="modal-overlay" id="editSiteModal" onclick="if(event.target===this)closeEditSiteModal()">
<div class="modal">
<button class="modal-close" onclick="closeEditSiteModal()">&times;</button>
<div class="modal-title">编辑网站</div>
<div class="modal-field"><label>图标代码</label><input type="text" id="editSiteIcon" placeholder="emoji 或 SVG 代码"></div>
<div class="modal-field"><label>网站名称</label><input type="text" id="editSiteTitle" placeholder="网站名称"></div>
<div class="modal-field"><input type="text" id="editSiteDesc" placeholder="网站介绍"></div>
<div class="modal-field"><label>网站网址</label><input type="url" id="editSiteUrl" placeholder="https://..."></div>
<div class="sheet-btn-row">
<button class="sheet-btn sheet-btn-danger" onclick="deleteEditSite()">删除</button>
<button class="sheet-btn sheet-btn-edit" onclick="saveEditSite()">修改</button>
</div>
</div>
</div>

<!-- Settings Modal -->
<div class="modal-overlay" id="settingsModal" onclick="if(event.target===this)closeSettingsModal()">
<div class="modal">
<button class="modal-close" onclick="closeSettingsModal()">&times;</button>
<div class="modal-title">设置</div>
<div class="toggle-wrap">
<span class="toggle-label">屏蔽浏览器右键菜单</span>
<label class="toggle-switch">
<input type="checkbox" id="blockCtxToggle" onchange="toggleBlockCtx()">
<span class="toggle-slider"></span>
</label>
</div>
</div>
</div>

<!-- Bottom Sheet -->
<div class="bottom-sheet-overlay" id="sheetOverlay" onclick="closeSheet()"></div>
<div class="bottom-sheet" id="bottomSheet">
<div class="sheet-handle"></div>
<div class="sheet-title" id="sheetTitle"></div>
<div class="sheet-desc" id="sheetDesc"></div>
<div class="sheet-btn-row" id="sheetButtons"></div>
</div>

<!-- Sort Sheet -->
<div class="bottom-sheet-overlay" id="sortOverlay" onclick="closeSortSheet()"></div>
<div class="bottom-sheet" id="sortSheet">
<div class="sheet-handle"></div>
<div class="sheet-title">排序方式</div>
<div id="sortOptions" style="padding:8px 0">
<div class="sort-option" data-value="clicks" onclick="selectSort('clicks')">
<div class="sort-check" id="sortCheckClicks"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></div>
<span>最多使用</span>
</div>
<div class="sort-option" data-value="newest" onclick="selectSort('newest')">
<div class="sort-check" id="sortCheckNewest"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6L9 17l-5-5"/></svg></div>
<span>最新投稿</span>
</div>
</div>
</div>

<!-- Add Site Modal -->
<div class="modal-overlay" id="siteModal" onclick="if(event.target===this)closeSiteModal()">
<div class="modal">
<button class="modal-close" onclick="closeSiteModal()">&times;</button>
<div class="modal-title">添加网站</div>
<div class="modal-field"><label>图标代码</label><input type="text" id="siteIcon" placeholder="emoji 或 SVG 代码"></div>
<div class="modal-field"><input type="url" id="siteUrl" placeholder="网站URL" onblur="fetchSiteInfo('site')"></div>
<div class="modal-field"><input type="text" id="siteTitle" placeholder="网站标题"></div>
<div class="modal-field"><input type="text" id="siteDesc" placeholder="网站介绍"></div>
<div class="modal-field">
<select id="siteCat">
<option value="">主分类</option>
<option value="__new__">+ 新建主分类</option>
</select>
</div>
<div class="modal-field" id="siteNewCatField" style="display:none"><input type="text" id="siteNewCatName" placeholder="新主分类名称"></div>
<div class="modal-field">
<select id="siteSubCat">
<option value="">子分类</option>
<option value="__new__">+ 新建子分类</option>
</select>
</div>
<div class="modal-field" id="siteNewSubField" style="display:none"><input type="text" id="siteNewSubName" placeholder="新子分类名称"></div>
<button class="modal-submit" onclick="addSite()">提交</button>
</div>
</div>

<div id="viewport">
<div id="pages">

<!-- Page 0: Home -->
<div class="page" id="page-home">
<div class="topbar">
<div class="search-row">
<div class="search-box">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
<input type="text" id="s1" placeholder="搜索所需功能" oninput="searchHome()">
<div class="avatar" onclick="openLoginModal()">
<img src="https://linyu.live/logo.png" alt="" onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#4099FF,#52c41a)';this.parentElement.innerHTML='?'">
<div class="login-dot" id="loginDot"></div>
</div>
</div>
<div class="action-btn" onclick="if(isLoggedIn){openAddToolModal()}else{showToast('请先登录')}" title="添加功能">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
</div>
</div>
</div>
<div class="repo-tabs" id="homeTabs" style="justify-content:center;border-bottom:none">
<div class="repo-tab" style="flex:none;padding:8px 20px;font-size:15px;font-weight:700;color:#333;pointer-events:none">功能大全</div>
</div>
<div id="homeContent" style="padding-top:4px"></div>
</div>

<!-- Page 1: Fav -->
<div class="page" id="page-fav">
<div class="topbar">
<div class="search-row">
<div class="search-box">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
<input type="text" id="s2" placeholder="搜索收藏" oninput="searchFav()">
<div class="avatar" onclick="openLoginModal()">
<img src="https://linyu.live/logo.png" alt="" onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#4099FF,#52c41a)';this.parentElement.innerHTML='?'">
<div class="login-dot" id="loginDot2"></div>
</div>
</div>
</div>
</div>
<div class="repo-tabs" id="favTabs">
<div class="repo-tab active" id="favTabHome" onclick="switchFavTab('home')">首页功能</div>
<div class="repo-tab" id="favTabFeature" onclick="switchFavTab('feature')">特色功能</div>
<div class="repo-tab" id="favTabRepo" onclick="switchFavTab('repo')">在线网站</div>
</div>
<div id="favContent"></div>
</div>

<!-- Page 2: Feature (在线功能) -->
<div class="page" id="page-api">
<div class="topbar">
<div class="search-row">
<div class="search-box">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
<input type="text" id="s3" placeholder="搜索在线功能" oninput="searchFeature()">
<div class="avatar" onclick="openLoginModal()">
<img src="https://linyu.live/logo.png" alt="" onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#4099FF,#52c41a)';this.parentElement.innerHTML='?'">
<div class="login-dot" id="loginDot3"></div>
</div>
</div>
</div>
</div>
<div class="repo-tabs" id="featureTabs" style="padding-left:16px">
<div class="repo-tab" style="flex:none;padding:8px 20px;font-size:15px;font-weight:700;color:#333;pointer-events:none">在线功能</div>
<div class="sort-btn" onclick="openSortSheet('feature')" style="margin-left:auto;padding:6px 12px;font-size:12px;color:var(--primary);background:var(--primary-light);border-radius:8px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:4px">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
<span id="featureSortLabel">最多使用</span>
</div>
</div>
<div class="site-layout" id="featureLayout">
<div class="sidebar" id="featureSidebar"></div>
<div class="site-content" id="featureContent"></div>
</div>
</div>

<!-- Page 3: Repo (在线网站) -->
<div class="page" id="page-repo">
<div class="topbar">
<div class="search-row">
<div class="search-box">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
<input type="text" id="s4" placeholder="搜索在线网站" oninput="searchRepo()">
<div class="avatar" onclick="openLoginModal()">
<img src="https://linyu.live/logo.png" alt="" onerror="this.style.display='none';this.parentElement.style.background='linear-gradient(135deg,#4099FF,#52c41a)';this.parentElement.innerHTML='?'">
<div class="login-dot" id="loginDot4"></div>
</div>
</div>
<div class="action-btn" onclick="openSettingsModal()" title="设置">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
</div>
</div>
</div>
<div class="repo-tabs" id="repoTabs" style="padding-left:16px">
<div class="repo-tab" style="flex:none;padding:8px 20px;font-size:15px;font-weight:700;color:#333;pointer-events:none">在线网页</div>
<div class="sort-btn" onclick="openSortSheet('repo')" style="margin-left:auto;padding:6px 12px;font-size:12px;color:var(--primary);background:var(--primary-light);border-radius:8px;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:4px">
<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
<span id="repoSortLabel">最多使用</span>
</div>
</div>
<div class="site-layout" id="siteLayout">
<div class="sidebar" id="sidebar"></div>
<div class="site-content" id="siteContent"></div>
</div>
</div>

</div>
</div>

<button class="fab" id="fabBtn" onclick="handleFab()">+</button>

<nav class="bottom-nav">
<button class="nav-btn active" onclick="goPage(0)">
<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 5.69l5 4.5V18h-2v-6H9v6H7v-7.81l5-4.5M12 3L2 12h3v8h6v-6h2v6h6v-8h3L12 3z"/></svg>
<span>首页</span>
<div class="nav-dot"></div>
</button>
<button class="nav-btn" onclick="goPage(1)">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><path d="M7 7h.01" stroke-linecap="round" stroke-width="2.5"/></svg>
<span>收藏</span>
<div class="nav-dot"></div>
</button>
<button class="nav-btn" onclick="goPage(2)">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
<span>特色</span>
<div class="nav-dot"></div>
</button>
<button class="nav-btn" onclick="goPage(3)">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
<span>仓库</span>
<div class="nav-dot"></div>
</button>
</nav>

<script>
// ===== Config =====
const catColors={hot:'#4099FF',feature:'#52c41a',query:'#4099FF',smart:'#4099FF',doc:'#4099FF',image:'#4099FF',text:'#4099FF',encode:'#4099FF',calc:'#4099FF',life:'#4099FF',tool:'#4099FF',security:'#4099FF',random:'#4099FF',color:'#4099FF',convert:'#4099FF',device:'#4099FF',media:'#4099FF',dev:'#4099FF',other:'#999999'};
const catsDef = <?php echo json_encode($catsDef); ?>;
const allTools = <?php echo json_encode($allTools); ?>;
const apiSitesDefault = <?php echo json_encode($apiSitesDefault); ?>;

const PASSWORD_HASH = '9aac200a71bff87f6c3ac9b6ef6db2a7ab4730f02b446b5a596884c0f45ad155';
const SESSION_TOKEN = 'qk_session_v2';

let currentPage=0,currentCat='全部',currentSub='全部',expandedCats={},subExpanded={},featureCat='全部';
let currentSheetItem=null,longPressTimer=null,searchTimers={};
let apiViewMode='grid',isLoggedIn=false;
let editingApiIdx=null,editingSiteIdx=null;
let featureSort='clicks',repoSort='clicks';

// ===== wendang 数据 (服务端注入，所有人共享) =====
const WENDANG_API = '/api/gongju/wendang/api.php';
let wApiSites = <?php echo json_encode($wendangApiSites, JSON_UNESCAPED_UNICODE); ?>;
let wSites    = <?php echo json_encode($wendangSites, JSON_UNESCAPED_UNICODE); ?>;
let wSiteCats = <?php echo json_encode($wendangSiteCats, JSON_UNESCAPED_UNICODE); ?>;
let wFavs     = <?php echo json_encode($wendangFavs, JSON_UNESCAPED_UNICODE); ?>;
let wFeatures = <?php echo json_encode($featureTools, JSON_UNESCAPED_UNICODE); ?>;
let wFeatureCats = <?php echo json_encode($wendangFeatureCats, JSON_UNESCAPED_UNICODE); ?>;
let wReady    = false;
let featureClicks = <?php echo json_encode($featureClicks, JSON_UNESCAPED_UNICODE); ?>;
let siteClicks = <?php echo json_encode($siteClicks, JSON_UNESCAPED_UNICODE); ?>;

// 确保 favs 和 site_cats 是对象（JSON.stringify 才能序列化命名属性）
if (Array.isArray(wFavs)) wFavs = {};
if (Array.isArray(wSiteCats)) wSiteCats = {};
if (Array.isArray(wFeatureCats)) wFeatureCats = {};

// wendang 保存函数
function wSave(key, data) {
    const map = {api_sites: function(){wApiSites=data}, sites: function(){wSites=data}, site_cats: function(){wSiteCats=data}, favs: function(){wFavs=data}, features: function(){wFeatures=data}, feature_cats: function(){wFeatureCats=data}};
    if (map[key]) map[key]();
    fetch(WENDANG_API, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'set', key: key, value: data})
    }).then(function(r){return r.json()}).then(function(j){
        if(j.code!==0) showToast('保存失败: '+(j.msg||''))
    }).catch(function(e){ showToast('保存失败: '+e.message) });
}

function getFavs(){return wFavs}
function saveFavs(f){wFavs=f;wSave('favs',f)}
function getApiSites(){return wApiSites}
function saveApiSites(s){wApiSites=s;wSave('api_sites',s)}
function getSites(){return wSites}
function saveSites(s){wSites=s;wSave('sites',s);if(currentPage===3)renderSiteLayout()}
function getSiteCats(){return wSiteCats}
function saveSiteCats(c){wSiteCats=c;wSave('site_cats',c)}
function getFeatures(){return wFeatures}
function saveFeatures(f){wFeatures=f;wSave('features',f);if(currentPage===2)renderFeatures()}
function getFeatureCats(){return wFeatureCats}
function saveFeatureCats(c){wFeatureCats=c;wSave('feature_cats',c)}
function initApiSites(){if(!wApiSites||!wApiSites.length){wApiSites=<?php echo json_encode($apiSitesDefault, JSON_UNESCAPED_UNICODE); ?>;wSave('api_sites',wApiSites)}return wApiSites}
function getFavicon(url){try{const u=new URL(url);return'https://t0.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=http://'+u.hostname+'&size=64'}catch(e){return''}}
function esc(s){return(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'&quot;')}

const letterColors=['#FF6B6B','#4ECDC4','#45B7D1','#96CEB4','#FFEAA7','#DDA0DD','#98D8C8','#F7DC6F','#BB8FCE','#85C1E9','#F8C471','#82E0AA','#F1948A','#85C1E9','#F39C12','#E74C3C','#2ECC71'];
function getLetterColor(name){let h=0;for(let i=0;i<name.length;i++)h=name.charCodeAt(i)+((h<5)-h);return letterColors[Math.abs(h)%letterColors.length]}
function getLetterAvatar(name, size=40){
  const letter=(name||'?').charAt(0).toUpperCase();
  const h=name.split('').reduce((acc,c)=>c.charCodeAt(0)+((acc<<5)-acc),0);
  const col1=letterColors[Math.abs(h)%letterColors.length];
  const col2=letterColors[Math.abs(h+7)%letterColors.length];
  const c1=col1;const c2=col2;const c3=letterColors[Math.abs(h+13)%letterColors.length];
  const svg=`<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}"><defs><linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:${c1};stop-opacity:1" /><stop offset="50%" style="stop-color:${c2};stop-opacity:1" /><stop offset="100%" style="stop-color:${c3};stop-opacity:1" /></linearGradient><filter id="s"><feDropShadow dx="0" dy="1" stdDeviation="1" flood-color="${c1}44"/></filter></defs><rect width="${size}" height="${size}" rx="12" fill="url(#g)" filter="url(#s)"/><text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="#fff" font-size="${size*0.45}" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,sans-serif" font-weight="700" letter-spacing="1">${letter}</text></svg>`;
  return 'data:image/svg+xml;base64,'+btoa(unescape(encodeURIComponent(svg)));
}
function formatSiteName(name){
  if(!name)return'';
  let t=name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  if(t.length<=7)return t;
  if(t.length<=13)return t.substring(0,7)+'<br>'+t.substring(7);
  return t.substring(0,7)+'<br>'+t.substring(7,13)+'...';
}


// ===== Click recording =====
function recordClick(url, type) {
    if (!url) return;
    const fd = new FormData();
    fd.append('action', 'record_click');
    fd.append('url', url);
    fd.append('type', type);
    fetch('', {method: 'POST', body: fd}).then(()=>{
        if (type === 'feature') {
            featureClicks[url] = (featureClicks[url] || 0) + 1;
            if(currentPage===2)renderFeatures();
        } else {
            siteClicks[url] = (siteClicks[url] || 0) + 1;
            if(currentPage===3)renderSiteLayout();
        }
    }).catch(()=>{});
}

async function fetchSiteInfo(type) {
    const urlId = type === 'feature' ? 'toolUrl' : 'siteUrl';
    const url = document.getElementById(urlId).value.trim();
    if (!url) return;

    if (type === 'site') {
        try { const u = new URL(url); if(!document.getElementById('siteTitle').value) document.getElementById('siteTitle').placeholder = u.hostname; } catch(e) {}
    }

    showToast('正在获取网站信息...');
    try {
        const fd = new FormData();
        fd.append('action', 'fetch_site_info');
        fd.append('url', url);
        const r = await fetch('', {method: 'POST', body: fd});
        const j = await r.json();
        if (j.success) {
            if (type === 'feature') {
                if (j.title && !document.getElementById('toolName').value) document.getElementById('toolName').value = j.title;
                if (j.desc && !document.getElementById('toolDesc').value) document.getElementById('toolDesc').value = j.desc;
            } else {
                if (j.title && !document.getElementById('siteTitle').value) document.getElementById('siteTitle').value = j.title;
                if (j.desc && !document.getElementById('siteDesc').value) document.getElementById('siteDesc').value = j.desc;
            }
            showToast('获取成功');
        } else {
            showToast(j.error || '获取失败');
        }
    } catch(e) { showToast('获取失败'); }
}

// ===== Block context menu (default ON) =====
let blockCtxEnabled=true;
function initBlockCtx(){blockCtxEnabled=localStorage.getItem('qk_block_ctx')!=='0';document.getElementById('blockCtxToggle').checked=blockCtxEnabled;applyBlockCtx()}
function applyBlockCtx(){if(blockCtxEnabled){document.addEventListener('contextmenu',blockCtx,true)}else{document.removeEventListener('contextmenu',blockCtx,true)}}
function blockCtx(e){e.preventDefault();e.stopPropagation();e.stopImmediatePropagation();return false}
function toggleBlockCtx(){blockCtxEnabled=document.getElementById('blockCtxToggle').checked;localStorage.setItem('qk_block_ctx',blockCtxEnabled?'1':'0');applyBlockCtx()}

// ===== Auth =====
async function sha256(str){const buf=await crypto.subtle.digest('SHA-256',new TextEncoder().encode(str));return Array.from(new Uint8Array(buf)).map(b=>b.toString(16).padStart(2,'0')).join('')}
async function doLogin(){const pass=document.getElementById('loginPass').value.trim();if(!pass){showToast('请输入密码');return}const hash=await sha256(pass);if(hash===PASSWORD_HASH){const sessionHash=await sha256(PASSWORD_HASH+'qingkong_salt_2024');localStorage.setItem(SESSION_TOKEN,sessionHash);document.getElementById('loginPass').value='';closeLoginModal();updateLoginState();showToast('登录成功')}else{showToast('密码错误');document.getElementById('loginPass').value='';document.getElementById('loginPass').focus()}}
function doLogout(){localStorage.removeItem(SESSION_TOKEN);updateLoginState();showToast('已退出登录')}
async function checkLogin(){const token=localStorage.getItem(SESSION_TOKEN);if(!token)return false;const expected=await sha256(PASSWORD_HASH+'qingkong_salt_2024');return token===expected}
async function updateLoginState(){isLoggedIn=await checkLogin();if(isLoggedIn&&(currentPage===2||currentPage===3)){fabBtn.classList.add('show')}else{fabBtn.classList.remove('show')}document.querySelectorAll('.login-dot').forEach(d=>d.classList.toggle('show',isLoggedIn));if(isLoggedIn){document.getElementById('loginTitle').textContent='已登录';document.getElementById('loginSub').textContent='管理员模式已开启';document.getElementById('loginPassWrap').style.display='none';document.getElementById('loginSubmitBtn').textContent='退出登录';document.getElementById('loginSubmitBtn').onclick=doLogout}else{document.getElementById('loginTitle').textContent='登录';document.getElementById('loginSub').textContent='请输入密码以管理内容';document.getElementById('loginPassWrap').style.display='block';document.getElementById('loginSubmitBtn').textContent='登录';document.getElementById('loginSubmitBtn').onclick=doLogin}}

// 给旧数据补充时间戳（保持原有顺序）
function initTimestamps(){const now=Date.now();wFeatures.forEach((f,i)=>{if(!f._t)f._t=now-(wFeatures.length-i)*1000});wSites.forEach((s,i)=>{if(!s._t)s._t=now-(wSites.length-i)*1000})}
function openLoginModal(){updateLoginState();document.getElementById('loginOverlay').classList.add('show');if(!isLoggedIn)setTimeout(()=>document.getElementById('loginPass').focus(),100)}
function closeLoginModal(){document.getElementById('loginOverlay').classList.remove('show')}

// ===== Settings =====
function openSettingsModal(){initBlockCtx();document.getElementById('settingsModal').classList.add('show')}
function closeSettingsModal(){document.getElementById('settingsModal').classList.remove('show')}

// ===== Navigation =====
const pages=document.getElementById('pages'),navBtns=document.querySelectorAll('.nav-btn'),fabBtn=document.getElementById('fabBtn');
function goPage(i){currentPage=i;pages.style.transform='translateX(-'+(i*25)+'%)';navBtns.forEach((b,j)=>b.classList.toggle('active',j===i));if(isLoggedIn&&(i===2||i===3)){fabBtn.classList.add('show')}else{fabBtn.classList.remove('show')}if(i===1)renderFavs();if(i===2)renderFeatures();if(i===3){initSites();renderSiteLayout()}}
let sx=0,sy=0,sw=false;
const vp=document.getElementById('viewport');
/*vp.addEventListener('touchstart',e=>{sx=e.touches[0].clientX;sy=e.touches[0].clientY;sw=true},{passive:true});*/
/*vp.addEventListener('touchmove',e=>{if(!sw)return;const dx=e.touches[0].clientX-sx,dy=e.touches[0].clientY-sy;if(Math.abs(dx)>Math.abs(dy)&&Math.abs(dx)>40)e.preventDefault()},{passive:false});*/
// 禁用滑动切换页面，避免滑到空白区域
/*vp.addEventListener('touchend',e=>{if(!sw)return;sw=false;const dx=e.changedTouches[0].clientX-sx;if(Math.abs(dx)<60)return;if(dx<0&&currentPage<3)goPage(currentPage+1);else if(dx>0&&currentPage>0)goPage(currentPage-1)});*/

// ===== Home =====
function buildHome(){
    const groups={};
    allTools.forEach(t=>{const c=t.cat||'other';if(!groups[c])groups[c]=[];groups[c].push(t)});
    let html='';
    const sortedCats=Object.keys(catsDef).sort((a,b)=>catsDef[a].order-catsDef[b].order);
    sortedCats.forEach(ck=>{
        if(!groups[ck]||!groups[ck].length)return;
        const list=groups[ck],col=catColors[ck]||'#4a6cf7',isOpen=expandedCats[ck]!==false;
        html+=`<div class="cat-section" data-cat="${ck}"><div class="cat-header" onclick="toggleCat('${ck}')"><div class="cat-title">${catsDef[ck].name}<span class="cat-badge">${list.length}个应用</span></div><svg class="cat-arrow ${isOpen?'open':''}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg></div><div class="cat-body ${isOpen?'open':''}"><div class="tool-grid">`;
        list.forEach(t=>{
            const id=t.type+'-'+t.dir;
            const url=t.type==='module'?t.url:'generator/'+t.dir+'/';
            const iconEl=t.icon&&t.icon.startsWith('<')?`<span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%">${t.icon}</span>`:(t.icon||'⚙️');
            html+=`<div class="tool-pill" data-id="${id}" data-name="${esc(t.name)}" data-desc="${esc(t.desc||'')}" onclick="openTool('${url}')" ontouchstart="startLongPress(event,'${id}','${esc(t.name)}','${esc(t.desc||'')}','${url}')" ontouchend="cancelLongPress()" ontouchmove="cancelLongPress()"><div class="tool-pill-icon" style="background:${col}12;color:${col}">${iconEl}</div><div class="tool-pill-name">${t.name}</div></div>`;
        });
        html+=`</div></div></div>`;
    });
    document.getElementById('homeContent').innerHTML=html;
}
function toggleCat(ck){expandedCats[ck]=expandedCats[ck]===false?true:false;const sec=document.querySelector(`.cat-section[data-cat="${ck}"]`);if(!sec)return;sec.querySelector('.cat-body').classList.toggle('open');sec.querySelector('.cat-arrow').classList.toggle('open')}
function debounce(key,fn,ms=250){if(searchTimers[key])clearTimeout(searchTimers[key]);searchTimers[key]=setTimeout(fn,ms)}
function searchHome(){debounce('home',()=>{
    const v=document.getElementById('s1').value.toLowerCase().trim();
    const c=document.getElementById('homeContent');
    const all=c.querySelectorAll('.tool-pill');
    if(!v){all.forEach(p=>{p.classList.remove('search-hidden');p.classList.remove('search-highlight')});buildHome();return}
    const matched=[];
    all.forEach(p=>{
        const s=(p.dataset.name+' '+p.dataset.desc).toLowerCase();
        if(s.includes(v)){matched.push(p);p.classList.add('search-highlight');p.classList.remove('search-hidden')}
        else{p.classList.remove('search-highlight');p.classList.add('search-hidden')}
    });
    matched.forEach(p=>c.prepend(p))
})}
function startLongPress(e,id,name,desc,url){
    longPressTimer=setTimeout(()=>{openFavSheet(id,name,desc,url)},500)
}
function cancelLongPress(){if(longPressTimer){clearTimeout(longPressTimer);longPressTimer=null}}
function openTool(url){window.location.href=url}

// ===== Add Tool / Feature (POST to wendang API or PHP) =====
let currentToolTargetDir='generator';
function openAddToolModal(targetDir){
    targetDir=targetDir||'generator';
    currentToolTargetDir=targetDir;

    document.getElementById('addToolModalTitle').textContent = targetDir==='feature' ? '添加特色功能' : '添加功能';

    // Reset fields
    document.getElementById('toolIcon').value='';
    document.getElementById('toolName').value='';

    if(targetDir === 'feature'){
        document.getElementById('homeFields').style.display='none';
        document.getElementById('featureFields').style.display='block';

        document.getElementById('toolUrl').value='';
        document.getElementById('toolDesc').value='';
        document.getElementById('featureNewCatField').style.display='none';
        document.getElementById('featureNewSubField').style.display='none';
        document.getElementById('featureNewCatName').value='';
        document.getElementById('featureNewSubName').value='';

        // Populate Feature Cats
        const cats=getFeatureCats();
        const sel=document.getElementById('toolCatFeature');
        sel.innerHTML='<option value="">主分类</option><option value="__new__">+ 新建主分类</option>';
        Object.keys(cats).forEach(cat=>{sel.innerHTML+=`<option value="${cat}">${cat}</option>`});
        document.getElementById('toolSubCat').innerHTML='<option value="">子分类</option><option value="__new__">+ 新建子分类</option>';
    }else{
        document.getElementById('homeFields').style.display='block';
        document.getElementById('featureFields').style.display='none';

        document.getElementById('toolDirName').value='';
        document.getElementById('toolCode').value='';

        // Populate Home Cats
        const sel=document.getElementById('toolCatHome');
        sel.innerHTML='';
        Object.keys(catsDef).forEach(ck=>{sel.innerHTML+=`<option value="${ck}">${catsDef[ck].name}</option>`});
    }

    document.getElementById('addToolModal').classList.add('show');
}
function closeAddToolModal(){document.getElementById('addToolModal').classList.remove('show')}
document.addEventListener('DOMContentLoaded',()=>{
    // Listeners for Feature Modal
    const fc=document.getElementById('toolCatFeature');
    if(fc)fc.addEventListener('change',()=>{
        document.getElementById('featureNewCatField').style.display=fc.value==='__new__'?'block':'none';
        const cats=getFeatureCats();
        const cat=fc.value;
        const subSel=document.getElementById('toolSubCat');
        subSel.innerHTML='<option value="">子分类</option><option value="__new__">+ 新建子分类</option>';
        if(cat && cats[cat]) cats[cat].forEach(s=>subSel.innerHTML+=`<option value="${s}">${s}</option>`);
        const sv=subSel.value;
        document.getElementById('featureNewSubField').style.display=sv==='__new__'?'block':'none';
    });
    const fs=document.getElementById('toolSubCat');
    if(fs)fs.addEventListener('change',()=>{
        document.getElementById('featureNewSubField').style.display=fs.value==='__new__'?'block':'none';
    });

    // Listeners for Site Modal (Repo Page)
    const sc=document.getElementById('siteCat');
    if(sc)sc.addEventListener('change',()=>{
        document.getElementById('siteNewCatField').style.display=sc.value==='__new__'?'block':'none';
        updateSubCatOptions();
        const ss=document.getElementById('siteSubCat').value;
        document.getElementById('siteNewSubField').style.display=ss==='__new__'?'block':'none';
    });
    const ss=document.getElementById('siteSubCat');
    if(ss)ss.addEventListener('change',()=>{
        document.getElementById('siteNewSubField').style.display=ss.value==='__new__'?'block':'none';
    });
});
async function submitAddTool(){
    const icon=document.getElementById('toolIcon').value.trim();
    const name=document.getElementById('toolName').value.trim();

    if(currentToolTargetDir==='feature'){
        const url=document.getElementById('toolUrl').value.trim();
        const desc=document.getElementById('toolDesc').value.trim();
        let cat=document.getElementById('toolCatFeature').value;
        let sub=document.getElementById('toolSubCat').value;

        if(!name){showToast('请输入名称');return}
        if(!url){showToast('请输入URL');return}

        if(cat==='__new__'){cat=document.getElementById('featureNewCatName').value.trim();if(!cat){showToast('请输入主分类名称');return}}
        if(sub==='__new__'){sub=document.getElementById('featureNewSubName').value.trim();if(!sub){showToast('请输入子分类名称');return}}

        const cats=getFeatureCats();
        if(!cats[cat])cats[cat]=[];
        if(sub&&!cats[cat].includes(sub))cats[cat].push(sub);
        saveFeatureCats(cats);

        const features=getFeatures();
        features.push({name,url,desc,icon:icon||'🔧',cat,sub:sub||'',_t:Date.now()});
        saveFeatures(features);
        showToast('添加成功');closeAddToolModal();renderFeatures();
    }else{
        const dirName=document.getElementById('toolDirName').value.trim();
        const cat=document.getElementById('toolCatHome').value;
        const code=document.getElementById('toolCode').value.trim();

        if(!name){showToast('请输入名称');return}
        if(!code){showToast('请输入源代码');return}

        const fd=new FormData();
        fd.append('action','add_tool');fd.append('name',name);fd.append('dir_name',dirName);
        fd.append('icon',icon||'🔧');fd.append('category',cat);fd.append('code',code);
        fd.append('target_dir','generator');

        try{
            const r=await fetch('',{method:'POST',body:fd});
            const j=await r.json();
            if(j.success){showToast('添加成功');closeAddToolModal();setTimeout(()=>location.reload(),600)}
            else{showToast(j.error||'添加失败')}
        }catch(e){showToast('请求失败: '+e.message)}
    }
}
function openFeatureModal(){openAddToolModal('feature')}
// ===== Sheet + Fav =====
function openFavSheet(id,name,desc,url){currentSheetItem={id,name,desc,url};document.getElementById('sheetTitle').textContent=name;document.getElementById('sheetDesc').textContent=desc||'';const favs=getFavs();document.getElementById('sheetButtons').innerHTML=`<button class="sheet-btn ${favs[id]?'sheet-btn-danger':'sheet-btn-edit'}" onclick="toggleFav()">${favs[id]?'取消收藏':'收藏此功能'}</button>`;document.getElementById('sheetOverlay').classList.add('show');document.getElementById('bottomSheet').classList.add('show')}
function closeSheet(){document.getElementById('sheetOverlay').classList.remove('show');document.getElementById('bottomSheet').classList.remove('show');currentSheetItem=null}

// ===== Sort Sheet =====
let currentSortType='';
function openSortSheet(type){currentSortType=type;const current=type==='feature'?featureSort:repoSort;document.getElementById('sortCheckClicks').classList.toggle('active',current==='clicks');document.getElementById('sortCheckNewest').classList.toggle('active',current==='newest');document.getElementById('sortOverlay').classList.add('show');document.getElementById('sortSheet').classList.add('show');document.body.style.overflow='hidden'}
function closeSortSheet(){document.getElementById('sortOverlay').classList.remove('show');document.getElementById('sortSheet').classList.remove('show');currentSortType='';document.body.style.overflow=''}
function selectSort(sort){if(currentSortType==='feature'){featureSort=sort;document.getElementById('featureSortLabel').textContent=sort==='clicks'?'最多使用':'最新投稿';renderFeatures()}else if(currentSortType==='repo'){repoSort=sort;document.getElementById('repoSortLabel').textContent=sort==='clicks'?'最多使用':'最新投稿';renderSiteLayout()}closeSortSheet();showToast(sort==='clicks'?'已按最多使用排序':'已按最新投稿排序')}
function toggleFav(){if(!currentSheetItem)return;const favs=getFavs();if(favs[currentSheetItem.id]){delete favs[currentSheetItem.id];showToast('已取消收藏')}else{favs[currentSheetItem.id]={name:currentSheetItem.name,desc:currentSheetItem.desc,url:currentSheetItem.url,type:currentSheetItem.id.split('-')[0]}}saveFavs(favs);closeSheet();if(currentPage===1)renderFavs()}

let favTab='home';
function switchFavTab(tab){favTab=tab;document.getElementById('favTabHome').classList.toggle('active',tab==='home');document.getElementById('favTabFeature').classList.toggle('active',tab==='feature');document.getElementById('favTabRepo').classList.toggle('active',tab==='repo');renderFavs()}
function renderFavs(){
    const favs=getFavs();const ids=Object.keys(favs);const c=document.getElementById('favContent');
    let filtered=[];
    ids.forEach(id=>{const f=favs[id];
        if(favTab==='home'){if(!f.type||f.type==='generator'||f.type==='module')filtered.push({...f,id})}
        else if(favTab==='feature'){if(f.type==='feature')filtered.push({...f,id})}
        else if(favTab==='repo'){if(f.type==='site'||f.type==='api')filtered.push({...f,id})}
    });
    if(!filtered.length){c.innerHTML='<div class="empty"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg><div class="text">暂无收藏</div></div>';return}
    let html='<div class="site-content" style="padding:10px 12px">';
    filtered.forEach(f=>{const col=catColors[f.type]||'#4a6cf7';html+=`<div class="site-item" data-f="${esc(f.name)}" ontouchstart="startLongPressFav(event,'${f.id}','${esc(f.name)}','${esc(f.desc||'')}','${esc(f.url)}')" ontouchend="cancelLongPress()" ontouchmove="cancelLongPress()" style="cursor:pointer" onclick="openTool('${esc(f.url)}')"><div class="favicon" style="background:${col}12;color:${col}">⭐</div><div class="info"><div class="name">${f.name}</div><div class="sub">${f.desc||f.url||''}</div></div></div>`});
    html+='</div>';c.innerHTML=html;
}
function startLongPressFav(e,id,name,desc,url){longPressTimer=setTimeout(()=>{currentSheetItem={id,name,desc,url};document.getElementById('sheetTitle').textContent=name;document.getElementById('sheetDesc').textContent='长按取消收藏';document.getElementById('sheetButtons').innerHTML=`<button class="sheet-btn sheet-btn-danger" onclick="removeFavDirect()">取消收藏</button>`;document.getElementById('sheetOverlay').classList.add('show');document.getElementById('bottomSheet').classList.add('show')},500)}
function removeFavDirect(){if(!currentSheetItem)return;const favs=getFavs();delete favs[currentSheetItem.id];saveFavs(favs);showToast('已取消收藏');closeSheet();renderFavs()}
function searchFav(){debounce('fav',()=>{
    const v=document.getElementById('s2').value.toLowerCase().trim();
    const c=document.getElementById('favContent');
    const all=c.querySelectorAll('.site-item');
    if(!v){all.forEach(p=>{p.classList.remove('search-hidden');p.classList.remove('search-highlight')});renderFavs();return}
    const matched=[];
    all.forEach(p=>{
        const s=(p.dataset.f||'').toLowerCase();
        if(s.includes(v)){matched.push(p);p.classList.add('search-highlight');p.classList.remove('search-hidden')}
        else{p.classList.remove('search-highlight');p.classList.add('search-hidden')}
    });
    matched.forEach(p=>c.prepend(p))
})}

// ===== Feature Page (在线功能) =====
let featureCurrentCat='全部', featureCurrentSub='全部', featureSubExpanded={};
function renderFeatures(){
    let features=getFeatures().slice();
    const cats=getFeatureCats();
    const clicks = featureClicks || {};

    // 排序
    if(featureSort==='clicks'){
        features.sort((a,b)=>(clicks[b.url]||0)-(clicks[a.url]||0));
    }else{
        features.sort((a,b)=>(b._t||0)-(a._t||0));
    }

    const groups={};
    features.forEach(t=>{
        const cat=t.cat||'其他';
        if(!groups[cat]) groups[cat]=[];
        groups[cat].push(t);
    });

    let sidebarHtml = '<div class="sidebar-item'+(featureCurrentCat==='全部'?' active':'')+'" onclick="selectFeatureCat(\'全部\')">全部</div>';

    const sortedCats = Object.keys(cats).sort(); 

    sortedCats.forEach(cat => {
        const subs = cats[cat] || [];
        const hasSubs = subs.length > 0;
        const isExpanded = !!featureSubExpanded[cat];
        const isActive = featureCurrentCat === cat && (featureCurrentSub === '全部' || !hasSubs);

        sidebarHtml += `<div class="sidebar-item${(isActive?' active':'')}" onclick="toggleFeatureSub('${esc(cat)}')">${cat}</div>`;

        if(hasSubs){
            sidebarHtml += `<div class="sidebar-sub-wrap ${isExpanded?'show':''}" id="featureSubWrap-${esc(cat)}">`;
            subs.forEach(sub => {
                const isSubActive = featureCurrentCat === cat && featureCurrentSub === sub;
                sidebarHtml += `<div class="sidebar-sub${(isSubActive?' active':'')}" onclick="event.stopPropagation();selectFeatureSub('${esc(cat)}','${esc(sub)}')">${sub}</div>`;
            });
            sidebarHtml += `</div>`;
        }
    });
    document.getElementById('featureSidebar').innerHTML = sidebarHtml;

    let contentHtml = '';
    let filtered = [];

    if (featureCurrentCat === '全部') {
        filtered = features;
    } else {
        filtered = features.filter(t => (t.cat || '其他') === featureCurrentCat);
        if (featureCurrentSub !== '全部') {
            filtered = filtered.filter(t => (t.sub || '') === featureCurrentSub);
        }
    }

    if(!filtered.length){
        contentHtml+=`<div class="empty"><div class="text">暂无功能</div></div>`;
    }else{
        filtered.forEach(t=>{
            const id='feature-'+t.name;
            const url=t.url;
            const clickCount = clicks[url] || 0;
            const clickText = clickCount > 0 ? ` · ${clickCount}次使用` : '';

            let iconEl;
            if (t.icon && t.icon.trim()) {
                iconEl = t.icon.startsWith('<') ? `<span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%">${t.icon}</span>` : `<span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:20px">${t.icon}</span>`;
            } else {
                const favUrl = getFavicon(t.url);
                const letterImg = getLetterAvatar(t.name || t.url);
                iconEl = `<img class="fav-letter" src="${letterImg}" alt=""><img src="${favUrl}" loading="lazy" decoding="async" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .3s;z-index:2" onload="this.style.opacity='1';this.previousElementSibling.style.display='none'" onerror="this.style.display='none'">`;
            }

            contentHtml+=`<div class="site-item" data-f="${(t.name+' '+t.desc).toLowerCase()}" ontouchstart="startLongPressFeature(event,'${id}','${esc(t.name)}','${esc(t.desc||'')}','${esc(url)}')" ontouchend="cancelLongPress()" ontouchmove="cancelLongPress()" style="cursor:pointer" onclick="recordClick('${esc(url)}','feature');openTool('${esc(url)}')"><div class="favicon" style="position:relative;overflow:hidden">${iconEl}</div><div class="info"><div class="name">${formatSiteName(t.name)}</div><div class="click-count">${clickText}</div></div></div>`;
        });
    }
    document.getElementById('featureContent').innerHTML=contentHtml;
}
function toggleFeatureSub(cat){
    Object.keys(featureSubExpanded).forEach(k => { if(k !== cat) featureSubExpanded[k] = false; });
    featureSubExpanded[cat] = !featureSubExpanded[cat];
    featureCurrentCat = cat;
    featureCurrentSub = '全部';
    renderFeatures();
}
function selectFeatureCat(cat){
    featureCurrentCat = cat;
    featureCurrentSub = '全部';
    renderFeatures();
}
function selectFeatureSub(cat, sub){
    featureCurrentCat = cat;
    featureCurrentSub = sub;
    renderFeatures();
}
function startLongPressFeature(e,id,name,desc,url){
    longPressTimer=setTimeout(()=>{
        const favs=getFavs();
        currentSheetItem={id,name,desc,url};
        document.getElementById('sheetTitle').textContent=name;
        document.getElementById('sheetDesc').textContent=desc||'';

        let btns = `<button class="sheet-btn ${favs[id]?'sheet-btn-danger':'sheet-btn-edit'}" onclick="toggleFeatureFav('${id}','${esc(name)}','${esc(url)}')">${favs[id]?'取消收藏':'收藏此功能'}</button>`;
        if(isLoggedIn){
            btns+=`<button class="sheet-btn sheet-btn-edit" onclick="editFeatureItem()">编辑</button>`;
            btns+=`<button class="sheet-btn sheet-btn-danger" onclick="deleteFeatureItem()">删除</button>`;
        }
        document.getElementById('sheetButtons').innerHTML=btns;
        document.getElementById('sheetOverlay').classList.add('show');
        document.getElementById('bottomSheet').classList.add('show')
    },500)
}
function toggleFeatureFav(id,name,url){const favs=getFavs();if(favs[id]){delete favs[id];showToast('已取消收藏')}else{favs[id]={name:name,url:url,type:'feature'};showToast('已收藏')}saveFavs(favs);closeSheet();if(currentPage===1)renderFavs()}
function searchFeature(){debounce('feature',()=>{
    const v=document.getElementById('s3').value.toLowerCase().trim();
    const c=document.getElementById('featureContent');
    const all=c.querySelectorAll('.site-item');
    if(!v){all.forEach(p=>{p.classList.remove('search-hidden');p.classList.remove('search-highlight')});renderFeatures();return}
    const matched=[];
    all.forEach(p=>{
        const s=(p.dataset.f||'').toLowerCase();
        if(s.includes(v)){matched.push(p);p.classList.add('search-highlight');p.classList.remove('search-hidden')}
        else{p.classList.remove('search-highlight');p.classList.add('search-hidden')}
    });
    matched.forEach(p=>c.prepend(p))
})}

// ===== Repo Page (在线网站) =====
function initSites(){
    let hasApiSites = false;
    if(wApiSites && wApiSites.length){
        const existingUrls = new Set((wSites||[]).map(s=>s.url));
        wApiSites.forEach(s=>{
            if(!existingUrls.has(s.url)){
                wSites.push({title:s.name,url:s.url,cat:'API',sub:'api',icon:''});
                hasApiSites = true;
            }
        });
    }
    if(!wSiteCats['API']){wSiteCats['API']=['api']}
    if(hasApiSites){wSave('sites',wSites);wSave('site_cats',wSiteCats)}
    return wSites;
}

// ===== Site Page =====
function renderSiteLayout(){
    const cats=getSiteCats();
    const clicks = siteClicks || {};
    let sites = wSites.slice(); // 副本，不修改原始顺序

    // 排序
    if(repoSort==='clicks'){
        sites.sort((a,b)=>(clicks[b.url]||0)-(clicks[a.url]||0));
    }else{
        sites.sort((a,b)=>(b._t||0)-(a._t||0));
    }

    let sidebarHtml='<div class="sidebar-item'+(currentCat==='全部'?' active':'')+'" onclick="selectSiteCat(\'全部\')">全部</div>';
    Object.keys(cats).forEach(cat=>{const isSubOpen=!!subExpanded[cat],subs=cats[cat]||[];sidebarHtml+=`<div class="sidebar-item${(currentCat===cat?' active':'')}" onclick="toggleSubCat('${esc(cat)}')">${cat}</div><div class="sidebar-sub-wrap ${isSubOpen?'show':''}" id="subWrap-${esc(cat)}">`;subs.forEach(sub=>{sidebarHtml+=`<div class="sidebar-sub${(currentCat===cat&&currentSub===sub?' active':'')}" onclick="event.stopPropagation();selectSiteSub('${esc(sub)}')">${sub}</div>`});sidebarHtml+=`</div>`});
    document.getElementById('sidebar').innerHTML=sidebarHtml;

    let contentHtml='';
    const filtered=(currentCat==='全部')?sites:sites.filter(s=>s.cat===currentCat);
    const subFiltered=(currentSub==='全部'||currentCat==='全部')?filtered:filtered.filter(s=>s.sub===currentSub);

    if(!subFiltered.length){contentHtml+=`<div class="empty"><div class="text">暂无网站</div></div>`}else{
        subFiltered.forEach((s)=>{
            const realIdx=wSites.indexOf(s);
            const clickCount = clicks[s.url] || 0;
            const clickText = clickCount > 0 ? ` · ${clickCount}次使用` : '';

            let faviconHtml;
            if (s.icon && s.icon.trim()) {
                faviconHtml = s.icon.startsWith('<') ? `<span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%">${s.icon}</span>` : `<span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:20px">${s.icon}</span>`;
            } else {
                const favUrl=getFavicon(s.url);
                const letterImg=getLetterAvatar(s.title||s.url);
                faviconHtml = `<img class="fav-letter" src="${letterImg}" alt=""><img src="${favUrl}" loading="lazy" decoding="async" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:0;transition:opacity .3s;z-index:2" onload="this.style.opacity='1';this.previousElementSibling.style.display='none'" onerror="this.style.display='none'">`;
            }

            contentHtml+=`<a href="${s.url}" target="_blank" class="site-item" data-s="${(s.title+' '+s.desc+' '+s.url).toLowerCase()}" onclick="recordClick('${esc(s.url)}','site')" ontouchstart="startLongPressSite(event,${realIdx})" ontouchend="cancelLongPress()" ontouchmove="cancelLongPress()"><div class="favicon" style="position:relative;overflow:hidden">${faviconHtml}</div><div class="info"><div class="name">${formatSiteName(s.title)}</div><div class="click-count">${clickText}</div></div></a>`;
        });
    }
    document.getElementById('siteContent').innerHTML=contentHtml;
}
function toggleSubCat(cat){
    Object.keys(subExpanded).forEach(k => { if(k !== cat) subExpanded[k] = false; });
    subExpanded[cat]=!subExpanded[cat];
    currentCat=cat;
    currentSub='全部';
    renderSiteLayout();
}
function selectSiteCat(cat){currentCat=cat;currentSub='全部';renderSiteLayout()}
function selectSiteSub(sub){currentSub=sub;renderSiteLayout()}
function searchRepo(){debounce('site',()=>{
    const v=document.getElementById('s4').value.toLowerCase().trim();
    const c=document.getElementById('siteContent');
    const all=c.querySelectorAll('.site-item');
    if(!v){all.forEach(p=>{p.classList.remove('search-hidden');p.classList.remove('search-highlight')});renderSiteLayout();return}
    const matched=[];
    all.forEach(p=>{
        const s=(p.dataset.s||'').toLowerCase();
        if(s.includes(v)){matched.push(p);p.classList.add('search-highlight');p.classList.remove('search-hidden')}
        else{p.classList.remove('search-highlight');p.classList.add('search-hidden')}
    });
    matched.forEach(p=>c.prepend(p))
})}
function startLongPressSite(e,i){longPressTimer=setTimeout(()=>{openSiteSheet(i)},500)}
function openSiteSheet(i){const sites=getSites();if(!sites[i])return;const s=sites[i];currentSheetItem={idx:i};editingSiteIdx=i;document.getElementById('sheetTitle').textContent=s.title;document.getElementById('sheetDesc').textContent=s.url;const siteId='site-'+s.url;const favs=getFavs();let btns=`<button class="sheet-btn ${favs[siteId]?'sheet-btn-danger':'sheet-btn-edit'}" onclick="toggleSiteFav('${siteId}','${esc(s.title)}','${esc(s.url)}')">${favs[siteId]?'取消收藏':'收藏此网站'}</button>`;if(isLoggedIn){btns+=`<button class="sheet-btn sheet-btn-edit" onclick="editSiteItem()">编辑</button><button class="sheet-btn sheet-btn-danger" onclick="deleteSiteItem()">删除</button>`}document.getElementById('sheetButtons').innerHTML=btns;document.getElementById('sheetOverlay').classList.add('show');document.getElementById('bottomSheet').classList.add('show')}
function toggleSiteFav(id,name,url){const favs=getFavs();if(favs[id]){delete favs[id];showToast('已取消收藏')}else{favs[id]={name:name,url:url,type:'site'};showToast('已收藏')}saveFavs(favs);closeSheet();if(currentPage===1)renderFavs()}
function editSiteItem(){closeSheet();const sites=getSites();if(!sites[editingSiteIdx])return;const s=sites[editingSiteIdx];document.getElementById('editSiteIcon').value=s.icon||'';document.getElementById('editSiteTitle').value=s.title;document.getElementById('editSiteDesc').value=s.desc||'';document.getElementById('editSiteUrl').value=s.url;document.getElementById('editSiteModal').classList.add('show')}
function closeEditSiteModal(){document.getElementById('editSiteModal').classList.remove('show');editingSiteIdx=null}
function saveEditSite(){if(editingSiteIdx===null)return;const sites=getSites();if(!sites[editingSiteIdx])return;sites[editingSiteIdx].icon=document.getElementById('editSiteIcon').value.trim();sites[editingSiteIdx].title=document.getElementById('editSiteTitle').value.trim();sites[editingSiteIdx].desc=document.getElementById('editSiteDesc').value.trim();sites[editingSiteIdx].url=document.getElementById('editSiteUrl').value.trim();saveSites(sites);closeEditSiteModal();showToast('已修改');renderSiteLayout()}
function deleteEditSite(){if(editingSiteIdx===null)return;const sites=getSites();if(!sites[editingSiteIdx])return;sites.splice(editingSiteIdx,1);saveSites(sites);closeEditSiteModal();showToast('已删除');renderSiteLayout()}
function deleteSiteItem(){if(!currentSheetItem||currentSheetItem.idx===undefined)return;const sites=getSites();sites.splice(currentSheetItem.idx,1);saveSites(sites);showToast('已删除');closeSheet();renderSiteLayout()}

// ===== Feature Edit =====
let editingFeatureUrl = null;
function editFeatureItem(){
    if(!currentSheetItem)return;
    editingFeatureUrl = currentSheetItem.url;
    const features=getFeatures();
    const f = features.find(x => x.url === editingFeatureUrl);
    if(!f)return;

    document.getElementById('editFeatureIcon').value=f.icon||'';
    document.getElementById('editFeatureUrl').value=f.url;
    document.getElementById('editFeatureName').value=f.name;
    document.getElementById('editFeatureDesc').value=f.desc||'';
    document.getElementById('editFeatureModal').classList.add('show');
    closeSheet();
}
function closeEditFeatureModal(){document.getElementById('editFeatureModal').classList.remove('show');editingFeatureUrl=null}
function saveEditFeature(){
    if(!editingFeatureUrl)return;
    const features=getFeatures();
    const idx = features.findIndex(x => x.url === editingFeatureUrl);
    if(idx===-1)return;

    features[idx].icon=document.getElementById('editFeatureIcon').value.trim();
    features[idx].url=document.getElementById('editFeatureUrl').value.trim();
    features[idx].name=document.getElementById('editFeatureName').value.trim();
    features[idx].desc=document.getElementById('editFeatureDesc').value.trim();
    saveFeatures(features);
    closeEditFeatureModal();
    showToast('已修改');
    renderFeatures();
}
function deleteEditFeature(){
    if(!editingFeatureUrl)return;
    const features=getFeatures();
    const idx = features.findIndex(x => x.url === editingFeatureUrl);
    if(idx===-1)return;

    const favs=getFavs();
    delete favs['feature-'+features[idx].name];
    saveFavs(favs);

    features.splice(idx,1);
    saveFeatures(features);
    closeEditFeatureModal();
    showToast('已删除');
    renderFeatures();
}

function openSiteModal(){document.getElementById('siteModal').classList.add('show');document.getElementById('siteIcon').value='';document.getElementById('siteUrl').value='';document.getElementById('siteTitle').value='';document.getElementById('siteDesc').value='';document.getElementById('siteNewCatField').style.display='none';document.getElementById('siteNewSubField').style.display='none';document.getElementById('siteNewCatName').value='';document.getElementById('siteNewSubName').value='';const cats=getSiteCats();const catSelect=document.getElementById('siteCat');catSelect.innerHTML='<option value="">主分类</option>';Object.keys(cats).forEach(cat=>{catSelect.innerHTML+=`<option value="${cat}">${cat}</option>`});catSelect.innerHTML+='<option value="__new__">+ 新建主分类</option>';updateSubCatOptions()}
function closeSiteModal(){document.getElementById('siteModal').classList.remove('show')}
document.getElementById('siteCat').addEventListener('change',function(){if(this.value==='__new__'){document.getElementById('newCatField').style.display='block'}else{document.getElementById('newCatField').style.display='none'}updateSubCatOptions()});
document.getElementById('siteSubCat').addEventListener('change',function(){if(this.value==='__new__'){document.getElementById('newSubField').style.display='block'}else{document.getElementById('newSubField').style.display='none'}});
function updateSubCatOptions(){const cats=getSiteCats();const mainCat=document.getElementById('siteCat').value;const subSelect=document.getElementById('siteSubCat');subSelect.innerHTML='<option value="">子分类</option>';if(mainCat&&mainCat!=='__new__'&&cats[mainCat]){cats[mainCat].forEach(sub=>{subSelect.innerHTML+=`<option value="${sub}">${sub}</option>`})}else{Object.values(cats).forEach(subs=>{subs.forEach(sub=>{subSelect.innerHTML+=`<option value="${sub}">${sub}</option>`})})}subSelect.innerHTML+='<option value="__new__">+ 新建子分类</option>'}
function fetchSiteFavicon(){const url=document.getElementById('siteUrl').value;if(url){try{const u=new URL(url);if(!document.getElementById('siteTitle').value){document.getElementById('siteTitle').placeholder=u.hostname}}catch(e){}}}
function addSite(){const url=document.getElementById('siteUrl').value.trim();let title=document.getElementById('siteTitle').value.trim();const desc=document.getElementById('siteDesc').value.trim();let cat=document.getElementById('siteCat').value;let sub=document.getElementById('siteSubCat').value;const icon=document.getElementById('siteIcon').value.trim();if(!url){showToast('请输入网站URL');return}if(!title){try{title=new URL(url).hostname}catch(e){title=url}}if(!cat||cat==='__new__'){const newCat=document.getElementById('siteNewCatName').value.trim();if(!newCat){showToast('请输入主分类名称');return}cat=newCat}if(sub==='__new__'){const newSub=document.getElementById('siteNewSubName').value.trim();if(!newSub){showToast('请输入子分类名称');return}sub=newSub}const cats=getSiteCats();if(!cats[cat])cats[cat]=[];if(sub&&!cats[cat].includes(sub))cats[cat].push(sub);saveSiteCats(cats);const sites=getSites();sites.push({title,url,desc,cat,sub:sub||'',icon});saveSites(sites);closeSiteModal();renderSiteLayout();showToast('已添加')}

function handleFab(){if(currentPage===2)openFeatureModal();else if(currentPage===3)openSiteModal()}
function showToast(m){const t=document.getElementById('toast');t.textContent=m;t.style.display='block';setTimeout(()=>{t.style.display='none'},1200)}

// ===== Init =====
initBlockCtx();
initTimestamps();
updateLoginState().then(()=>{buildHome();renderFeatures();initSites();renderSiteLayout()});

// ===== Poll for tool changes (auto-reload when backend files change) =====
let lastToolHash='';
function pollToolChanges(){if(currentPage!==0)return;fetch('?action=scan_tools&t='+Date.now()).then(r=>r.json()).then(d=>{if(lastToolHash&&lastToolHash!==d.hash){location.reload()}lastToolHash=d.hash}).catch(()=>{})}
setInterval(pollToolChanges,5000);
</script>
</body>
</html>
