<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$options = \Widget\Options::alloc();
$user = \Widget\User::alloc();
$config = $options->plugin('Weiyu');
$token = \TypechoPlugin\Weiyu\Plugin::getToken();
$isLogin = $user->hasLogin();
$isAdmin = $isLogin && $user->pass('administrator', true);
$allowGuestPost = intval($config->allowGuestPost ?? '1');

$cover = $config->cover ?: 'https://ty2.a.kwimgs.com/upic/2023/09/30/20/BMjAyMzA5MzAyMDIyMjFfMjExNjM2Nzc2OV8xMTM5NTMyNjk2MzNfMl8z_B96ca5b2783d3a7ea01e5495c83f65d3e.jpg?tag=1-1778949686-sr-0-yxehx1gian-13c47b8c11c3972a&clientCacheKey=3xq4ucc8jkyktgc.jpg&di=8821e1f&bp=10001';
$coverVideo = $config->coverVideo ?? '';
$videoPlayMode = $config->videoPlayMode ?? 'click';
$videoFrame = $config->videoFrame ?? 'first';
$nick = $isLogin ? $user->screenName : '博主';
$topAvatar = $isLogin ? \TypechoPlugin\Weiyu\Plugin::getAvatar($user->mail) 
    : $options->pluginUrl . '/Weiyu/logo.png';
?>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", sans-serif; background: #f2f3f5; color: #1a1a1a; }

.wy-wrap { max-width: 680px; margin: 0 auto; position: relative; padding: 0 5px; }

/* ===== 顶图 ===== */
.wy-header-wrap { position: relative; margin-bottom: 48px; }
.wy-cover { position: relative; width: 100%; height: 180px; overflow: visible; background: #2c3e50; }
.wy-cover-img { position: absolute; inset: 0; background: url('<?php echo $cover; ?>') center/cover no-repeat; }
.wy-cover-video { position: absolute; inset: 0; }
.wy-cover-video video { width: 100%; height: 100%; object-fit: cover; }
.wy-cover-video.has-poster::after { content: ''; position: absolute; inset: 0; background: inherit; pointer-events: none; }
.wy-cover-mask { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.45) 100%); }
.wy-cover-meta { position: absolute; bottom: 6px; right: 80px; z-index: 20; display: flex; align-items: center; height: 40px; }
.wy-cover-name { font-size: 16px; color: #fff; font-weight: 600; letter-spacing: 1px; line-height: 1.2; background: rgba(0,0,0,0.35); padding: 4px 10px; border-radius: 6px; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); margin-top:46px;}
.wy-cover-avatar-wrap { 
    position: absolute;
    bottom: -10px;
    right: 20px;
    width: 56px;
    height: 56px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.35);
    z-index: 100;
    padding: 0;
    border: 2px solid rgba(255,255,255,0.95);
    overflow: hidden;
}
.wy-cover-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; border-radius: 14px; display: block; }
.wy-topbar { position: absolute; top: 10px; right: 10px; z-index: 40; display: flex; gap: 8px; align-items: center; }
.wy-top-btn { padding: 5px 14px; border-radius: 20px; font-size: 13px; cursor: pointer; backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25); color: #fff; background: rgba(0,0,0,0.35); transition: all .2s; line-height: 1.4; }
.wy-top-btn:hover { background: rgba(0,0,0,0.55); }

/* ===== 列表 ===== */
.wy-list { padding: 0 7px; }
.wy-item { background: #fff; border-radius: 12px; padding: 12px 8px; margin-bottom: 12px; }
.wy-item-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.wy-item-avatar { width: 32px !important; height: 32px !important; border-radius: 50% !important; overflow: hidden !important; flex-shrink: 0 !important; background: #f0f0f0 !important; }
.wy-item-avatar img { width: 100% !important; height: 100% !important; object-fit: cover !important; border-radius: 50% !important; display: block; flex-shrink: 0 !important; }
.wy-item-meta { flex: 1; min-width: 0; display: flex; align-items: center; gap: 4px; }
.wy-item-nick { font-size: 15px; color: #576b95; font-weight: 600; line-height: 1; display: flex; align-items: center; }
.wy-item-badge { width: 14px; height: 14px; background: transparent; display: none; align-items: center; justify-content: center; flex-shrink: 0; }
.wy-item-badge.show { display: flex; }
.wy-item-badge svg { width: 14px; height: 14px; fill: #5396FF; display: block; }
.wy-item-title { font-size: 16px; color: #1a1a1a; font-weight: 600; line-height: 1.4; margin-bottom: 8px; }
.wy-item-text { font-size: 15px; color: #1a1a1a; line-height: 1.65; word-break: break-word; white-space: pre-wrap; margin-bottom: 10px; }
.wy-item-media { margin-bottom: 10px; }
.wy-item-media img { width: 80px; height: 80px; object-fit: cover; border-radius: 4px; }
.wy-item .wy-info { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 8px; border-top: 1px solid #f0f0f0; position: relative; }

/* 图片 */
.wy-imgs { display: grid; gap: 2px; margin-bottom: 8px; }
.wy-imgs.g1 { grid-template-columns: 120px; justify-items: start; }
.wy-imgs.g1 img { width: 120px; height: 120px; object-fit: cover; border-radius: 3px; }
.wy-imgs.g2, .wy-imgs.g4 { grid-template-columns: repeat(2, 120px); }
.wy-imgs.g2 img, .wy-imgs.g4 img { width: 120px; height: 120px; object-fit: cover; border-radius: 3px; }
.wy-imgs.g3, .wy-imgs.g5, .wy-imgs.g6, .wy-imgs.g7, .wy-imgs.g8, .wy-imgs.g9 { grid-template-columns: repeat(3, 120px); }
.wy-imgs.g3 img, .wy-imgs.g5 img, .wy-imgs.g6 img, .wy-imgs.g7 img, .wy-imgs.g8 img, .wy-imgs.g9 img { width: 120px; height: 120px; object-fit: cover; border-radius: 3px; }

/* +卡片 */
.wy-music{display:flex;align-items:stretch;gap:0;background:#f5f5f5;border-radius:8px;padding:0;margin:10px 0;overflow:hidden;cursor:pointer;position:relative;}
.wy-music-cover { width: 80px; height: 80px; position: relative; flex-shrink: 0; }
.wy-music-cover img { width: 100%; height: 100%; object-fit: cover; }
.wy-music-cover .overlay { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
.wy-music-cover .play-btn { width: 36px; height: 36px; background: rgba(255,255,255,0.9); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.wy-music-cover .play-btn svg { width: 14px; height: 14px; fill: #333; margin-left: 2px; }
.wy-music-cover .note-wrap { position: absolute; bottom: 4px; left: 4px; width: 20px; height: 20px; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); border-radius: 50%; display: flex; align-items: center; justify-content: center; z-index: 2; }
.wy-music-cover .note-icon { width: 12px; height: 12px; fill: #fff; }
.wy-music.playing .wy-music-cover .note-wrap { animation: rotate 1.5s linear infinite; }
@keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
.wy-music-info { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; padding: 8px 12px; position: relative; overflow: hidden; }
.wy-music-bg { position: absolute; inset: 0; background-size: cover; background-position: center; filter: blur(20px); -webkit-filter: blur(20px); opacity: 0.5; z-index: 0; }
.wy-music-content { position: relative; z-index: 1; }
.wy-music-title { font-size: 14px; color: #fff; font-weight: 600; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 4px; text-shadow: 0 1px 2px rgba(0,0,0,0.6); }
.wy-music-artist { font-size: 12px; color: #f5f5f5; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-shadow: 0 1px 2px rgba(0,0,0,0.6); }
.wy-music-play { display: none; }

/* 视频 */
.wy-video{margin:10px 0;width:100%;}
.wy-video video{width:100%;max-height:160px;border-radius:8px;object-fit:cover;display:block;}


/* 底部信息 */
.wy-info { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 8px; border-top: 1px solid #f0f0f0; position: relative; }
.wy-time { font-size: 12px; color: #b0b0b0; }
.wy-del { color: #ff3b30; margin-left: 8px; cursor: pointer; font-size: 12px; }
.wy-like-wrap { display: flex; align-items: center; gap: 4px; cursor: pointer; }
.wy-like-icon { width: 18px; height: 18px; fill: none; stroke: #576b95; stroke-width: 2; transition: all .2s; }
.wy-like-icon.liked { fill: #ff2d55; stroke: #ff2d55; }
.wy-like-count { font-size: 13px; color: #576b95; }
.wy-comment-icon { width: 18px; height: 18px; fill: none; stroke: #576b95; stroke-width: 2; cursor: pointer; margin-left: 12px; }

/* 互动区 */
.wy-social { background: #f7f7f7; border-radius: 4px; margin-top: 10px; padding: 8px 12px; }
.wy-likes{display:none;}.wy-likes:empty{display:none;padding:0;border:none;margin:0;}.lk-name{font-size:13px;color:#576b95;}.lk-sep{font-size:13px;color:#576b95;}
.lk-heart{width:14px;height:14px;margin-right:6px;flex-shrink:0;color:#576b95;}

/* 评论 */
.wy-cm-list {  }
.wy-cm { font-size: 14px; line-height: 1.55; color: #1a1a1a; padding: 5px 0; cursor: pointer; display: flex; align-items: flex-start; gap: 6px; border-bottom: 1px solid #e8e8e8; }
.wy-cm:last-child { border-bottom: none; }
.wy-cm .cm-avatar{width:18px;height:18px;border-radius:3px;overflow:hidden;flex-shrink:0;margin-top:1px;background:#eee;}
.wy-cm .cm-avatar img { width: 100%; height: 100%; object-fit: cover; }
.wy-cm .cm-body { flex: 1; }
.wy-cm .cm-name { color: #576b95; font-weight: 500; }
.wy-cm .cm-re { color: #666; }
.wy-cm .cm-to { color: #576b95; font-weight: 500; }

/* ===== 登录弹窗 ===== */
.wy-modal-mask { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 300; }
.wy-modal-mask.show { display: block; }
.wy-modal { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 16px; padding: 28px 24px 24px; width: 86%; max-width: 340px; z-index: 301; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.wy-modal.show { display: block; }
.wy-modal h4 { text-align: center; font-size: 17px; margin-bottom: 20px; color: #1a1a1a; }
.wy-modal input { width: 100%; padding: 12px; border: 1px solid #e8e8e8; border-radius: 8px; font-size: 15px; outline: none; margin-bottom: 12px; background: #fafafa; }
.wy-modal input:focus { border-color: #07c160; background: #fff; }
.wy-modal button { width: 100%; padding: 12px; background: #07c160; color: #fff; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; margin-top: 4px; font-weight: 500; }
.wy-modal button:active { opacity: 0.9; }
.wy-modal .wy-modal-close { position: absolute; top: 12px; right: 16px; color: #999; font-size: 20px; cursor: pointer; line-height: 1; }

/* ===== 发帖大弹窗 ===== */
.wy-post-mask { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 400; }
.wy-post-mask.show { display: block; }
.wy-post-box { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 16px; width: 90%; max-width: 420px; z-index: 401; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.wy-post-box.show { display: block; }
.wy-post-header { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid #f5f5f5; }
.wy-post-cancel { font-size: 15px; color: #666; cursor: pointer; border: none; background: none; }
.wy-post-title { font-size: 16px; font-weight: 600; color: #1a1a1a; position: absolute; left: 50%; transform: translateX(-50%); }
.wy-post-submit { font-size: 15px; color: #07c160; font-weight: 500; cursor: pointer; border: none; background: none; }
.wy-post-body { padding: 16px; max-height: 60vh; overflow-y: auto; }
.wy-post-row { display: flex; gap: 12px; }
.wy-post-avatar { width: 44px; height: 44px; border-radius: 8px; overflow: hidden; flex-shrink: 0; background: #f0f0f0; }
.wy-post-avatar img { width: 100%; height: 100%; object-fit: cover; }
.wy-post-content { flex: 1; min-width: 0; }
.wy-post-title-input { width: 100%; border: none; outline: none; font-size: 16px; font-weight: 600; color: #1a1a1a; margin-bottom: 8px; padding: 0; }
.wy-post-title-input::placeholder { color: #999; }
.wy-post-textarea { width: 100%; border: none; outline: none; font-size: 15px; color: #1a1a1a; line-height: 1.6; resize: none; height: 80px; padding: 0; }
.wy-post-textarea::placeholder { color: #999; }
.wy-post-textarea { flex: 1; border: none; outline: none; font-size: 16px; line-height: 1.6; resize: none; min-height: 100px; font-family: inherit; color: #1a1a1a; }
.wy-post-textarea::placeholder { color: #b2b2b2; }
.wy-post-tools { display: flex; gap: 24px; padding: 12px 16px; border-top: 1px solid #f5f5f5; }
.wy-post-tool { display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; color: #576b95; }
.wy-post-tool svg { width: 20px; height: 20px; }

/* 小弹框（图片/音乐/视频） */
.wy-sub-mask { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 500; }
.wy-sub-mask.show { display: block; }
.wy-sub-box { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 12px; padding: 20px; width: 86%; max-width: 340px; z-index: 501; }
.wy-sub-box.show { display: block; }
.wy-sub-title { font-size: 16px; font-weight: 600; margin-bottom: 14px; text-align: center; }
.wy-sub-input { width: 100%; padding: 10px; border: 1px solid #e8e8e8; border-radius: 6px; font-size: 14px; outline: none; margin-bottom: 10px; font-family: inherit; }
.wy-sub-input:focus { border-color: #07c160; }
.wy-sub-btns { display: flex; gap: 10px; margin-top: 6px; }
.wy-sub-btns button { flex: 1; padding: 10px; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; }
.wy-sub-btns .wy-sub-cancel { background: #f5f5f5; color: #666; }
.wy-sub-btns .wy-sub-ok { background: #07c160; color: #fff; }

/* 已选附件提示 */
.wy-post-attachments { padding: 0 16px 10px; }
.wy-post-attachments:empty { display: none; }
.wy-attach-tag { display: inline-flex; align-items: center; gap: 4px; background: #f0f9f4; color: #07c160; padding: 4px 10px; border-radius: 12px; font-size: 12px; margin-right: 6px; margin-bottom: 4px; }
.wy-attach-tag .wy-attach-del { cursor: pointer; font-size: 14px; }

/* 评论输入 */
.wy-input-mask { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 200; }
.wy-input-mask.show { display: block; }
.wy-input-box { display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #f7f7f7; padding: 12px 16px 80px; z-index: 201; border-top: 1px solid #e0e0e0; animation: slideUp .2s ease; }
.wy-input-box.show { display: block; }
@keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
.wy-input-meta { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
.wy-input-meta input { flex: 1; min-width: 80px; padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px; outline: none; background: #fff; }
.wy-input-meta input:focus { border-color: #07c160; }
.wy-input-box textarea { width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 15px; outline: none; resize: none; height: 70px; background: #fff; }
.wy-input-box button { margin-top: 10px; padding: 9px 22px; background: #07c160; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; float: right; font-weight: 500; }
.wy-input-box button:active { opacity: 0.9; }
.wy-input-hint { font-size: 12px; color: #999; margin-top: 12px; float: left; }

.wy-loading { text-align: center; padding: 28px; color: #ccc; font-size: 13px; }
.wy-toast { position: fixed; top: 22%; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.78); color: #fff; padding: 10px 22px; border-radius: 20px; font-size: 14px; z-index: 99999; pointer-events: none; animation: toastIn .25s ease; }
@keyframes toastIn { from { opacity: 0; transform: translateX(-50%) translateY(-10px); } to { opacity: 1; transform: translateX(-50%) translateY(0); } }
.wy-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.92); z-index: 9999; display: flex; align-items: center; justify-content: center; cursor: zoom-out; }
.wy-overlay img { max-width: 92%; max-height: 92%; object-fit: contain; border-radius: 4px; }
</style>

<div class="wy-wrap">
    <div class="wy-header-wrap">
        <div class="wy-cover">
            <div class="wy-cover-img"></div>
            <?php if (!empty($coverVideo)): ?>
            <div class="wy-cover-video" id="wy-cover-video" data-mode="<?php echo $videoPlayMode; ?>" data-frame="<?php echo $videoFrame; ?>">
                <video <?php echo ($videoPlayMode === 'refresh') ? 'autoplay' : ''; ?> muted playsinline<?php echo ($videoPlayMode === 'refresh') ? ' onloadstart="this.play()"' : ''; ?><?php echo ($videoFrame === 'last') ? ' onended="this.currentTime=this.duration-(1/this.playbackRate)"' : ''; ?>>
                    <source src="<?php echo htmlspecialchars($coverVideo); ?>" type="video/mp4">
                </video>
            </div>
            <?php endif; ?>
            <div class="wy-cover-mask"></div>
            <div class="wy-cover-meta">
                <div class="wy-cover-name" id="top-nick"><?php echo htmlspecialchars($nick); ?></div>
            </div>
            <div class="wy-cover-avatar-wrap" id="top-avatar-wrap">
                <img src="<?php echo $topAvatar; ?>" alt="" id="top-avatar-img">
            </div>
            <div class="wy-topbar">
                <span class="wy-top-btn" id="btn-login" onclick="openLogin()">登录</span>
                <span class="wy-top-btn" id="btn-post" onclick="openPostBox()" style="display:none;">发帖</span>
                <span class="wy-top-btn" id="btn-logout" onclick="doLogout()" style="display:none;">退出</span>
            </div>
        </div>
    </div>

    <div class="wy-list" id="wy-list"></div>
    <div class="wy-loading" id="wy-loading">加载中...</div>
</div>

<!-- 登录弹窗 -->
<div class="wy-modal-mask" id="login-mask" onclick="closeLogin()"></div>
<div class="wy-modal" id="login-box">
    <div class="wy-modal-close" onclick="closeLogin()">×</div>
    <h4>游客登录</h4>
    <input type="text" id="lg-name" placeholder="昵称">
    <input type="email" id="lg-mail" placeholder="邮箱">
    <input type="text" id="lg-url" placeholder="网址（可选）">
    <button onclick="doLogin()">登录</button>
</div>

<!-- 发帖大弹窗 -->
<div class="wy-post-mask" id="post-mask" onclick="closePostBox()"></div>
<div class="wy-post-box" id="post-box">
    <div class="wy-post-header">
        <button class="wy-post-cancel" onclick="closePostBox()">取消</button>
        <div class="wy-post-title">发表文字</div>
        <button class="wy-post-submit" onclick="submitPost()">发表</button>
    </div>
    <div class="wy-post-body">
        <div class="wy-post-row">
            <div class="wy-post-avatar" id="post-avatar">
                <img src="<?php echo $topAvatar; ?>" alt="">
            </div>
            <div class="wy-post-content">
                <input type="text" class="wy-post-title-input" id="post-title" placeholder="标题（可选）" maxlength="50">
                <textarea class="wy-post-textarea" id="post-content" placeholder="这一刻的想法..."></textarea>
            </div>
        </div>
    </div>
    <div class="wy-post-attachments" id="post-attachments"></div>
    <div class="wy-post-tools">
        <div class="wy-post-tool" onclick="openSub('img')">
            <svg t="1778950495769" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1689" width="200" height="200"><path d="M986.112 446.7712a38.4 38.4 0 0 0 38.4-38.4V144.128a140.9536 140.9536 0 0 0-140.8-140.8L140.7488 3.6864a140.9536 140.9536 0 0 0-140.8 140.8v735.3856a140.9536 140.9536 0 0 0 140.8 140.8l742.9632-0.4096a140.9536 140.9536 0 0 0 140.8-140.8V588.288c0-3.6864-1.1264-7.0144-2.0992-10.3936a37.8368 37.8368 0 0 0-11.9808-29.5936L785.8176 342.6304c-26.0096-23.8592-65.8432-24.576-96.2048 1.9968l-163.1232 182.8864-146.2272-84.9408a70.8608 70.8608 0 0 0-53.3504-13.6192 70.3488 70.3488 0 0 0-44.544 26.4704L179.6096 563.8656a38.4 38.4 0 0 0 55.7056 52.8384l103.8336-109.568 145.9712 84.8384c25.9584 20.0192 62.976 18.9952 91.5968-5.888l162.3552-182.1184 208.5888 191.0272v284.4672c0 35.2768-28.7232 64-64 64l-742.912 0.4096c-35.2768 0-64-28.7232-64-64V144.4864c0-35.2768 28.7232-64 64-64l742.9632-0.4096c35.2768 0 64 28.7232 64 64v264.2944c0 21.1968 17.2032 38.4 38.4 38.4z" fill="#438CFF" p-id="1690"></path><path d="M264.4992 248.4224m-49.664 0a49.664 49.664 0 1 0 99.328 0 49.664 49.664 0 1 0-99.328 0Z" fill="#438CFF" p-id="1691"></path></svg>
            图片
        </div>
        <div class="wy-post-tool" onclick="openSub('music')">
           <svg t="1779060681320" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2642" width="200" height="200"><path d="M513 138.02c50.01 0 98.49 9.78 144.08 29.06 44.08 18.64 83.68 45.35 117.7 79.37s60.73 73.63 79.37 117.7c19.28 45.59 29.06 94.07 29.06 144.08 0 50.01-9.78 98.49-29.06 144.08-18.64 44.08-45.35 83.68-79.37 117.7s-73.63 60.73-117.7 79.37c-45.59 19.28-94.07 29.06-144.08 29.06s-98.49-9.78-144.08-29.06c-44.08-18.64-83.68-45.35-117.7-79.37s-60.73-73.63-79.37-117.7c-19.28-45.59-29.06-94.07-29.06-144.08 0-50.01 9.78-98.49 29.06-144.08 18.64-44.08 45.35-83.68 79.37-117.7s73.63-60.73 117.7-79.37c45.59-19.28 94.07-29.06 144.08-29.06m0-76.35c-246.64 0-446.57 199.94-446.57 446.57 0 246.64 199.94 446.57 446.57 446.57s446.57-199.94 446.57-446.57C959.57 261.6 759.64 61.67 513 61.67z" fill="#231815" p-id="2643"></path><path d="M424.36 624.79m-148.69 0a148.69 148.69 0 1 0 297.38 0 148.69 148.69 0 1 0-297.38 0Z" fill="#F7B52C" p-id="2644"></path><path d="M677.89 426.14L556.76 692.01 417.79 628.7l168.39-369.61z" fill="#F7B52C" p-id="2645"></path><path d="M765.34 508.53L523.36 398.29l63.31-138.97z" fill="#F7B52C" p-id="2646"></path></svg>
            音乐
        </div>
        <div class="wy-post-tool" onclick="openSub('video')">
           <svg t="1778950601542" class="icon" viewBox="0 0 1241 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2715" width="200" height="200"><path d="M193.84646453 62h872.72727276c60.00000029 0 109.09090898 49.09090869 109.09090899 109.09090898v681.81818204c0 60.00000029-49.09090869 109.09090898-109.09090899 109.09090898H193.84646453c-60.00000029 0-109.09090898-49.09090869-109.09090898-109.09090898V171.09090898c0-60.00000029 49.09090869-109.09090898 109.09090898-109.09090898z" fill="#2F77F1" p-id="2716"></path><path d="M84.75555555 225.63636348h1090.90909072v54.54545449H84.75555555v-54.5454545z m673.63636377 340.90909101c9.5454545 5.4545458 15.00000029 16.36363653 15.0000003 27.27272725s-5.4545458 21.81818145-15.0000003 27.27272724L589.30101004 726.09090928c-9.5454545 5.4545458-21.81818145 5.4545458-31.36363594-1e-8-9.5454545-5.4545458-16.36363653-16.36363653-15.00000029-27.27272724V490.18181855c0-10.90909073 5.4545458-21.81818145 15.00000029-27.27272724 9.5454545-5.4545458 21.81818145-5.4545458 31.36363594 0L758.39191932 566.54545449z" fill="#AFFCFE" p-id="2717"></path></svg>
            视频
        </div>
    </div>
</div>

<!-- 图片弹框 -->
<div class="wy-sub-mask" id="sub-mask-img" onclick="closeSub('img')"></div>
<div class="wy-sub-box" id="sub-box-img">
    <div class="wy-sub-title">添加图片</div>
    <textarea class="wy-sub-input" id="sub-img-input" rows="4" placeholder="每行一个图片链接"></textarea>
    <div class="wy-sub-btns">
        <button class="wy-sub-cancel" onclick="closeSub('img')">取消</button>
        <button class="wy-sub-ok" onclick="saveSub('img')">确定</button>
    </div>
</div>

<!-- 音乐弹框 -->
<div class="wy-sub-mask" id="sub-mask-music" onclick="closeSub('music')"></div>
<div class="wy-sub-box" id="sub-box-music">
    <div class="wy-sub-title">添加音乐</div>
    <input type="text" class="wy-sub-input" id="sub-music-cover" placeholder="封面链接">
    <input type="text" class="wy-sub-input" id="sub-music-title" placeholder="歌曲名称">
    <input type="text" class="wy-sub-input" id="sub-music-artist" placeholder="歌手">
    <input type="text" class="wy-sub-input" id="sub-music-link" placeholder="音乐链接 (mp3)">
    <div class="wy-sub-btns">
        <button class="wy-sub-cancel" onclick="closeSub('music')">取消</button>
        <button class="wy-sub-ok" onclick="saveSub('music')">确定</button>
    </div>
</div>

<!-- 视频弹框 -->
<div class="wy-sub-mask" id="sub-mask-video" onclick="closeSub('video')"></div>
<div class="wy-sub-box" id="sub-box-video">
    <div class="wy-sub-title">添加视频</div>
    <input type="text" class="wy-sub-input" id="sub-video-link" placeholder="视频链接 (mp4)">
    <input type="text" class="wy-sub-input" id="sub-video-cover" placeholder="视频封面链接">
    <div class="wy-sub-btns">
        <button class="wy-sub-cancel" onclick="closeSub('video')">取消</button>
        <button class="wy-sub-ok" onclick="saveSub('video')">确定</button>
    </div>
</div>

<!-- 评论弹窗 -->
<div class="wy-input-mask" id="input-mask" onclick="closeComment()"></div>
<div class="wy-input-box" id="input-box">
    <div class="wy-input-meta" id="cm-meta">
        <input type="text" id="cm-author" placeholder="昵称">
        <input type="email" id="cm-mail" placeholder="邮箱">
        <input type="text" id="cm-url" placeholder="网址">
    </div>
    <textarea id="cm-text" placeholder="评论..."></textarea>
    <div style="overflow:hidden;">
        <span class="wy-input-hint" id="cm-hint"></span>
        <button onclick="submitCm()">发送</button>
    </div>
</div>

<script>
const API = '<?php echo rtrim($options->index, '/'); ?>/weiyu/action';
const TOKEN = '<?php echo $token; ?>';
const TYPECHO_LOGIN = <?php echo $isLogin ? 'true' : 'false'; ?>;
const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;
const ALLOW_GUEST_POST = <?php echo $allowGuestPost; ?>;
let currentPage = 1, totalPage = 1, isLoading = false, replyTo = 0, activePostId = 0;

// 音频全局
let wyAudio = null;
let wyAudioId = null;

// 发帖附件数据
let postData = { images: '', musicCover: '', musicTitle: '', musicArtist: '', musicLink: '', videoLink: '', videoCover: '' };

function checkGuestLogin() {
    try {
        const name = localStorage.getItem('wy_guest_name');
        const mail = localStorage.getItem('wy_guest_mail');
        return !!(name && mail);
    } catch (e) { return false; }
}

function getGuestInfo() {
    try {
        return {
            name: localStorage.getItem('wy_guest_name') || '',
            mail: localStorage.getItem('wy_guest_mail') || '',
            url: localStorage.getItem('wy_guest_url') || ''
        };
    } catch (e) { return { name: '', mail: '', url: '' }; }
}

function getCurrentAvatar() {
    if (TYPECHO_LOGIN) return '<?php echo $isLogin ? \TypechoPlugin\Weiyu\Plugin::getAvatar($user->mail) : ''; ?>';
    const g = getGuestInfo();
    if (g.mail) {
        const mail = g.mail.toLowerCase().trim();
        if (/^(\d+)@qq\.com$/.test(mail)) {
            return 'https://q.qlogo.cn/headimg_dl?dst_uin=' + mail.match(/^(\d+)@qq\.com$/)[1] + '&spec=100';
        }
        return 'https://gravatar.loli.net/avatar/' + hexMD5(mail) + '?s=200&d=identicon';
    }
    return 'https://gravatar.loli.net/avatar/000?s=200&d=identicon';
}

function getCurrentName() {
    if (TYPECHO_LOGIN) return '<?php echo $isLogin ? $user->screenName : ''; ?>';
    const g = getGuestInfo();
    return g.name || '游客';
}

/* MD5 */
function hexMD5(string) {
    function RotateLeft(lValue, iShiftBits) {
        return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
    }
    function AddUnsigned(lX,lY) {
        var lX4,lY4,lX8,lY8,lResult;
        lX8 = (lX & 0x80000000);
        lY8 = (lY & 0x80000000);
        lX4 = (lX & 0x40000000);
        lY4 = (lY & 0x40000000);
        lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
        if (lX4 & lY4) return (lResult ^ 0x80000000 ^ lY8 ^ lX8);
        if (lX4 | lY4) {
            if (lResult & 0x40000000) return (lResult ^ 0xC0000000 ^ lY8 ^ lX8);
            else return (lResult ^ 0x40000000 ^ lY8 ^ lX8);
        } else return (lResult ^ lY8 ^ lX8);
    }
    function F(x,y,z) { return (x & y) | ((~x) & z); }
    function G(x,y,z) { return (x & z) | (y & (~z)); }
    function H(x,y,z) { return (x ^ y ^ z); }
    function I(x,y,z) { return (y ^ (x | (~z))); }
    function FF(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function GG(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function HH(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function II(a,b,c,d,x,s,ac) {
        a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
        return AddUnsigned(RotateLeft(a, s), b);
    }
    function ConvertToWordArray(string) {
        var lWordCount;
        var lMessageLength = string.length;
        var lNumberOfWords_temp1=lMessageLength + 8;
        var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
        var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
        var lWordArray=Array(lNumberOfWords-1);
        var lBytePosition = 0;
        var lByteCount = 0;
        while ( lByteCount < lMessageLength ) {
            lWordCount = (lByteCount-(lByteCount % 4))/4;
            lBytePosition = (lByteCount % 4)*8;
            lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
            lByteCount++;
        }
        lWordCount = (lByteCount-(lByteCount % 4))/4;
        lBytePosition = (lByteCount % 4)*8;
        lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
        lWordArray[lNumberOfWords-2] = lMessageLength<<3;
        lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
        return lWordArray;
    }
    function WordToHex(lValue) {
        var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
        for (lCount = 0;lCount<=3;lCount++) {
            lByte = (lValue>>>(lCount*8)) & 255;
            WordToHexValue_temp = "0" + lByte.toString(16);
            WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
        }
        return WordToHexValue;
    }
    var x=Array();
    var k,AA,BB,CC,DD,a,b,c,d;
    var S11=7, S12=12, S13=17, S14=22;
    var S21=5, S22=9 , S23=14, S24=20;
    var S31=4, S32=11, S33=16, S34=23;
    var S41=6, S42=10, S43=15, S44=21;
    string = unescape(encodeURIComponent(string));
    x = ConvertToWordArray(string);
    a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;
    for (k=0;k<x.length;k+=16) {
        AA=a; BB=b; CC=c; DD=d;
        a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
        d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
        c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
        b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
        a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
        d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
        c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
        b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
        a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
        d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
        c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
        b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
        a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
        d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
        c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
        b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
        a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
        d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
        c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
        b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
        a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
        d=GG(d,a,b,c,x[k+10],S22,0x2441453);
        c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
        b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
        a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
        d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
        c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
        b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
        a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
        d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
        c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
        b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
        a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
        d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
        c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
        b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
        a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
        d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
        c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
        b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
        a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
        d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
        c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
        b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
        a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
        d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
        c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
        b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
        a=II(a,b,c,d,x[k+0], S41,0xF4292244);
        d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
        c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
        b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
        a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
        d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
        c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
        b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
        a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
        d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
        c=II(c,d,a,b,x[k+6], S43,0xA3014314);
        b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
        a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
        d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
        c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
        b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
        a=AddUnsigned(a,AA); b=AddUnsigned(b,BB); c=AddUnsigned(c,CC); d=AddUnsigned(d,DD);
    }
    var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
    return temp.toLowerCase();
}

function updateLoginUI() {
    const isGuest = checkGuestLogin();
    const btnLogin = document.getElementById('btn-login');
    const btnPost = document.getElementById('btn-post');
    const btnLogout = document.getElementById('btn-logout');

    const canPost = IS_ADMIN || (ALLOW_GUEST_POST && (TYPECHO_LOGIN || isGuest));

    if (TYPECHO_LOGIN || isGuest) {
        btnLogin.style.display = 'none';
        btnLogout.style.display = 'inline-block';
        btnPost.style.display = canPost ? 'inline-block' : 'none';
        document.getElementById('top-nick').textContent = getCurrentName();
        const avatar = getCurrentAvatar();
        if (avatar) {
            document.getElementById('top-avatar-img').src = avatar;
            document.querySelector('#post-avatar img').src = avatar;
        }
        // 博主登录后，顶图头像可点击，添加提示
        const avatarWrap = document.getElementById('top-avatar-wrap');
        avatarWrap.style.pointerEvents = 'auto';
        avatarWrap.style.cursor = 'pointer';
        avatarWrap.onclick = () => toast('博主已登录');
    } else {
        btnLogin.style.display = 'inline-block';
        btnPost.style.display = 'none';
        btnLogout.style.display = 'none';
        // 未登录时头像不可点击
        const avatarWrap = document.getElementById('top-avatar-wrap');
        avatarWrap.style.pointerEvents = 'none';
        avatarWrap.style.cursor = 'default';
        avatarWrap.onclick = null;
    }
}

/* ===== 发帖弹框 ===== */
function openPostBox() {
    if (!checkGuestLogin() && !TYPECHO_LOGIN) { openLogin(); return; }
    postData = { images: '', musicCover: '', musicTitle: '', musicArtist: '', musicLink: '', videoLink: '', videoCover: '' };
    document.getElementById('post-title').value = '';
    document.getElementById('post-content').value = '';
    updateAttachTags();
    document.getElementById('post-mask').classList.add('show');
    document.getElementById('post-box').classList.add('show');
    setTimeout(() => document.getElementById('post-content').focus(), 100);
}

function closePostBox() {
    document.getElementById('post-mask').classList.remove('show');
    document.getElementById('post-box').classList.remove('show');
}

function openSub(type) {
    document.getElementById('sub-mask-' + type).classList.add('show');
    document.getElementById('sub-box-' + type).classList.add('show');
    if (type === 'img') document.getElementById('sub-img-input').value = postData.images;
    if (type === 'music') {
        document.getElementById('sub-music-cover').value = postData.musicCover;
        document.getElementById('sub-music-title').value = postData.musicTitle;
        document.getElementById('sub-music-artist').value = postData.musicArtist;
        document.getElementById('sub-music-link').value = postData.musicLink;
    }
    if (type === 'video') {
        document.getElementById('sub-video-link').value = postData.videoLink;
        document.getElementById('sub-video-cover').value = postData.videoCover;
    }
}

function closeSub(type) {
    document.getElementById('sub-mask-' + type).classList.remove('show');
    document.getElementById('sub-box-' + type).classList.remove('show');
}

function saveSub(type) {
    if (type === 'img') {
        postData.images = document.getElementById('sub-img-input').value.trim();
    }
    if (type === 'music') {
        postData.musicCover = document.getElementById('sub-music-cover').value.trim();
        postData.musicTitle = document.getElementById('sub-music-title').value.trim();
        postData.musicArtist = document.getElementById('sub-music-artist').value.trim();
        postData.musicLink = document.getElementById('sub-music-link').value.trim();
    }
    if (type === 'video') {
        postData.videoLink = document.getElementById('sub-video-link').value.trim();
        postData.videoCover = document.getElementById('sub-video-cover').value.trim();
    }
    updateAttachTags();
    closeSub(type);
}

function updateAttachTags() {
    const box = document.getElementById('post-attachments');
    let html = '';
    if (postData.images) {
        const count = postData.images.split('\n').filter(l => l.trim()).length;
        html += `<span class="wy-attach-tag">🖼️ ${count}张图片<span class="wy-attach-del" onclick="clearAttach('images')">×</span></span>`;
    }
    if (postData.musicLink) {
        html += `<span class="wy-attach-tag">🎵 ${postData.musicTitle || '音乐'}<<span class="wy-attach-del" onclick="clearAttach('music')">×</span></span>`;
    }
    if (postData.videoLink) {
        html += `<span class="wy-attach-tag">🎬 视频<span class="wy-attach-del" onclick="clearAttach('video')">×</span></span>`;
    }
    box.innerHTML = html;
}

function clearAttach(key) {
    if (key === 'images') postData.images = '';
    if (key === 'music') { postData.musicCover = ''; postData.musicTitle = ''; postData.musicArtist = ''; postData.musicLink = ''; }
    if (key === 'video') { postData.videoLink = ''; postData.videoCover = ''; }
    updateAttachTags();
}

async function submitPost() {
    const title = document.getElementById('post-title').value.trim();
    const content = document.getElementById('post-content').value.trim();
    const imgCount = postData.images.split('\n').filter(l => l.trim()).length;
    if (imgCount > 4) return toast('最多只能上传 4 张图片');
    if (!content && !postData.images && !postData.musicLink && !postData.videoLink && !title) return toast('写点什么吧');

    const g = getGuestInfo();
    const body = `title=${encodeURIComponent(title)}` +
        `&content=${encodeURIComponent(content)}` +
        `&images=${encodeURIComponent(postData.images)}` +
        `&music_cover=${encodeURIComponent(postData.musicCover)}` +
        `&music_title=${encodeURIComponent(postData.musicTitle)}` +
        `&music_artist=${encodeURIComponent(postData.musicArtist)}` +
        `&music_link=${encodeURIComponent(postData.musicLink)}` +
        `&video_link=${encodeURIComponent(postData.videoLink)}` +
        `&video_cover=${encodeURIComponent(postData.videoCover)}` +
        `&guest_name=${encodeURIComponent(g.name)}&guest_mail=${encodeURIComponent(g.mail)}&guest_url=${encodeURIComponent(g.url)}`;

    try {
        const res = await fetch(`${API}?do=publish&_=${TOKEN}`, {
            method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body
        });
        const text = await res.text();
        let json;
        try { json = JSON.parse(text); } catch(e) { console.error('发布解析失败:', text); toast('发布失败'); return; }
        if (json.code === 200) {
            closePostBox();
            toast(json.msg);
            loadPosts(1);
        } else if (json.code === 401) {
            toast('请先登录');
        } else {
            toast(json.msg || '发布失败');
        }
    } catch(e) { toast('发布失败'); }
}

/* ===== 登录 ===== */
function openLogin() {
    document.getElementById('login-mask').classList.add('show');
    document.getElementById('login-box').classList.add('show');
}
function closeLogin() {
    document.getElementById('login-mask').classList.remove('show');
    document.getElementById('login-box').classList.remove('show');
}
function doLogin() {
    const name = document.getElementById('lg-name').value.trim();
    const mail = document.getElementById('lg-mail').value.trim();
    const url = document.getElementById('lg-url').value.trim();
    if (!name || !mail) return alert('请填写昵称和邮箱');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)) return alert('邮箱格式不正确');
    try {
        localStorage.setItem('wy_guest_name', name);
        localStorage.setItem('wy_guest_mail', mail);
        localStorage.setItem('wy_guest_url', url);
    } catch (e) {}
    closeLogin();
    updateLoginUI();
    toast('登录成功');
    loadPosts(1);
}
function doLogout() {
    try {
        localStorage.removeItem('wy_guest_name');
        localStorage.removeItem('wy_guest_mail');
        localStorage.removeItem('wy_guest_url');
    } catch (e) {}
    updateLoginUI();
    toast('已退出登录');
    setTimeout(() => location.reload(), 800);
}

function toast(msg) {
    const t = document.createElement('div'); t.className = 'wy-toast'; t.textContent = msg;
    document.body.appendChild(t); setTimeout(() => t.remove(), 1800);
}

document.addEventListener('DOMContentLoaded', () => {
    updateLoginUI();
    loadPosts();
    
    // 顶图视频点击播放处理
    const videoContainer = document.getElementById('wy-cover-video');
    if (videoContainer) {
        const mode = videoContainer.getAttribute('data-mode');
        const video = videoContainer.querySelector('video');
        
        if (mode === 'click' && video) {
            // 点击播放模式
            videoContainer.style.cursor = 'pointer';
            videoContainer.addEventListener('click', () => {
                if (video.paused) {
                    video.currentTime = 0;
                    video.play();
                }
            });
            
            // 播放结束后显示指定帧
            video.addEventListener('ended', () => {
                const frame = videoContainer.getAttribute('data-frame');
                if (frame === 'last') {
                    video.currentTime = video.duration - (1 / video.playbackRate);
                }
            });
        }
    }
    
    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 180) {
            if (!isLoading && currentPage < totalPage) loadPosts(currentPage + 1);
        }
    });
    document.addEventListener('click', (e) => {
    });
});

async function loadPosts(page = 1) {
    if (isLoading) return;
    isLoading = true;
    document.getElementById('wy-loading').textContent = '加载中...';
    try {
        const res = await fetch(`${API}?do=getPosts&page=${page}`);
        const text = await res.text();
        let json;
        try { json = JSON.parse(text); } catch(e) { console.error('JSON解析失败:', text); throw e; }
        if (json.code === 200) {
            totalPage = json.totalPage; currentPage = page;
            const list = document.getElementById('wy-list');
            if (page === 1) list.innerHTML = '';
            if (json.data.length === 0 && page === 1) list.innerHTML = '<div style="text-align:center;padding:60px;color:#ccc;">还没有微语</div>';
            json.data.forEach(post => list.appendChild(createItem(post)));
            const ld = document.getElementById('wy-loading');
            if (currentPage >= totalPage) ld.textContent = json.total > 0 ? '— 到底了 —' : '';
            else ld.textContent = '';
        } else {
            console.error('API错误:', json);
        }
    } catch (e) { console.error(e); document.getElementById('wy-loading').textContent = '加载失败'; }
    isLoading = false;
}

function createItem(post) {
    const div = document.createElement('div'); div.className = 'wy-item'; div.id = `post-${post.id}`;

    let imgHtml = '';
    if (post.images && post.images.length > 0) {
        const c = post.images.length;
        let cls = 'g3';
        if (c === 1) cls = 'g1';
        else if (c === 2 || c === 4) cls = 'g2';
        else if (c >= 5) cls = 'g' + (c > 9 ? 9 : c);
        imgHtml = `<div class="wy-imgs ${cls}">${post.images.map(img => `<img src="${img}" onclick="previewImage('${img}')" loading="lazy" alt="">`).join('')}</div>`;
    }

    let musicHtml = '';
    if (post.music_link) {
        const coverUrl = post.music_cover || 'https://via.placeholder.com/100';
        musicHtml = `<div class="wy-music" id="music-${post.id}" onclick="toggleMusic(${post.id}, '${post.music_link}')">
            <div class="wy-music-cover">
                <img src="${coverUrl}" alt="">
                <div class="overlay">
                    <div class="play-btn">
                        <svg class="icon-play" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        <svg class="icon-pause" viewBox="0 0 24 24" style="display:none;"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                    </div>
                </div>
                <div class="note-wrap"><svg class="note-icon" viewBox="0 0 24 24"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg></div>
            </div>
            <div class="wy-music-info">
                <div class="wy-music-bg" style="background-image: url('${coverUrl}');"></div>
                <div class="wy-music-content">
                    <div class="wy-music-title">${escapeHtml(post.music_title || '未知歌曲')}</div>
                    <div class="wy-music-artist">${escapeHtml(post.music_artist || '未知歌手')}</div>
                </div>
            </div>
        </div>`;
    }

    let videoHtml = '';
    if (post.video_link) {
        videoHtml = `<div class="wy-video">
            <video controls poster="${post.video_cover || ''}" src="${post.video_link}" preload="metadata"></video>
        </div>`;
    }

    let likesHtml='';if(post.like_list&&post.like_list.length>0){const names=post.like_list.map(lk=>`<span class="lk-name">${escapeHtml(lk.name)}</span>`).join('<span class="lk-sep">，</span>');likesHtml=`<div class="wy-likes"><svg class="lk-heart" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>${names}</div>`;}

    let cmHtml = '';
    if (post.comments && post.comments.length > 0) {
       const cmItems=post.comments.map(c=>{const reply=c.is_child?`<span class="cm-re"> 回复 </span><span class="cm-to">${escapeHtml(c.parent_name)}</span>`:'';return`<div class="wy-cm" onclick="replyCm(${post.id},${c.id},'${escapeHtml(c.author)}')"><div class="cm-body"><span class="cm-name">${escapeHtml(c.author)}</span>${reply}：${escapeHtml(c.content)}</div></div>`;}).join('');

        cmHtml = `<div class="wy-cm-list">${cmItems}</div>`;
    }

    const delBtn = post.can_delete ? `<span class="wy-del" onclick="event.stopPropagation();deletePost(${post.id})">删除</span>` : '';
    const isAuthor = post.authorId > 0;

    div.innerHTML = `
        <div class="wy-item-header">
            <div class="wy-item-avatar"><img src="${post.author_avatar}" alt=""></div>
            <div class="wy-item-meta">
                <div class="wy-item-nick">${escapeHtml(post.author_name)}</div>
                ${isAuthor ? `<div class="wy-item-badge show" title="博主"><svg t="1779054523655" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4231"><path d="M956.672 459.776L862.72 397.568c-16.64-11.008-24.832-30.976-20.992-50.688l22.272-110.592c4.096-20.736-2.304-42.24-17.152-57.344-15.104-15.104-36.352-21.504-57.344-17.152l-110.592 22.272c-19.712 4.096-39.424-4.352-50.688-20.992L565.76 69.12c-11.776-17.664-31.488-28.16-52.736-28.16s-40.96 10.496-52.736 28.16l-62.464 93.952c-11.008 16.64-30.976 24.832-50.688 20.992l-110.592-22.272c-20.736-4.096-42.24 2.304-57.344 17.152-15.104 15.104-21.504 36.352-17.152 57.344l22.272 110.592c3.84 19.712-4.352 39.424-20.992 50.688l-93.952 62.464c-17.664 11.776-28.16 31.488-28.16 52.736s10.496 40.96 28.16 52.736l93.952 62.464c16.64 11.008 24.832 30.976 20.992 50.688l-22.272 110.592c-4.096 20.736 2.304 42.24 17.152 57.344 15.104 15.104 36.352 21.504 57.344 17.152l110.592-22.272c19.712-4.096 39.424 4.352 50.688 20.992l62.464 93.952c11.776 17.664 31.488 28.16 52.736 28.16s40.96-10.496 52.736-28.16l62.464-93.952c11.008-16.64 30.976-24.832 50.688-20.992l110.592 22.272c20.736 4.096 42.24-2.304 57.344-17.152 15.104-15.104 21.504-36.352 17.152-57.344l-22.272-110.592c-3.84-19.712 4.352-39.424 20.992-50.688l93.952-62.464c17.664-11.776 28.16-31.488 28.16-52.736s-10.496-41.216-28.16-52.992z m-249.344-22.016l-211.456 215.04c-5.888 6.144-13.824 9.216-22.016 9.216-7.168 0-14.592-2.56-20.224-7.68l-138.24-122.112c-12.8-11.264-13.824-30.72-2.816-43.264 11.264-12.8 30.72-13.824 43.264-2.816l116.48 102.912 190.976-194.304c11.776-12.032 31.232-12.288 43.52-0.256 12.288 11.52 12.288 30.976 0.512 43.264z" fill="#5396FF" p-id="4232"></path></svg></div>` : ''}
            </div>
        </div>
        ${post.title ? `<div class="wy-item-title">${escapeHtml(post.title)}</div>` : ''}
        <div class="wy-item-text">${escapeHtml(post.content)}</div>
        <div class="wy-item-media">
            ${imgHtml}
            ${videoHtml}
        </div>
        ${musicHtml}
        <div class="wy-info">
            <div class="wy-time">${post.created_format} ${delBtn}</div>
            <div style="display:flex;align-items:center;">
                <div class="wy-like-wrap" onclick="doLike(${post.id})">
                    <svg class="wy-like-icon ${post.has_liked ? 'liked' : ''}" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span class="wy-like-count">${post.likes || 0}</span>
                </div>
               <svg class="wy-comment-icon" viewBox="0 0 24 24" onclick="openComment(${post.id})"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            </div>
        </div>
        ${(likesHtml || cmHtml) ? `<div class="wy-social">${likesHtml}${cmHtml}</div>` : ''}
    `;
    return div;
}

async function doLike(id) {
    if (!checkGuestLogin() && !TYPECHO_LOGIN) { openLogin(); return; }
    const g = getGuestInfo();
    try {
        const res = await fetch(`${API}?do=like&post_id=${id}&guest_name=${encodeURIComponent(g.name)}&guest_mail=${encodeURIComponent(g.mail)}&_=${TOKEN}`);
        const text = await res.text();
        let json;
        try { json = JSON.parse(text); } catch(e) { console.error('点赞解析失败:', text); toast('操作失败'); return; }
        if (json.code === 200) { loadPosts(1); }
        else { toast(json.msg || '操作失败'); }
    } catch (e) { toast('操作失败'); }
}

/* ===== 音乐播放/暂停 ===== */
function toggleMusic(id, url) {
    if (wyAudio && wyAudioId === id) {
        if (wyAudio.paused) {
            wyAudio.play().catch(() => toast('播放失败'));
            setMusicPlaying(id, true);
        } else {
            wyAudio.pause();
            setMusicPlaying(id, false);
        }
        return;
    }
    if (wyAudio) {
        wyAudio.pause();
        setMusicPlaying(wyAudioId, false);
        wyAudio = null;
    }
    const audio = new Audio(url);
    audio.onended = () => {
        setMusicPlaying(id, false);
        wyAudio = null; wyAudioId = null;
    };
    audio.onerror = () => {
        toast('播放失败');
        setMusicPlaying(id, false);
        wyAudio = null; wyAudioId = null;
    };
    audio.play().then(() => {
        wyAudio = audio;
        wyAudioId = id;
        setMusicPlaying(id, true);
    }).catch(() => {
        toast('播放失败');
    });
}

function setMusicPlaying(id, playing) {
    const el = document.getElementById('music-' + id);
    if (!el) return;
    if (playing) {
        el.classList.add('playing');
        el.querySelector('.icon-play').style.display = 'none';
        el.querySelector('.icon-pause').style.display = 'block';
    } else {
        el.classList.remove('playing');
        el.querySelector('.icon-play').style.display = 'block';
        el.querySelector('.icon-pause').style.display = 'none';
    }
}

function openComment(postId) {
    if (!checkGuestLogin() && !TYPECHO_LOGIN) { openLogin(); return; }
    activePostId = postId; replyTo = 0;
    const metaRow = document.getElementById('cm-meta');
    const hint = document.getElementById('cm-hint');

    if (TYPECHO_LOGIN) {
        metaRow.style.display = 'none';
        hint.textContent = '<?php echo $isLogin ? '以 ' . $user->screenName . ' 的身份评论' : ''; ?>';
    } else if (checkGuestLogin()) {
        metaRow.style.display = 'none';
        const g = getGuestInfo();
        hint.textContent = `以 ${g.name} 的身份评论`;
    } else {
        metaRow.style.display = 'flex';
        hint.textContent = '';
        const iden = loadIdentity();
        document.getElementById('cm-author').value = iden.author;
        document.getElementById('cm-mail').value = iden.mail;
        document.getElementById('cm-url').value = iden.url;
    }

    document.getElementById('cm-text').value = '';
    document.getElementById('input-mask').classList.add('show');
    document.getElementById('input-box').classList.add('show');
    setTimeout(() => document.getElementById('cm-text').focus(), 100);
}

function closeComment() {
    document.getElementById('input-mask').classList.remove('show');
    document.getElementById('input-box').classList.remove('show');
    activePostId = 0; replyTo = 0;
}

function replyCm(postId, cmId, author) {
    openComment(postId);
    replyTo = cmId;
    setTimeout(() => {
        const ta = document.getElementById('cm-text');
        ta.placeholder = `回复 ${author}：`; ta.focus();
    }, 100);
}

async function submitCm() {
    if (!activePostId) return;
    const text = document.getElementById('cm-text').value.trim();
    if (!text) return toast('请输入内容');

    let author, mail, url;
    if (TYPECHO_LOGIN) {
        author = '<?php echo $isLogin ? $user->screenName : ''; ?>'; mail = '<?php echo $isLogin ? $user->mail : ''; ?>'; url = '<?php echo $isLogin ? $user->url : ''; ?>';
    } else if (checkGuestLogin()) {
        const g = getGuestInfo(); author = g.name; mail = g.mail; url = g.url;
    } else {
        author = document.getElementById('cm-author').value.trim();
        mail = document.getElementById('cm-mail').value.trim();
        url = document.getElementById('cm-url').value.trim();
        if (!author || !mail) return toast('请填写昵称和邮箱');
        saveIdentity(author, mail, url);
    }

    const body = `post_id=${activePostId}&parent_id=${replyTo || 0}&content=${encodeURIComponent(text)}&author=${encodeURIComponent(author)}&mail=${encodeURIComponent(mail)}&url=${encodeURIComponent(url)}`;
    try {
        const res = await fetch(`${API}?do=comment&_=${TOKEN}`, {
            method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body
        });
        const text = await res.text();
        let json;
        try { json = JSON.parse(text); } catch(e) { console.error('评论解析失败:', text); toast('提交失败'); return; }
        if (json.code === 200) { closeComment(); toast('评论成功'); loadPosts(1); }
        else { toast(json.msg || '提交失败'); }
    } catch (e) { toast('提交失败'); }
}

async function deletePost(id) {
    if (!confirm('确定删除这条微语？')) return;
    try {
        const res = await fetch(`${API}?do=delete&id=${id}&_=${TOKEN}`);
        const text = await res.text();
        let json;
        try { json = JSON.parse(text); } catch(e) { console.error('删除解析失败:', text); toast('删除失败'); return; }
        if (json.code === 200) { document.getElementById(`post-${id}`)?.remove(); toast('已删除'); }
        else toast(json.msg || '删除失败');
    } catch (e) { toast('删除失败'); }
}

function loadIdentity() {
    try { return { author: localStorage.getItem('wy_author') || '', mail: localStorage.getItem('wy_mail') || '', url: localStorage.getItem('wy_url') || '' }; } catch (e) { return { author: '', mail: '', url: '' }; }
}
function saveIdentity(author, mail, url) {
    try { localStorage.setItem('wy_author', author); localStorage.setItem('wy_mail', mail); localStorage.setItem('wy_url', url || ''); } catch (e) {}
}

function escapeHtml(t) { if (!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
function previewImage(url) { const o = document.createElement('div'); o.className = 'wy-overlay'; o.innerHTML = `<img src="${url}" alt="">`; o.onclick = () => o.remove(); document.body.appendChild(o); }
</script>
