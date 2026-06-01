<?php
/**
 * @name 时间戳转换
 * @desc Unix时间戳互转
 * @icon ⏱️
 * @category convert
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>时间戳转换</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); margin-bottom:12px; }
.input-group { margin-bottom:14px; }
.input-group label { display:block; font-size:13px; font-weight:500; color:#495057; margin-bottom:6px; }
.form-control { width:100%; padding:10px 12px; border:1px solid #dee2e6; border-radius:8px; font-size:14px; outline:none; transition:border-color 0.2s; font-family:inherit; color:#212529; }
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
        <div class="input-group">
            <label>Unix 时间戳 (秒)</label>
            <input type="number" class="form-control" id="ts-input" placeholder="例如 1715155200">
        </div>
        <button class="btn-primary" onclick="tsToDate()">转日期时间</button>
        <div class="result-box" id="ts-result">
            <span id="ts-text"></span>
            <button class="copy-btn" onclick="copyText('ts-text')">复制</button>
        </div>
    </div>
    <div class="card">
        <div class="input-group">
            <label>日期时间</label>
            <input type="datetime-local" class="form-control" id="dt-input">
        </div>
        <button class="btn-primary" onclick="dateToTs()">转时间戳</button>
        <div class="result-box" id="dt-result">
            <span id="dt-text"></span>
            <button class="copy-btn" onclick="copyText('dt-text')">复制</button>
        </div>
    </div>
</main>

<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(id){ const text=document.getElementById(id).textContent; if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function tsToDate(){
    const ts=document.getElementById('ts-input').value;
    if(!ts){ toast('请输入时间戳'); return; }
    const date=new Date(parseInt(ts)*1000);
    const str=date.toLocaleString('zh-CN',{hour12:false});
    document.getElementById('ts-text').textContent=str;
    document.getElementById('ts-result').style.display='flex';
}
function dateToTs(){
    const val=document.getElementById('dt-input').value;
    if(!val){ toast('请选择日期时间'); return; }
    const ts=Math.floor(new Date(val).getTime()/1000);
    document.getElementById('dt-text').textContent=ts;
    document.getElementById('dt-result').style.display='flex';
}
</script>
</body>
</html>