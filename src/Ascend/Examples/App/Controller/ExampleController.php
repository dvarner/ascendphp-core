<?php namespace Ascend\Examples\App\Controller;

use Ascend\Core\View;

class ExampleController
{
    public static function viewIndex()
    {
        $tpl = [];
        $tpl['container'] = View::html('index.php', $tpl, 'Examples');

        echo View::html('_template.php', $tpl, 'Examples');
    }
}