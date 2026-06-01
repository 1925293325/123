<?php
/**
 * @name 短链接生成
 * @desc 长网址压缩
 * @icon ✂️
 * @category tool
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>短链接生成</title>
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
        <div class="input-group"><label>长链接</label><input type="text" class="form-control" id="long-url" placeholder="粘贴长网址..." value="https://linyu.live/article/this-is-a-very-long-url-path"></div>
        <button class="btn-primary" onclick="genShort()">生成短链接</button>
        <div class="result-box" id="short-result"><span id="short-val"></span><button class="copy-btn" onclick="copyText(document.getElementById('short-val').textContent)">复制</button></div>
    </div>
    <div class="card" style="font-size:12px;color:#adb5bd;line-height:1.6;">
        说明：本工具通过第三方接口生成短链接，生成的链接可能有时效性。
    </div>
</main>
<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }
function copyText(text){ if(!text){ toast('无内容'); return; } if(navigator.clipboard){ navigator.clipboard.writeText(text).then(()=>toast('已复制')); }else{ const ta=document.createElement('textarea'); ta.value=text; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); toast('已复制'); } }
function genShort(){
    const url=document.getElementById('long-url').value.trim();
    if(!url){ toast('请输入网址'); return; }
    toast('正在生成...');
    fetch('https://api.shrtco.de/v2/shorten?url='+encodeURIComponent(url)).then(r=>r.json()).then(d=>{
        if(d.ok){ document.getElementById('short-val').textContent=d.result.full_short_link; document.getElementById('short-result').style.display='flex'; toast('生成成功'); }
        else{ toast('生成失败：'+d.error); }
    }).catch(()=>toast('网络错误，请重试'));
}
</script>
</body>
</html>