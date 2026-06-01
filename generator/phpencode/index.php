<?php
/**
 * @name PHP在线加密
 * @desc 红尘云风格PHP代码加密
 * @icon 🔐
 * @category dev
 */
function phpencode($code) {
    $code = str_replace(array('<?php','?>','<?PHP'), array('','',''), $code);
    $encode = base64_encode(gzdeflate($code));
    return "<?php\neval(gzinflate(base64_decode('" . $encode . "')));\n?>";
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>PHP在线加密</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --p:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,.04); margin-bottom:12px; }
.input-group { margin-bottom:14px; }
.input-group label { display:block; font-size:13px; font-weight:500; color:#495057; margin-bottom:6px; }
.form-control { width:100%; padding:10px 12px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; outline:none; transition:border-color .2s; font-family:monospace; color:#212529; resize:vertical; }
.form-control:focus { border-color:var(--p); }
.btn-primary { width:100%; padding:10px; background:var(--p); color:#fff; border:none; border-radius:20px; font-size:14px; font-weight:500; cursor:pointer; transition:all .2s; }
.btn-primary:hover { background:#0b5ed7; transform:translateY(-1px); box-shadow:0 3px 8px rgba(13,110,253,.25); }
.result-box { margin-top:12px; padding:12px; background:#f8f9fa; border-radius:8px; border:1px dashed #dee2e6; word-break:break-all; font-size:13px; color:#212529; font-family:monospace; }
.copy-btn { padding:4px 10px; background:#fff; border:1px solid #dee2e6; border-radius:6px; font-size:12px; cursor:pointer; color:#495057; white-space:nowrap; }
.copy-btn:hover { border-color:var(--p); color:var(--p); }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,.9); color:#fff; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
.hint { font-size:12px; color:#adb5bd; margin-top:8px; line-height:1.5; }
</style>
</head>
<body>
<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <form method="post">
            <div class="input-group">
                <label>输入要加密的PHP代码</label>
                <textarea class="form-control" name="source" rows="8" placeholder="<?php echo 'hello'; ?>"><?php if(isset($_POST['source'])) echo htmlspecialchars($_POST['source']); ?></textarea>
            </div>
            <button class="btn-primary" type="submit">点击加密</button>
        </form>
        <div class="hint">提示：会自动去除 <?php ?> 标签后加密</div>
    </div>
    <?php if(!empty($_POST['source'])): ?>
    <div class="card">
        <div class="input-group">
            <label>加密后的代码 <button class="copy-btn" onclick="copyText(document.getElementById('out').textContent)">复制</button></label>
            <pre class="result-box" id="out" style="white-space:pre-wrap;max-height:300px;overflow-y:auto"><?php echo htmlspecialchars(phpencode($_POST['source'])); ?></pre>
        </div>
    </div>
    <?php endif; ?>
</main>
<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(text){ if(!text){ toast('无内容'); return; } if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); } else { const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
</script>
</body>
</html>