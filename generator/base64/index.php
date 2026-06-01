<?php
/**
 * @name Base64编解码
 * @desc 文本与Base64互转
 * @icon 🔁
 * @category encode
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>Base64 编解码</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:12px; }
.input-group { margin-bottom:14px; }
.input-group label { display:block; font-size:13px; font-weight:500; color:#495057; margin-bottom:6px; }
.form-control { width:100%; padding:10px 12px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; outline:none; transition:border-color 0.2s; font-family:inherit; color:#212529; resize:vertical; }
.form-control:focus { border-color:var(--primary); }
.btn-primary { width:100%; padding:10px; background:var(--primary); color:white; border:none; border-radius:20px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.btn-primary:hover { background:#0b5ed7; transform:translateY(-1px); box-shadow:0 3px 8px rgba(13,110,253,0.25); }
.result-box { margin-top:12px; padding:12px; background:#f8f9fa; border-radius:8px; border:1px dashed #dee2e6; word-break:break-all; font-size:14px; color:#212529; display:flex; align-items:center; justify-content:space-between; gap:8px; display:none; }
.copy-btn { padding:4px 10px; background:white; border:1px solid #dee2e6; border-radius:6px; font-size:12px; cursor:pointer; color:#495057; white-space:nowrap; }
.copy-btn:hover { border-color:var(--primary); color:var(--primary); }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>
<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="input-group"><label>原文本</label><textarea class="form-control" id="b64-text" rows="3" placeholder="输入要编码的文本...">Hello 林子API</textarea></div>
        <button class="btn-primary" onclick="toBase64()">编码 → Base64</button>
        <div class="result-box" id="b64-encode-result"><span id="b64-encode-val"></span><button class="copy-btn" onclick="copyText(document.getElementById('b64-encode-val').textContent)">复制</button></div>
    </div>
    <div class="card">
        <div class="input-group"><label>Base64 字符串</label><textarea class="form-control" id="b64-code" rows="3" placeholder="输入要解码的Base64..."></textarea></div>
        <button class="btn-primary" onclick="fromBase64()">解码 → 原文</button>
        <div class="result-box" id="b64-decode-result"><span id="b64-decode-val"></span><button class="copy-btn" onclick="copyText(document.getElementById('b64-decode-val').textContent)">复制</button></div>
    </div>
</main>
<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(text){ if(!text){ toast('无内容'); return; } if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function toBase64(){
    const text=document.getElementById('b64-text').value;
    if(!text){ toast('请输入文本'); return; }
    try{ const b64=btoa(unescape(encodeURIComponent(text))); document.getElementById('b64-encode-val').textContent=b64; document.getElementById('b64-encode-result').style.display='flex'; }catch(e){ toast('编码失败'); }
}
function fromBase64(){
    const code=document.getElementById('b64-code').value.trim();
    if(!code){ toast('请输入Base64'); return; }
    try{ const text=decodeURIComponent(escape(atob(code))); document.getElementById('b64-decode-val').textContent=text; document.getElementById('b64-decode-result').style.display='flex'; }catch(e){ toast('解码失败，可能不是有效的Base64'); }
}
window.addEventListener('DOMContentLoaded', toBase64);
</script>
</body>
</html>