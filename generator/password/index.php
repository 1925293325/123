<?php
/**
 * @name 密码生成器
 * @desc 随机强密码生成
 * @icon 🔐
 * @category security
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>密码生成器</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
:root { --primary:#0d6efd; }
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}
.main-content{width:100%;min-height:100vh;padding:16px;}
.card { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:16px; box-shadow:0 2px 6px rgba(0,0,0,0.04); }
.input-group { margin-bottom:14px; }
.input-group label { display:block; font-size:13px; font-weight:500; color:#495057; margin-bottom:6px; }
.range-wrap { display:flex; align-items:center; gap:10px; }
input[type=range] { flex:1; accent-color:var(--primary); }
.range-val { min-width:36px; text-align:center; font-weight:600; color:var(--primary); font-size:14px; }
.chk-row { display:flex; flex-wrap:wrap; gap:14px; margin-bottom:14px; }
.chk-row label { display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px; color:#495057; }
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
            <label>密码长度 <span class="range-val" id="pwd-len-val">16</span></label>
            <div class="range-wrap">
                <input type="range" id="pwd-len" min="4" max="64" value="16" oninput="document.getElementById('pwd-len-val').textContent=this.value">
            </div>
        </div>
        <div class="chk-row">
            <label><input type="checkbox" id="pwd-upper" checked> 大写字母</label>
            <label><input type="checkbox" id="pwd-lower" checked> 小写字母</label>
            <label><input type="checkbox" id="pwd-number" checked> 数字</label>
            <label><input type="checkbox" id="pwd-symbol"> 特殊符号</label>
        </div>
        <button class="btn-primary" onclick="genPassword()">生成密码</button>
        <div class="result-box" id="pwd-result">
            <span id="pwd-text"></span>
            <button class="copy-btn" onclick="copyText('pwd-text')">复制</button>
        </div>
    </div>
</main>

<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(id){ const text=document.getElementById(id).textContent; if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function genPassword(){
    const len=parseInt(document.getElementById('pwd-len').value);
    const upper='ABCDEFGHIJKLMNOPQRSTUVWXYZ'; const lower='abcdefghijklmnopqrstuvwxyz'; const number='0123456789'; const symbol='!@#$%^&*()_+-=[]{}|;:,.<>?';
    let chars='';
    if(document.getElementById('pwd-upper').checked) chars+=upper;
    if(document.getElementById('pwd-lower').checked) chars+=lower;
    if(document.getElementById('pwd-number').checked) chars+=number;
    if(document.getElementById('pwd-symbol').checked) chars+=symbol;
    if(!chars){ toast('至少选择一种字符类型'); return; }
    let pwd=''; for(let i=0;i<len;i++) pwd+=chars.charAt(Math.floor(Math.random()*chars.length));
    document.getElementById('pwd-text').textContent=pwd;
    document.getElementById('pwd-result').style.display='flex';
}
</script>
</body>
</html>