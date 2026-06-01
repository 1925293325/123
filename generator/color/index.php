<?php
/**
 * @name 配色生成器
 * @desc 随机颜色值生成
 * @icon 🎨
 * @category color
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>配色生成器</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
.color-preview { width:100%; height:140px; border-radius:8px; margin-bottom:10px; border:1px solid #e9ecef; background:#0d6efd; }
.color-value { font-family:monospace; font-size:18px; text-align:center; font-weight:600; letter-spacing:1px; }
.btn-primary { width:100%; padding:10px; background:var(--primary); color:white; border:none; border-radius:20px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; margin-top:12px; }
.btn-primary:hover { background:#0b5ed7; transform:translateY(-1px); box-shadow:0 3px 8px rgba(13,110,253,0.25); }
.result-box { margin-top:10px; padding:12px; background:#f8f9fa; border-radius:8px; border:1px dashed #dee2e6; word-break:break-all; font-size:14px; color:#212529; display:flex; align-items:center; justify-content:space-between; gap:8px; }
.copy-btn { padding:4px 10px; background:white; border:1px solid #dee2e6; border-radius:6px; font-size:12px; cursor:pointer; color:#495057; white-space:nowrap; }
.copy-btn:hover { border-color:var(--primary); color:var(--primary); }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>

<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="color-preview" id="color-preview"></div>
        <div class="color-value" id="color-value">#0D6EFD</div>
        <button class="btn-primary" onclick="genColor()">随机颜色</button>
        <div class="result-box">
            <span id="color-rgb">rgb(13, 110, 253)</span>
            <button class="copy-btn" onclick="copyText('color-value')">复制 HEX</button>
        </div>
    </div>
</main>

<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(id){ const text=document.getElementById(id).textContent; if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function genColor(){
    const r=Math.floor(Math.random()*256), g=Math.floor(Math.random()*256), b=Math.floor(Math.random()*256);
    const hex='#'+[r,g,b].map(x=>x.toString(16).padStart(2,'0')).join('').toUpperCase();
    document.getElementById('color-preview').style.background=hex;
    document.getElementById('color-value').textContent=hex;
    document.getElementById('color-rgb').textContent='rgb('+r+', '+g+', '+b+')';
}
genColor();
</script>
</body>
</html>