<?php
/**
 * @name 二维码生成
 * @desc 文本转二维码
 * @icon 📱
 * @category generate
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>二维码生成</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
.input-group { margin-bottom:14px; }
.input-group label { display:block; font-size:13px; font-weight:500; color:#495057; margin-bottom:6px; }
.form-control { width:100%; padding:10px 12px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; outline:none; transition:border-color 0.2s; font-family:inherit; color:#212529; resize:vertical; }
.form-control:focus { border-color:var(--primary); }
.btn-primary { width:100%; padding:10px; background:var(--primary); color:white; border:none; border-radius:20px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.btn-primary:hover { background:#0b5ed7; transform:translateY(-1px); box-shadow:0 3px 8px rgba(13,110,253,0.25); }
.qr-wrap { text-align:center; margin-top:12px; }
.qr-wrap img { max-width:200px; border-radius:8px; border:1px solid #e9ecef; display:block; margin:0 auto 10px; }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>

<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="input-group">
            <label>输入内容</label>
            <textarea class="form-control" id="qr-text" rows="3" placeholder="输入网址或文本...">https://linyu.live</textarea>
        </div>
        <button class="btn-primary" onclick="genQR()">生成二维码</button>
        <div class="qr-wrap" id="qr-wrap"></div>
    </div>
</main>

<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function genQR(){
    const text=document.getElementById('qr-text').value.trim();
    if(!text){ toast('请输入内容'); return; }
    const url='https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='+encodeURIComponent(text);
    document.getElementById('qr-wrap').innerHTML='<img src="'+url+'" alt="二维码" onerror="this.style.display=\'none\';this.nextSibling.style.display=\'block\';"><div style="display:none;color:#adb5bd;font-size:12px;margin-top:6px;">生成失败，请检查网络</div><br><a href="'+url+'" target="_blank" download="qrcode.png" class="btn-primary" style="display:inline-block;width:auto;padding:6px 16px;font-size:12px;">下载二维码</a>';
}
</script>
</body>
</html>