<?php
/**
 * @name IP查询
 * @desc 显示IP、UA、归属地
 * @icon 🌐
 * @category query
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>IP 查询</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:12px; }
.info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:10px 0; border-bottom:1px solid #f1f3f5; gap:10px; }
.info-row:last-child { border-bottom:none; }
.info-label { font-size:13px; color:#6c757d; font-weight:500; white-space:nowrap; min-width:60px; }
.info-val { font-size:13px; color:#212529; word-break:break-all; text-align:right; flex:1; font-family:monospace; }
.btn-primary { width:100%; padding:10px; background:var(--primary); color:white; border:none; border-radius:20px; font-size:14px; font-weight:500; cursor:pointer; transition:all 0.2s; }
.btn-primary:hover { background:#0b5ed7; transform:translateY(-1px); box-shadow:0 3px 8px rgba(13,110,253,0.25); }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>
<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="info-row"><span class="info-label">IP地址</span><span class="info-val" id="ip-val">加载中...</span></div>
        <div class="info-row"><span class="info-label">归属地</span><span class="info-val" id="loc-val">加载中...</span></div>
        <div class="info-row"><span class="info-label">UserAgent</span><span class="info-val" id="ua-val" style="font-size:11px;">加载中...</span></div>
        <div class="info-row"><span class="info-label">语言</span><span class="info-val" id="lang-val">加载中...</span></div>
        <div class="info-row"><span class="info-label">平台</span><span class="info-val" id="plat-val">加载中...</span></div>
    </div>
    <button class="btn-primary" onclick="getIP()">刷新信息</button>
</main>
<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function getIP(){
    document.getElementById('ip-val').textContent='查询中...';
    document.getElementById('loc-val').textContent='查询中...';
    fetch('https://ipapi.co/json/').then(r=>r.json()).then(d=>{
        document.getElementById('ip-val').textContent=d.ip||'未知';
        document.getElementById('loc-val').textContent=(d.city||'')+' '+(d.region||'')+' '+(d.country_name||'');
    }).catch(()=>{
        document.getElementById('ip-val').textContent='查询失败';
        document.getElementById('loc-val').textContent='查询失败';
    });
    document.getElementById('ua-val').textContent=navigator.userAgent;
    document.getElementById('lang-val').textContent=navigator.language;
    document.getElementById('plat-val').textContent=navigator.platform;
}
window.addEventListener('DOMContentLoaded', getIP);
</script>
</body>
</html>