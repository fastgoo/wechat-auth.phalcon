<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

function fileCache()
{
    return new BackFile(new FrontData(["lifetime" => 300]), ["cacheDir" => BASE_PATH."/cache/"]);
}


/**
 * Add your routes here
 */
$app->get('/', function () {
    echo $this['view']->render('index');
});



$app->get('/wechat', function () {
    $key = isset($_GET['key'])?$_GET['key']:123;
    $url = isset($_GET['url'])?$_GET['url']:'http://www.baidu.com';
    $cache = fileCache()->get('123');
    if($cache){
        var_dump($cache);
    }else{
        $wechat = new Services\WechatAuth();
        fileCache()->save($key, ['redirectUrl'=>$url,'userInfo'=>$wechat->auth()]);
    }
});


$app->get('/redirect', function () {
    $wechat = new Services\Wechat();
    var_dump($wechat->getAuth(123));

});





/**
 * Not found handler
 */
$app->notFound(function () use($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo $app['view']->render('404');
});
