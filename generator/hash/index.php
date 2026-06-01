<?php
/**
 * @name 哈希生成器
 * @desc MD5/SHA1/SHA256/SHA512
 * @icon 🔒
 * @category security
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>哈希生成器</title>
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
.result-box { margin-top:12px; padding:12px; background:#f8f9fa; border-radius:8px; border:1px dashed #dee2e6; word-break:break-all; font-size:13px; color:#212529; display:flex; align-items:center; justify-content:space-between; gap:8px; }
.copy-btn { padding:4px 10px; background:white; border:1px solid #dee2e6; border-radius:6px; font-size:12px; cursor:pointer; color:#495057; white-space:nowrap; }
.copy-btn:hover { border-color:var(--primary); color:var(--primary); }
.hash-row { display:flex; flex-direction:column; gap:10px; margin-top:12px; }
.hash-item { background:#f8f9fa; border-radius:8px; padding:12px; border:1px solid #e9ecef; }
.hash-label { font-size:12px; color:#6c757d; margin-bottom:4px; font-weight:500; }
.hash-val { font-family:monospace; font-size:13px; color:#212529; word-break:break-all; }
.warn-box { background:#fff3cd; border:1px solid #ffeaa7; border-radius:8px; padding:12px; font-size:13px; color:#856404; margin-bottom:14px; }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>
<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="warn-box">⚠️ 哈希是单向函数，无法反向破解。本工具仅支持"密码→哈希值"和"哈希值识别"。</div>
        <div class="input-group">
            <label>输入文本 / 密码</label>
            <textarea class="form-control" id="hash-input" rows="3" placeholder="输入要转换的文本...">123456</textarea>
        </div>
        <button class="btn-primary" onclick="genHash()">生成哈希值</button>
        <div class="hash-row" id="hash-result"></div>
    </div>
    <div class="card">
        <div class="input-group">
            <label>哈希值识别（粘贴哈希值）</label>
            <textarea class="form-control" id="detect-input" rows="2" placeholder="粘贴一段哈希值，自动识别算法..."></textarea>
        </div>
        <button class="btn-primary" onclick="detectHash()">识别算法</button>
        <div class="result-box" id="detect-result" style="display:none;margin-top:12px;">
            <span id="detect-text"></span>
        </div>
    </div>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js"></script>
<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(text){ if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function genHash(){
    const text=document.getElementById('hash-input').value;
    if(!text){ toast('请输入文本'); return; }
    const md5=CryptoJS.MD5(text).toString();
    const sha1=CryptoJS.SHA1(text).toString();
    const sha256=CryptoJS.SHA256(text).toString();
    const sha512=CryptoJS.SHA512(text).toString();
    const base64=CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(text));
    const html='<div class="hash-item"><div class="hash-label">MD5</div><div class="hash-val">'+md5+' <button class="copy-btn" onclick="copyText(\''+md5+'\')">复制</button></div></div>'
        +'<div class="hash-item"><div class="hash-label">SHA1</div><div class="hash-val">'+sha1+' <button class="copy-btn" onclick="copyText(\''+sha1+'\')">复制</button></div></div>'
        +'<div class="hash-item"><div class="hash-label">SHA256</div><div class="hash-val">'+sha256+' <button class="copy-btn" onclick="copyText(\''+sha256+'\')">复制</button></div></div>'
        +'<div class="hash-item"><div class="hash-label">SHA512</div><div class="hash-val">'+sha512+' <button class="copy-btn" onclick="copyText(\''+sha512+'\')">复制</button></div></div>'
        +'<div class="hash-item"><div class="hash-label">Base64</div><div class="hash-val">'+base64+' <button class="copy-btn" onclick="copyText(\''+base64+'\')">复制</button></div></div>';
    document.getElementById('hash-result').innerHTML=html;
}
function detectHash(){
    const val=document.getElementById('detect-input').value.trim().toLowerCase().replace(/[^a-f0-9]/g,'');
    if(!val){ toast('请输入哈希值'); return; }
    let result='';
    if(val.length===32) result='可能是 MD5（32位十六进制）';
    else if(val.length===40) result='可能是 SHA1（40位十六进制）';
    else if(val.length===64) result='可能是 SHA256（64位十六进制）';
    else if(val.length===128) result='可能是 SHA512（128位十六进制）';
    else result='长度不匹配常见哈希算法，可能是自定义哈希或其他编码';
    document.getElementById('detect-text').textContent=result;
    document.getElementById('detect-result').style.display='flex';
}
window.addEventListener('DOMContentLoaded', genHash);
</script>
</body>
</html>