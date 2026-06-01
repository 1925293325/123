<?php
/**
 * @name JSON格式化
 * @desc 美化、压缩、转义
 * @icon 📋
 * @category dev
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>JSON 格式化</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:12px; }
.input-group { margin-bottom:14px; }
.input-group label { display:block; font-size:13px; font-weight:500; color:#495057; margin-bottom:6px; }
.form-control { width:100%; padding:10px 12px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; outline:none; transition:border-color 0.2s; font-family:monospace; color:#212529; resize:vertical; }
.form-control:focus { border-color:var(--primary); }
.btn-group { display:flex; gap:10px; margin-bottom:12px; }
.btn-group .btn-primary { flex:1; }
.result-box { margin-top:12px; padding:12px; background:#f8f9fa; border-radius:8px; border:1px dashed #dee2e6; word-break:break-all; font-size:13px; color:#212529; display:flex; align-items:center; justify-content:space-between; gap:8px; display:none; }
.copy-btn { padding:4px 10px; background:white; border:1px solid #dee2e6; border-radius:6px; font-size:12px; cursor:pointer; color:#495057; white-space:nowrap; }
.copy-btn:hover { border-color:var(--primary); color:var(--primary); }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>
<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="input-group"><label>JSON 内容</label><textarea class="form-control" id="json-input" rows="6" placeholder="粘贴 JSON 内容...">{"name":"林子","url":"https://linyu.live","count":3}</textarea></div>
        <div class="btn-group">
            <button class="btn-primary" onclick="formatJSON()">美化</button>
            <button class="btn-primary" onclick="compressJSON()" style="background:#6c757d;">压缩</button>
            <button class="btn-primary" onclick="escapeJSON()" style="background:#198754;">转义</button>
        </div>
        <div class="result-box" id="json-result"><span id="json-val" style="white-space:pre-wrap;font-family:monospace;"></span><button class="copy-btn" onclick="copyText(document.getElementById('json-val').textContent)">复制</button></div>
    </div>
</main>
<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(text){ if(!text){ toast('无内容'); return; } if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function formatJSON(){
    const text=document.getElementById('json-input').value.trim();
    if(!text){ toast('请输入JSON'); return; }
    try{ const obj=JSON.parse(text); document.getElementById('json-val').textContent=JSON.stringify(obj,null,2); document.getElementById('json-result').style.display='flex'; }catch(e){ toast('JSON格式错误'); }
}
function compressJSON(){
    const text=document.getElementById('json-input').value.trim();
    if(!text){ toast('请输入JSON'); return; }
    try{ const obj=JSON.parse(text); document.getElementById('json-val').textContent=JSON.stringify(obj); document.getElementById('json-result').style.display='flex'; }catch(e){ toast('JSON格式错误'); }
}
function escapeJSON(){
    const text=document.getElementById('json-input').value.trim();
    if(!text){ toast('请输入JSON'); return; }
    try{ JSON.parse(text); document.getElementById('json-val').textContent=JSON.stringify(text); document.getElementById('json-result').style.display='flex'; }catch(e){ toast('JSON格式错误'); }
}
window.addEventListener('DOMContentLoaded', formatJSON);
</script>
</body>
</html>