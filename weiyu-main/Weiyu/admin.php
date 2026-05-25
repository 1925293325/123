<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

require_once __TYPECHO_ROOT_DIR__ . '/admin/common.php';
require_once __TYPECHO_ROOT_DIR__ . '/admin/header.php';
require_once __TYPECHO_ROOT_DIR__ . '/admin/menu.php';

$db = \Typecho\Db::get();
$prefix = $db->getPrefix();
$options = \Widget\Options::alloc();
$user = \Widget\User::alloc();
$token = \TypechoPlugin\Weiyu\Plugin::getToken();

// 按类型筛选
$type = $_GET['type'] ?? 'all';
$allowedTypes = ['all', 'text', 'image', 'music', 'video'];
if (!in_array($type, $allowedTypes)) $type = 'all';

$where = 'status = ?';
$params = [1];
if ($type !== 'all') {
    if ($type === 'text') {
        $where .= ' AND (images IS NULL OR images = "" OR images = "[]") AND music_link IS NULL AND video_link IS NULL';
    } elseif ($type === 'image') {
        $where .= ' AND images IS NOT NULL AND images != "" AND images != "[]" AND (music_link IS NULL OR music_link = "") AND (video_link IS NULL OR video_link = "")';
    } elseif ($type === 'music') {
        $where .= ' AND music_link IS NOT NULL AND music_link != ""';
    } elseif ($type === 'video') {
        $where .= ' AND video_link IS NOT NULL AND video_link != ""';
    }
}

$page = max(1, intval($_GET['page'] ?? 1));
$pageSize = 20;

$totalApproved = $db->fetchObject($db->select(['COUNT(id)' => 'num'])->from($prefix . 'weiyu_posts')->where($where, 1))->num;
$totalPending = $db->fetchObject($db->select(['COUNT(id)' => 'num'])->from($prefix . 'weiyu_posts')->where('status = ?', 0))->num;
$postsApproved = $db->fetchAll($db->select()->from($prefix . 'weiyu_posts')->where($where, $params)->order('created', \Typecho\Db::SORT_DESC)->page($page, $pageSize));
$postsPending = $db->fetchAll($db->select()->from($prefix . 'weiyu_posts')->where('status = ?', 0)->order('created', \Typecho\Db::SORT_DESC));
$totalPage = ceil($totalApproved / $pageSize);
?>

<div class="main">
    <div class="body container">
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12">
                <div class="typecho-page-title"><h2>微语管理</h2></div>

                <div style="margin-bottom:20px;">
                    <a href="javascript:void(0)" onclick="showTab('approved')" id="tab-approved" style="padding:6px 16px;background:#467b96;color:#fff;border-radius:4px;text-decoration:none;display:inline-block;font-size:14px;">已发布 (<?php echo $totalApproved; ?>)</a>
                    <a href="javascript:void(0)" onclick="showTab('pending')" id="tab-pending" style="padding:6px 16px;background:#f0f0f0;color:#333;border-radius:4px;text-decoration:none;display:inline-block;margin-left:8px;font-size:14px;">审核区 (<?php echo $totalPending; ?>)</a>
                </div>
                
                <div style="margin-bottom:15px;">
                    <span style="font-size:13px;color:#666;margin-right:8px;">类型筛选：</span>
                    <a href="?panel=Weiyu/admin.php&type=all&page=<?php echo $page; ?>" style="padding:5px 12px;background:<?php echo $type === 'all' ? '#467b96' : '#f0f0f0'; ?>;color:<?php echo $type === 'all' ? '#fff' : '#333'; ?>;border-radius:4px;text-decoration:none;display:inline-block;font-size:13px;margin-right:6px;">全部</a>
                    <a href="?panel=Weiyu/admin.php&type=text&page=<?php echo $page; ?>" style="padding:5px 12px;background:<?php echo $type === 'text' ? '#467b96' : '#f0f0f0'; ?>;color:<?php echo $type === 'text' ? '#fff' : '#333'; ?>;border-radius:4px;text-decoration:none;display:inline-block;font-size:13px;margin-right:6px;">文字</a>
                    <a href="?panel=Weiyu/admin.php&type=image&page=<?php echo $page; ?>" style="padding:5px 12px;background:<?php echo $type === 'image' ? '#467b96' : '#f0f0f0'; ?>;color:<?php echo $type === 'image' ? '#fff' : '#333'; ?>;border-radius:4px;text-decoration:none;display:inline-block;font-size:13px;margin-right:6px;">图片</a>
                    <a href="?panel=Weiyu/admin.php&type=music&page=<?php echo $page; ?>" style="padding:5px 12px;background:<?php echo $type === 'music' ? '#467b96' : '#f0f0f0'; ?>;color:<?php echo $type === 'music' ? '#fff' : '#333'; ?>;border-radius:4px;text-decoration:none;display:inline-block;font-size:13px;margin-right:6px;">音乐</a>
                    <a href="?panel=Weiyu/admin.php&type=video&page=<?php echo $page; ?>" style="padding:5px 12px;background:<?php echo $type === 'video' ? '#467b96' : '#f0f0f0'; ?>;color:<?php echo $type === 'video' ? '#fff' : '#333'; ?>;border-radius:4px;text-decoration:none;display:inline-block;font-size:13px;margin-right:6px;">视频</a>
                </div>

                <!-- 编辑弹窗 -->
                <div id="edit-modal" style="display:none;background:#fff;padding:20px;margin-bottom:20px;border-radius:4px;border:1px solid #e0e0e0;">
                    <h4 style="margin-bottom:15px;">编辑微语 <span id="edit-id-display" style="color:#999;"></span></h4>
                    <input type="hidden" id="edit-id-val">
                    <input type="text" id="edit-title" style="width:100%;padding:10px;border:1px solid #d9d9d9;border-radius:4px;font-size:14px;margin-bottom:8px;" placeholder="标题（可选）">
                    <textarea id="edit-content" style="width:100%;min-height:80px;border:1px solid #d9d9d9;padding:10px;border-radius:4px;resize:vertical;font-family:inherit;font-size:14px;" placeholder="内容..."></textarea>
                    <textarea id="edit-images" style="width:100%;min-height:60px;border:1px solid #d9d9d9;padding:10px;border-radius:4px;resize:vertical;font-family:inherit;font-size:14px;margin-top:8px;" placeholder="图片链接，每行一个..."></textarea>
                    <input type="text" id="edit-music-cover" style="width:100%;padding:8px 10px;border:1px solid #d9d9d9;border-radius:4px;margin-top:8px;font-size:14px;" placeholder="音乐封面">
                    <input type="text" id="edit-music-title" style="width:100%;padding:8px 10px;border:1px solid #d9d9d9;border-radius:4px;margin-top:8px;font-size:14px;" placeholder="音乐名称">
                    <input type="text" id="edit-music-artist" style="width:100%;padding:8px 10px;border:1px solid #d9d9d9;border-radius:4px;margin-top:8px;font-size:14px;" placeholder="音乐歌手">
                    <input type="text" id="edit-music-link" style="width:100%;padding:8px 10px;border:1px solid #d9d9d9;border-radius:4px;margin-top:8px;font-size:14px;" placeholder="音乐链接">
                    <input type="text" id="edit-video-link" style="width:100%;padding:8px 10px;border:1px solid #d9d9d9;border-radius:4px;margin-top:8px;font-size:14px;" placeholder="视频链接">
                    <input type="text" id="edit-video-cover" style="width:100%;padding:8px 10px;border:1px solid #d9d9d9;border-radius:4px;margin-top:8px;font-size:14px;" placeholder="视频封面">
                    <div style="margin-top:10px;">
                        <button onclick="saveEdit()" style="padding:7px 20px;background:#467b96;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;">保存</button>
                        <button onclick="closeEdit()" style="padding:7px 20px;background:#999;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:14px;margin-left:10px;">取消</button>
                    </div>
                </div>

                <!-- 已发布 -->
                <div id="list-approved">
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup><col width="50"><col width="12%"><col width="22%"><col width="8%"><col width="8%"><col width="8%"><col width="8%"><col width="18%"></colgroup>
                            <thead><tr><th>ID</th><th>作者</th><th>内容</th><th>图</th><th>音乐</th><th>视频</th><th>点赞</th><th>时间/操作</th></tr></thead>
                            <tbody>
                                <?php foreach ($postsApproved as $post): 
                                    $authorName = $post['authorId'] ? ($db->fetchRow($db->select('screenName')->from('table.users')->where('uid = ?', $post['authorId']))['screenName'] ?? '博主') : ($post['authorName'] ?: '游客');
                                    $imgs = !empty($post['images']) ? json_decode($post['images'], true) : [];
                                ?>
                                <tr id="row-<?php echo $post['id']; ?>">
                                    <td><?php echo $post['id']; ?></td>
                                    <td><?php echo htmlspecialchars($authorName); ?></td>
                                    <td><div style="max-height:60px;overflow:hidden;line-height:1.5;"><?php echo htmlspecialchars($post['content']); ?></div></td>
                                    <td><?php echo count($imgs); ?></td>
                                    <td><?php echo !empty($post['music_link']) ? '有' : '-'; ?></td>
                                    <td><?php echo !empty($post['video_link']) ? '有' : '-'; ?></td>
                                    <td><?php echo $post['likes']; ?></td>
                                    <td>
                                        <?php echo date('Y-m-d H:i', $post['created']); ?><br>
                                        <a href="#" onclick="openEdit(<?php echo $post['id']; ?>);return false;" style="color:#467b96;font-size:12px;">编辑</a>
                                        <a href="#" onclick="del(<?php echo $post['id']; ?>);return false;" style="color:#c00;font-size:12px;margin-left:8px;">删除</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($postsApproved)): ?>
                                <tr><td colspan="8" style="text-align:center;padding:30px;color:#999;">暂无已发布微语</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="typecho-pager">
                        <?php if ($totalPage > 1): for ($i = 1; $i <= $totalPage; $i++): ?>
                        <a href="?panel=Weiyu/admin.php&page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'current' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; endif; ?>
                    </div>
                </div>

                <!-- 审核区 -->
                <div id="list-pending" style="display:none;">
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup><col width="50"><col width="12%"><col width="30%"><col width="10%"><col width="8%"><col width="8%"><col width="15%"></colgroup>
                            <thead><tr><th>ID</th><th>作者</th><th>内容</th><th>图片</th><th>点赞</th><th>评论</th><th>时间/操作</th></tr></thead>
                            <tbody>
                                <?php foreach ($postsPending as $post): 
                                    $authorName = $post['authorId'] ? ($db->fetchRow($db->select('screenName')->from('table.users')->where('uid = ?', $post['authorId']))['screenName'] ?? '博主') : ($post['authorName'] ?: '游客');
                                    $imgs = !empty($post['images']) ? json_decode($post['images'], true) : [];
                                ?>
                                <tr id="row-p-<?php echo $post['id']; ?>">
                                    <td><?php echo $post['id']; ?></td>
                                    <td><?php echo htmlspecialchars($authorName); ?></td>
                                    <td><div style="max-height:60px;overflow:hidden;line-height:1.5;"><?php echo htmlspecialchars($post['content']); ?></div></td>
                                    <td><?php echo count($imgs); ?> 张</td>
                                    <td><?php echo $post['likes']; ?></td>
                                    <td><?php echo $post['commentsNum']; ?></td>
                                    <td>
                                        <?php echo date('Y-m-d H:i', $post['created']); ?><br>
                                        <a href="#" onclick="approvePost(<?php echo $post['id']; ?>);return false;" style="color:#07c160;font-size:12px;">通过</a>
                                        <a href="#" onclick="del(<?php echo $post['id']; ?>);return false;" style="color:#c00;font-size:12px;margin-left:8px;">删除</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($postsPending)): ?>
                                <tr><td colspan="7" style="text-align:center;padding:30px;color:#999;">暂无待审核微语</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 评论管理 -->
                <div style="margin-top:30px;">
                    <h4 style="margin-bottom:15px;">评论管理</h4>
                    <?php $allComments = $db->fetchAll($db->select()->from($prefix . 'weiyu_comments')->order('created', \Typecho\Db::SORT_DESC)->limit(50)); ?>
                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table">
                            <colgroup><col width="50"><col width="15%"><col width="35%"><col width="20%"><col width="20%"></colgroup>
                            <thead><tr><th>ID</th><th>昵称</th><th>内容</th><th>邮箱</th><th>操作</th></tr></thead>
                            <tbody>
                                <?php foreach ($allComments as $c): ?>
                                <tr>
                                    <td><?php echo $c['id']; ?></td>
                                    <td><?php echo htmlspecialchars($c['author']); ?></td>
                                    <td><?php echo htmlspecialchars($c['content']); ?></td>
                                    <td><?php echo htmlspecialchars($c['mail']); ?></td>
                                    <td>
                                        <a href="#" onclick="editCm(<?php echo $c['id']; ?>,'<?php echo htmlspecialchars(addslashes($c['content'])); ?>');return false;" style="color:#467b96;font-size:12px;">编辑</a>
                                        <a href="#" onclick="delCm(<?php echo $c['id']; ?>);return false;" style="color:#c00;font-size:12px;margin-left:8px;">删除</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($allComments)): ?>
                                <tr><td colspan="5" style="text-align:center;padding:20px;color:#999;">暂无评论</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const API = '<?php echo rtrim($options->index, '/'); ?>/weiyu/action';
const TOKEN = '<?php echo $token; ?>';

function showTab(tab) {
    document.getElementById('list-approved').style.display = tab === 'approved' ? 'block' : 'none';
    document.getElementById('list-pending').style.display = tab === 'pending' ? 'block' : 'none';
    document.getElementById('tab-approved').style.background = tab === 'approved' ? '#467b96' : '#f0f0f0';
    document.getElementById('tab-approved').style.color = tab === 'approved' ? '#fff' : '#333';
    document.getElementById('tab-pending').style.background = tab === 'pending' ? '#467b96' : '#f0f0f0';
    document.getElementById('tab-pending').style.color = tab === 'pending' ? '#fff' : '#333';
}

async function openEdit(id){
    const res = await fetch(`${API}?do=getPost&id=${id}&_=${TOKEN}`);
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { alert('获取数据失败'); return; }
    if (json.code !== 200) return alert(json.msg || '获取数据失败');
    const post = json.data;
    document.getElementById('edit-id-display').textContent = '#' + id;
    document.getElementById('edit-id-val').value = id;
    document.getElementById('edit-title').value = post.title || '';
    document.getElementById('edit-content').value = post.content || '';
    document.getElementById('edit-images').value = (post.images || []).join('\n');
    document.getElementById('edit-music-cover').value = post.music_cover || '';
    document.getElementById('edit-music-title').value = post.music_title || '';
    document.getElementById('edit-music-artist').value = post.music_artist || '';
    document.getElementById('edit-music-link').value = post.music_link || '';
    document.getElementById('edit-video-link').value = post.video_link || '';
    document.getElementById('edit-video-cover').value = post.video_cover || '';
    document.getElementById('edit-modal').style.display = 'block';
    window.scrollTo({top:0, behavior:'smooth'});
}

function closeEdit(){
    document.getElementById('edit-modal').style.display = 'none';
}

async function saveEdit(){
    const id = document.getElementById('edit-id-val').value;
    if(!id) return alert('ID错误');
    const title = document.getElementById('edit-title').value.trim();
    const content = document.getElementById('edit-content').value.trim();
    const images = document.getElementById('edit-images').value.trim();
    const musicCover = document.getElementById('edit-music-cover').value.trim();
    const musicTitle = document.getElementById('edit-music-title').value.trim();
    const musicArtist = document.getElementById('edit-music-artist').value.trim();
    const musicLink = document.getElementById('edit-music-link').value.trim();
    const videoLink = document.getElementById('edit-video-link').value.trim();
    const videoCover = document.getElementById('edit-video-cover').value.trim();
    const body = `id=${id}&title=${encodeURIComponent(title)}&content=${encodeURIComponent(content)}&images=${encodeURIComponent(images)}&music_cover=${encodeURIComponent(musicCover)}&music_title=${encodeURIComponent(musicTitle)}&music_artist=${encodeURIComponent(musicArtist)}&music_link=${encodeURIComponent(musicLink)}&video_link=${encodeURIComponent(videoLink)}&video_cover=${encodeURIComponent(videoCover)}`;
    const res = await fetch(`${API}?do=edit&_=${TOKEN}`, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body});
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { alert('保存失败'); return; }
    if(json.code === 200){ location.reload(); } else { alert(json.msg); }
}

async function del(id){
    if(!confirm('确定删除？相关评论也将被删除。')) return;
    const res = await fetch(`${API}?do=delete&id=${id}&_=${TOKEN}`);
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { alert('删除失败'); return; }
    if(json.code === 200){ 
        document.getElementById(`row-${id}`)?.remove(); 
        document.getElementById(`row-p-${id}`)?.remove(); 
    } else { alert(json.msg); }
}

async function approvePost(id) {
    if(!confirm('确定通过审核？')) return;
    const res = await fetch(`${API}?do=approve&id=${id}&_=${TOKEN}`);
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { alert('审核失败'); return; }
    if(json.code === 200) location.reload(); else alert(json.msg);
}

async function editCm(id, content){
    const nc = prompt('编辑评论内容：', content);
    if(nc === null || nc === content) return;
    const body = `id=${id}&content=${encodeURIComponent(nc)}`;
    const res = await fetch(`${API}?do=editComment&_=${TOKEN}`, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body});
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { alert('编辑失败'); return; }
    if(json.code === 200) location.reload(); else alert(json.msg);
}

async function delCm(id){
    if(!confirm('确定删除这条评论？')) return;
    const res = await fetch(`${API}?do=deleteComment&id=${id}&_=${TOKEN}`);
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch(e) { alert('删除失败'); return; }
    if(json.code === 200) location.reload(); else alert(json.msg);
}
</script>

<?php require_once __TYPECHO_ROOT_DIR__ . '/admin/footer.php'; ?>
