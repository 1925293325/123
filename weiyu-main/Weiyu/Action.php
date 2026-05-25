<?php
namespace TypechoPlugin\Weiyu;

use Typecho\Widget;
use Typecho\Db;
use Widget\Options;
use Widget\User;
use Widget\Security;

class Action extends Widget implements \Widget\ActionInterface
{
    private function jsonResponse($data) {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data);
        exit;
    }

    public function action()
    {
        $request = $this->request;
        $do = $request->get('do');
        $db = Db::get();
        $prefix = $db->getPrefix();
        $user = User::alloc();

        $nonce = $request->get('_');
        $verified = false;
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        if ($nonce === ($_SESSION['weiyu_token'] ?? '')) $verified = true;
        if (!$verified) $verified = Security::alloc()->verify($nonce);

        if (!in_array($do, ['getPosts', 'getComments', 'getPost']) && !$verified) {
            $this->jsonResponse(['code' => 403, 'msg' => '安全验证失败，请刷新页面']);
        }

        switch ($do) {
            case 'publish': $this->publish($request, $db, $prefix, $user); break;
            case 'edit': $this->edit($request, $db, $prefix, $user); break;
            case 'delete': $this->delete($request, $db, $prefix, $user); break;
            case 'like': $this->like($request, $db, $prefix, $user); break;
            case 'comment': $this->comment($request, $db, $prefix, $user); break;
            case 'editComment': $this->editComment($request, $db, $prefix, $user); break;
            case 'deleteComment': $this->deleteComment($request, $db, $prefix, $user); break;
            case 'getPosts': $this->getPosts($request, $db, $prefix); break;
            case 'getComments': $this->getComments($request, $db, $prefix); break;
            case 'getPost': $this->getPost($request, $db, $prefix); break;
            case 'approve': $this->approve($request, $db, $prefix, $user); break;
            case 'checkLogin': $this->checkLogin($request, $user); break;
            default: $this->jsonResponse(['code' => 404, 'msg' => '未知操作']);
        }
    }

    private function publish($request, $db, $prefix, $user)
    {
        $isLogin = $user->hasLogin();
        $guestName = trim($request->get('guest_name'));
        $guestMail = trim($request->get('guest_mail'));
        $guestUrl = trim($request->get('guest_url'));

        if (!$isLogin && (empty($guestName) || empty($guestMail))) {
            $this->jsonResponse(['code' => 401, 'msg' => '请先登录']);
        }

        $title = trim($request->get('title'));
        $content = trim($request->get('content'));
        $imagesRaw = trim($request->get('images'));
        $imgArr = [];
        if (!empty($imagesRaw)) {
            $lines = preg_split('/\r\n|\r|\n/', $imagesRaw);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && filter_var($line, FILTER_VALIDATE_URL)) {
                    $imgArr[] = $line;
                }
            }
        }
        if (count($imgArr) > 6) {
            $this->jsonResponse(['code' => 400, 'msg' => '最多只能上传 6 张图片']);
        }

        $musicCover  = trim($request->get('music_cover'));
        $musicTitle  = trim($request->get('music_title'));
        $musicArtist = trim($request->get('music_artist'));
        $musicLink   = trim($request->get('music_link'));
        $videoLink   = trim($request->get('video_link'));
        $videoCover  = trim($request->get('video_cover'));

        if (empty($content) && empty($imgArr) && empty($musicLink) && empty($videoLink) && empty($title)) {
            $this->jsonResponse(['code' => 400, 'msg' => '内容不能为空']);
        }

        // 只有管理员免审核，其他所有人都要审核
        $status = ($isLogin && $user->pass('administrator', true)) ? 1 : 0;
        $time = time();

        $db->query($db->insert($prefix . 'weiyu_posts')->rows([
            'authorId'     => $isLogin ? $user->uid : 0,
            'authorName'   => $isLogin ? $user->screenName : $guestName,
            'authorMail'   => $isLogin ? $user->mail : $guestMail,
            'authorUrl'    => $isLogin ? $user->url : $guestUrl,
            'title'        => $title,
            'content'      => $content,
            'images'       => json_encode($imgArr),
            'music_cover'  => $musicCover,
            'music_title'  => $musicTitle,
            'music_artist' => $musicArtist,
            'music_link'   => $musicLink,
            'video_link'   => $videoLink,
            'video_cover'  => $videoCover,
            'likes'        => 0,
            'commentsNum'  => 0,
            'status'       => $status,
            'created'      => $time
        ]));

        $msg = $status ? '发布成功' : '发布成功，等待站长审核';
        $this->jsonResponse(['code' => 200, 'msg' => $msg]);
    }

    private function edit($request, $db, $prefix, $user)
    {
        if (!$user->hasLogin()) {
            $this->jsonResponse(['code' => 401, 'msg' => '请先登录']);
        }

        $id = intval($request->get('id'));
        $post = $db->fetchRow($db->select()->from($prefix . 'weiyu_posts')->where('id = ?', $id));
        if (!$post) {
            $this->jsonResponse(['code' => 404, 'msg' => '微语不存在']);
        }

        if ($user->uid != $post['authorId'] && !$user->pass('administrator', true)) {
            $this->jsonResponse(['code' => 403, 'msg' => '无权编辑']);
        }

        $title = trim($request->get('title'));
        $content = trim($request->get('content'));
        $imagesRaw = trim($request->get('images'));
        $imgArr = [];
        if (!empty($imagesRaw)) {
            $lines = preg_split('/\r\n|\r|\n/', $imagesRaw);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && filter_var($line, FILTER_VALIDATE_URL)) {
                    $imgArr[] = $line;
                }
            }
        }
        if (count($imgArr) > 6) {
            $this->jsonResponse(['code' => 400, 'msg' => '最多只能上传 6 张图片']);
        }

        $db->query($db->update($prefix . 'weiyu_posts')->rows([
            'title'        => $title,
            'content'      => $content,
            'images'       => json_encode($imgArr),
            'music_cover'  => trim($request->get('music_cover')),
            'music_title'  => trim($request->get('music_title')),
            'music_artist' => trim($request->get('music_artist')),
            'music_link'   => trim($request->get('music_link')),
            'video_link'   => trim($request->get('video_link')),
            'video_cover'  => trim($request->get('video_cover'))
        ])->where('id = ?', $id));

        $this->jsonResponse(['code' => 200, 'msg' => '编辑成功']);
    }

    private function delete($request, $db, $prefix, $user)
    {
        if (!$user->hasLogin()) {
            $this->jsonResponse(['code' => 401, 'msg' => '请先登录']);
        }

        $id = intval($request->get('id'));
        $post = $db->fetchRow($db->select()->from($prefix . 'weiyu_posts')->where('id = ?', $id));
        if (!$post) {
            $this->jsonResponse(['code' => 404, 'msg' => '微语不存在']);
        }

        if ($user->uid != $post['authorId'] && !$user->pass('administrator', true)) {
            $this->jsonResponse(['code' => 403, 'msg' => '无权删除']);
        }

        $db->query($db->delete($prefix . 'weiyu_posts')->where('id = ?', $id));
        $db->query($db->delete($prefix . 'weiyu_comments')->where('post_id = ?', $id));
        $db->query($db->delete($prefix . 'weiyu_likes')->where('post_id = ?', $id));

        $this->jsonResponse(['code' => 200, 'msg' => '删除成功']);
    }

    private function like($request, $db, $prefix, $user)
    {
        $postId = intval($request->get('post_id'));
        $ip = $request->getIp();
        $userId = $user->hasLogin() ? $user->uid : 0;
        $guestName = trim($request->get('guest_name'));
        $guestMail = trim($request->get('guest_mail'));

        if ($userId > 0) {
            $existing = $db->fetchRow($db->select()->from($prefix . 'weiyu_likes')
                ->where('post_id = ?', $postId)
                ->where('user_id = ?', $userId));
        } else {
            $existing = $db->fetchRow($db->select()->from($prefix . 'weiyu_likes')
                ->where('post_id = ?', $postId)
                ->where('user_id = ?', 0)
                ->where('ip = ?', $ip));
        }

        if ($existing) {
            $db->query($db->delete($prefix . 'weiyu_likes')->where('id = ?', $existing['id']));
            $db->query($db->update($prefix . 'weiyu_posts')->expression('likes', 'likes - 1')->where('id = ?', $postId));
            $this->jsonResponse(['code' => 200, 'msg' => '已取消点赞', 'action' => 'unlike']);
        } else {
            $db->query($db->insert($prefix . 'weiyu_likes')->rows([
                'post_id'    => $postId,
                'user_id'    => $userId,
                'guest_name' => $guestName,
                'guest_mail' => $guestMail,
                'ip'         => $ip,
                'created'    => time()
            ]));
            $db->query($db->update($prefix . 'weiyu_posts')->expression('likes', 'likes + 1')->where('id = ?', $postId));
            $this->jsonResponse(['code' => 200, 'msg' => '点赞成功', 'action' => 'like']);
        }
    }

    private function comment($request, $db, $prefix, $user)
    {
        $postId = intval($request->get('post_id'));
        $parentId = intval($request->get('parent_id', 0));
        $content = trim($request->get('content'));
        $author = trim($request->get('author'));
        $mail = trim($request->get('mail'));
        $url = trim($request->get('url'));

        if ($user->hasLogin()) {
            $author = $user->screenName;
            $mail = $user->mail;
            $url = $user->url;
        }

        if (empty($content)) {
            $this->jsonResponse(['code' => 400, 'msg' => '评论内容不能为空']);
        }
        if (empty($author) || empty($mail)) {
            $this->jsonResponse(['code' => 400, 'msg' => '请填写昵称和邮箱']);
        }
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(['code' => 400, 'msg' => '邮箱格式不正确']);
        }

        $time = time();
        $db->query($db->insert($prefix . 'weiyu_comments')->rows([
            'post_id'   => $postId,
            'parent_id' => $parentId,
            'author'    => $author,
            'mail'      => $mail,
            'url'       => $url,
            'ip'        => $request->getIp(),
            'content'   => $content,
            'created'   => $time
        ]));

        $db->query($db->update($prefix . 'weiyu_posts')->expression('commentsNum', 'commentsNum + 1')->where('id = ?', $postId));

        $this->jsonResponse(['code' => 200, 'msg' => '评论成功']);
    }

    private function editComment($request, $db, $prefix, $user)
    {
        if (!$user->hasLogin()) {
            $this->jsonResponse(['code' => 401, 'msg' => '请先登录']);
        }

        $id = intval($request->get('id'));
        $content = trim($request->get('content'));
        if (empty($content)) {
            $this->jsonResponse(['code' => 400, 'msg' => '内容不能为空']);
        }

        $db->query($db->update($prefix . 'weiyu_comments')->rows([
            'content' => $content
        ])->where('id = ?', $id));

        $this->jsonResponse(['code' => 200, 'msg' => '编辑成功']);
    }

    private function deleteComment($request, $db, $prefix, $user)
    {
        if (!$user->hasLogin()) {
            $this->jsonResponse(['code' => 401, 'msg' => '请先登录']);
        }

        $id = intval($request->get('id'));
        $comment = $db->fetchRow($db->select()->from($prefix . 'weiyu_comments')->where('id = ?', $id));
        if (!$comment) {
            $this->jsonResponse(['code' => 404, 'msg' => '评论不存在']);
        }

        $db->query($db->delete($prefix . 'weiyu_comments')->where('id = ?', $id));
        $db->query($db->update($prefix . 'weiyu_posts')->expression('commentsNum', 'commentsNum - 1')->where('id = ?', $comment['post_id']));

        $this->jsonResponse(['code' => 200, 'msg' => '删除成功']);
    }

    private function getPosts($request, $db, $prefix)
    {
        $page = max(1, intval($request->get('page', 1)));
        $pageSize = 10;

        $total = $db->fetchObject($db->select(['COUNT(id)' => 'num'])->from($prefix . 'weiyu_posts')->where('status = ?', 1))->num;
        $totalPage = ceil($total / $pageSize);

        $posts = $db->fetchAll($db->select()->from($prefix . 'weiyu_posts')
            ->where('status = ?', 1)
            ->order('created', Db::SORT_DESC)
            ->page($page, $pageSize));

        $user = User::alloc();
        $userId = $user->hasLogin() ? $user->uid : 0;
        $ip = $request->getIp();

        foreach ($posts as &$post) {
            $post['images'] = !empty($post['images']) ? json_decode($post['images'], true) : [];
            $post['created_format'] = $this->formatTime($post['created']);

            if ($post['authorId'] > 0) {
                $authorInfo = $db->fetchRow($db->select('screenName', 'mail')->from('table.users')->where('uid = ?', $post['authorId']));
                $post['author_name'] = $authorInfo ? $authorInfo['screenName'] : '博主';
                $post['author_avatar'] = Plugin::getAvatar($authorInfo ? $authorInfo['mail'] : '');
            } else {
                $post['author_name'] = $post['authorName'] ?: '游客';
                $post['author_avatar'] = Plugin::getAvatar($post['authorMail']);
            }
            $post['can_delete'] = $user->hasLogin() && ($user->uid == $post['authorId'] || $user->pass('administrator', true));

            $likes = $db->fetchAll($db->select()->from($prefix . 'weiyu_likes')->where('post_id = ?', $post['id']));
            $post['like_list'] = [];
            $post['has_liked'] = false;
            foreach ($likes as $like) {
                $likeUser = ['name' => '游客', 'avatar' => 'https://gravatar.loli.net/avatar/000?s=80&d=identicon'];
                if ($like['user_id'] > 0) {
                    $u = $db->fetchRow($db->select('screenName', 'mail')->from('table.users')->where('uid = ?', $like['user_id']));
                    if ($u) {
                        $likeUser['name'] = $u['screenName'];
                        $likeUser['avatar'] = Plugin::getAvatar($u['mail']);
                    }
                } else if (!empty($like['guest_mail'])) {
                    $likeUser['name'] = $like['guest_name'] ?: '游客';
                    $likeUser['avatar'] = Plugin::getAvatar($like['guest_mail']);
                }
                $post['like_list'][] = $likeUser;
                if ($userId > 0) {
                    if ($like['user_id'] == $userId) $post['has_liked'] = true;
                } else {
                    if ($like['user_id'] == 0 && $like['ip'] == $ip) $post['has_liked'] = true;
                }
            }

            $comments = $db->fetchAll($db->select()->from($prefix . 'weiyu_comments')
                ->where('post_id = ?', $post['id'])
                ->order('created', Db::SORT_ASC));
            $post['comments'] = $this->formatComments($comments);
        }
        unset($post);

        $this->jsonResponse([
            'code' => 200,
            'data' => $posts,
            'page' => $page,
            'totalPage' => $totalPage,
            'total' => $total
        ]);
    }

    private function getComments($request, $db, $prefix)
    {
        $postId = intval($request->get('post_id'));
        $comments = $db->fetchAll($db->select()->from($prefix . 'weiyu_comments')
            ->where('post_id = ?', $postId)
            ->order('created', Db::SORT_ASC));
        $this->jsonResponse(['code' => 200, 'data' => $this->formatComments($comments)]);
    }

    private function getPost($request, $db, $prefix)
    {
        $id = intval($request->get('id'));
        $post = $db->fetchRow($db->select()->from($prefix . 'weiyu_posts')->where('id = ?', $id));
        if (!$post) {
            $this->jsonResponse(['code' => 404, 'msg' => '微语不存在']);
        }
        $post['images'] = !empty($post['images']) ? json_decode($post['images'], true) : [];
        $this->jsonResponse(['code' => 200, 'data' => $post]);
    }

    private function approve($request, $db, $prefix, $user)
    {
        if (!$user->hasLogin() || !$user->pass('administrator', true)) {
            $this->jsonResponse(['code' => 403, 'msg' => '无权操作']);
        }
        $id = intval($request->get('id'));
        $db->query($db->update($prefix . 'weiyu_posts')->rows(['status' => 1])->where('id = ?', $id));
        $this->jsonResponse(['code' => 200, 'msg' => '审核通过']);
    }

    private function checkLogin($request, $user)
    {
        $isLogin = $user->hasLogin();
        $this->jsonResponse([
            'code' => 200,
            'isLogin' => $isLogin,
            'isAdmin' => $isLogin && $user->pass('administrator', true)
        ]);
    }

    private function formatComments($comments)
    {
        $map = [];
        foreach ($comments as $c) $map[$c['id']] = $c;

        $result = [];
        foreach ($comments as $c) {
            $c['created_format'] = $this->formatTime($c['created']);
            $c['is_child'] = false;
            $c['parent_name'] = '';
            $c['avatar'] = Plugin::getAvatar($c['mail']);
            if ($c['parent_id'] > 0 && isset($map[$c['parent_id']])) {
                $c['is_child'] = true;
                $c['parent_name'] = $map[$c['parent_id']]['author'];
            }
            $result[] = $c;
        }
        return $result;
    }

    private function formatTime($time)
    {
        $diff = time() - $time;
        if ($diff < 60) return '刚刚';
        if ($diff < 3600) return floor($diff / 60) . '分钟前';
        if ($diff < 86400) return floor($diff / 3600) . '小时前';
        if ($diff < 604800) return floor($diff / 86400) . '天前';
        return date('Y-m-d H:i', $time);
    }
}
