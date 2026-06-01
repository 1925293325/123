<?php
/**
 * @name 荣誉证书生成
 * @desc 以底图合成文字奖状
 * @icon 🏆
 * @category generate
 */
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>荣誉证书生成</title>
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
.preview-wrap { margin-top:12px; text-align:center; }
#cert-canvas { max-width:100%; border-radius:8px; border:1px solid #e9ecef; display:none; }
.download-btn { display:none; width:auto; padding:8px 20px; margin-top:10px; text-decoration:none; }
#loading { color:#adb5bd; font-size:13px; margin-top:10px; }
#toast { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(33,37,41,0.9); color:white; padding:10px 20px; border-radius:20px; z-index:1000; font-size:13px; }
</style>
</head>
<body>

<div id="toast"></div>
<main class="main-content">
    <div class="card">
        <div class="input-group">
            <label>获奖人姓名</label>
            <input type="text" class="form-control" id="name" placeholder="请输入姓名" value="张三">
        </div>
        <div class="input-group">
            <label>奖项名称</label>
            <input type="text" class="form-control" id="title" placeholder="请输入奖项名称" value="三好学生">
        </div>
        <div class="input-group">
            <label>班级名称</label>
            <input type="text" class="form-control" id="classname" placeholder="请输入班级名称" value="一年级一班">
        </div>
        <button class="btn-primary" onclick="genCert()">生成证书</button>
    </div>

    <div class="card preview-wrap">
        <div id="loading">正在合成...</div>
        <canvas id="cert-canvas"></canvas>
        <br>
        <a id="download" class="btn-primary download-btn" download="荣誉证书.png">下载证书</a>
    </div>
</main>

<script>
function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.style.display='block'; setTimeout(()=>t.style.display='none',1500); }

function genCert(){
    const name=document.getElementById('name').value.trim()||'张三';
    const title=document.getElementById('title').value.trim()||'三好学生';
    const classname=document.getElementById('classname').value.trim()||'一年级一班';
    const canvas=document.getElementById('cert-canvas');
    const ctx=canvas.getContext('2d');
    const loading=document.getElementById('loading');
    const download=document.getElementById('download');

    canvas.style.display='none';
    download.style.display='none';
    loading.style.display='block';

    const img=new Image();
    img.crossOrigin='anonymous';
    img.src='666.jpg?t='+Date.now();

    img.onload=function(){
        canvas.width=img.width;
        canvas.height=img.height;
        const W=canvas.width;
        const H=canvas.height;

        ctx.drawImage(img,0,0);

        // ========== 根据底图精调坐标 ==========

        // 1. 姓名：写在横线上，红色大字
        ctx.fillStyle='#000';
        ctx.font='bold '+(H*0.055)+'px "PingFang SC","Microsoft YaHei","Noto Sans SC",sans-serif';
        ctx.textAlign='center';
        ctx.fillText(name, W*0.22, H*0.390);

        // 2. 奖项：在"评为"后面
        ctx.fillStyle='#c41e3a';
        ctx.font='bold '+(H*0.088)+'px "PingFang SC","Microsoft YaHei","Noto Sans SC",sans-serif';
        ctx.textAlign='center';
        ctx.fillText('「'+title+'」', W*0.50, H*0.62);

        // 3. 班级：在奖项下方
        ctx.fillStyle='#333';
        ctx.font=(H*0.032)+'px "PingFang SC","Microsoft YaHei","Noto Sans SC",sans-serif';
        ctx.textAlign='center';
        ctx.fillText(classname, W*0.63, H*0.76);

        // 4. 日期：右下角
        const now=new Date();
        const dateStr=now.getFullYear()+'年'+(now.getMonth()+1)+'月'+now.getDate()+'日';
        ctx.fillStyle='#555';
        ctx.font=(H*0.025)+'px "PingFang SC","Microsoft YaHei","Noto Sans SC",sans-serif';
        ctx.textAlign='right';
        ctx.fillText(dateStr, W*0.69, H*0.80);

        // ======================================

        loading.style.display='none';
        canvas.style.display='inline-block';
        download.style.display='inline-block';
        download.href=canvas.toDataURL('image/png');
    };

    img.onerror=function(){
        loading.innerHTML='<span style="color:#dc3545">底图 666.jpg 加载失败，请确认已上传到本目录</span>';
        toast('底图加载失败');
    };
}

window.addEventListener('DOMContentLoaded', genCert);
</script>
</body>
</html>