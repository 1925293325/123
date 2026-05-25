<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="<?php \Widget\Options::alloc()->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>微语 - <?php \Widget\Options::alloc()->title(); ?></title>
    <style>
        body { margin: 0; background: #f2f3f5; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; -webkit-font-smoothing: antialiased; }
        .wy-container { max-width: 680px; margin: 0 auto; min-height: 100vh; background: #fff; }
        .wy-container .wy-imgs { max-width: 100%; overflow: hidden; }
        .wy-container .wy-imgs img { max-width: 100%; height: auto; }
    </style>
</head>
<body>
    <div class="wy-container">
        <?php include __DIR__ . '/widget-weiyu.php'; ?>
    </div>
</body>
</html>
