<?php
/**
 * @name 全屏时钟
 * @desc 全屏显示当前时间
 * @icon 🕐
 * @category tool
 */
?>
<!DOCTYPE html><html lang="zh-CN"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no"><title>时间屏幕</title><style>
*{margin:0;padding:0;box-sizing:border-box}:root{--p:#0d6efd}body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;background:#fff;width:100%;min-height:100vh;margin:0;padding:0;color:#212529}

.main-content{width:100%;min-height:100vh;padding:16px;}
.card{background:#fff;border:1px solid #e9ecef;border-radius:12px;padding:16px;box-shadow:0 2px 6px rgba(0,0,0,.04);margin-bottom:12px}
.input-group{margin-bottom:14px}.input-group label{display:block;font-size:13px;font-weight:500;color:#495057;margin-bottom:6px}
.form-control{width:100%;padding:10px 12px;border:1px solid #dee2e6;border-radius:8px;font-size:14px;outline:none;transition:border-color .2s;font-family:inherit;color:#212529;resize:vertical}
.form-control:focus{border-color:var(--p)}.btn-primary{width:100%;padding:10px;background:var(--p);color:#fff;border:none;border-radius:20px;font-size:14px;font-weight:500;cursor:pointer;transition:all .2s}
.btn-primary:hover{background:#0b5ed7;transform:translateY(-1px);box-shadow:0 3px 8px rgba(13,110,253,.25)}
.result-box{margin-top:12px;padding:12px;background:#f8f9fa;border-radius:8px;border:1px dashed #dee2e6;word-break:break-all;font-size:14px;color:#212529;display:flex;align-items:center;justify-content:space-between;gap:8px}
.copy-btn{padding:4px 10px;background:#fff;border:1px solid #dee2e6;border-radius:6px;font-size:12px;cursor:pointer;color:#495057;white-space:nowrap}
.copy-btn:hover{border-color:var(--p);color:var(--p)}
#toast{display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(33,37,41,.9);color:#fff;padding:10px 20px;border-radius:20px;z-index:1000;font-size:13px}
</style></head><body>
<div id="toast"></div>
