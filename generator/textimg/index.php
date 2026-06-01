<?php
/**
 * @name 文字转图片
 * @desc 纯文字转PNG图片
 * @icon 📝
 * @category generate
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>文字转图片</title>
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
input[type="color"].form-control { height:40px; padding:2px; }
.btn-primary { width:100%; padding:10px; background:var(--primary); color:white; border:none; border-radius:20px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.btn-primary:hover { background:#0b5ed7; transform:translateY(-1px); box-shadow:0 3px 8px rgba(13,110,253,0.25); }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>

<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="input-group">
            <label>输入文字</label>
            <textarea class="form-control" id="ti-text" rows="3" placeholder="输入要转成图片的文字...">林子API</textarea>
        </div>
        <div class="input-group">
            <label>背景色</label>
            <input type="color" class="form-control" id="ti-bg" value="#0d6efd">
        </div>
        <div class="input-group">
            <label>文字色</label>
            <input type="color" class="form-control" id="ti-color" value="#ffffff">
        </div>
        <button class="btn-primary" onclick="genTextImg()">生成图片</button>
        <div style="margin-top:12px;text-align:center;">
            <canvas id="ti-canvas" width="600" height="300" style="display:none;max-width:100%;border-radius:8px;border:1px solid #e9ecef;"></canvas>
            <br>
            <a id="ti-download" download="text.png" class="btn-primary" style="display:none;width:auto;padding:8px 20px;margin-top:10px;text-decoration:none;">下载图片</a>
        </div>
    </div>
</main>

<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function genTextImg(){
    const text=document.getElementById('ti-text').value||' ';
    const bg=document.getElementById('ti-bg').value;
    const color=document.getElementById('ti-color').value;
    const canvas=document.getElementById('ti-canvas');
    const ctx=canvas.getContext('2d');
    ctx.fillStyle=bg; ctx.fillRect(0,0,600,300);
    ctx.fillStyle=color;
    ctx.font='bold 48px -apple-system, BlinkMacSystemFont, sans-serif';
    ctx.textAlign='center'; ctx.textBaseline='middle';
    ctx.fillText(text,300,150);
    canvas.style.display='inline-block';
    const link=document.getElementById('ti-download');
    link.href=canvas.toDataURL('image/png');
    link.style.display='inline-block';
    link.textContent='下载图片';
}
</script>
</body>
</html>