<?php
namespace TypechoPlugin\Weiyu;

use Typecho\Widget;

class Page extends Widget
{
    public function render()
    {
        include __DIR__ . '/page-weiyu.php';
    }

    public function post()
    {
        include __DIR__ . '/post-weiyu.php';
    }
}
