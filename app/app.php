<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

/**
 * 初始化缓存设置
 */
$cache = new BackFile(new FrontData(["lifetime" => 300]), ["cacheDir" => BASE_PATH . "/cache/"]);


function responseData($code = 1, $msg = 'success', $data = [])
{
    if (empty($data) || (!is_array($data) && !is_object($data))) {
        $data = new stdClass();
    }
    $response = \Phalcon\Di::getDefault()->getResponse();
    $response->setJsonContent(compact('code', 'msg', 'data'));
    $response->send();
    die;
}


/**
 * Add your routes here
 */
$app->get('/', function () {
    echo $this['view']->render('index');
});


/**
 * 微信扫码授权
 * 授权数据存入缓存
 * 同时跳转页面到是否同意登录申请，同意则登录，不同意则取消
 */
$app->get('/authLogin', function () use ($cache) {
    $key = !empty($_GET['authKey']) ? $_GET['authKey'] : '';
    $url = !empty($_GET['redirectUrl']) ? $_GET['redirectUrl'] : '';
    if (empty($key) || empty($url)) {
        $this['view']->error = empty($key) ? '授权KEY' : '回调地址';
        exit($this['view']->render('auth-login-error'));
    }
    if (!($cache->get($key))) {
        $wechat = new Services\WechatAuth();
        $cache->save($key, ['redirectUrl' => $url, 'userInfo' => $wechat->auth(), 'status' => 0]);
    }
    echo $this['view']->render('auth-login');
});

/**
 * 设置缓存授权状态
 */
$app->post('/setAuth', function () use ($cache) {
    $key = !empty($_POST['authKey']) ? $_POST['authKey'] : '';
    if (empty($key)) {
        responseData(-1, '授权key不能为空');
    }
    $authCache = $cache->get($key);
    if (!$authCache) {
        responseData(-100, '该用户未微信授权，请重新授权登录');
    }
    $authCache['status'] = 1;
    $flag = $cache->save($key, $authCache);
    if ($flag) {
        responseData(1, '授权成功');
    } else {
        responseData(1, '授权失败');
    }
});

/**
 * 获取认证授权状态
 * 如果用户已授权则返回对应的缓存数据
 */
$app->post('/getAuth', function () use ($cache) {
    $key = !empty($_POST['authKey']) ? $_POST['authKey'] : '';
    if (empty($key)) {
        responseData(-1, '授权key不能为空');
    }
    $authCache = $cache->get($key);
    if (!$authCache || empty($authCache['status'])) {
        responseData(-2, '该用户未微信授权，请重新授权登录');
    }
    responseData(1, '授权成功', $authCache);
});


/**
 * 微信web公众号授权
 * 授权获取用户信息，直接跳转到改用户设置的回调地址上同时带上用户信息
 */
$app->get('/authWeb', function () use ($cache) {
    $url = !empty($_GET['redirectUrl']) ? $_GET['redirectUrl'] : '';
    if (empty($url)) {
        $this['view']->error = '回调地址';
        exit($this['view']->render('auth-login-error'));
    }
    $wechat = new Services\WechatAuth();
    $userInfo = $wechat->auth();
    $checkParam = strpos($url, '?');
    if ($checkParam) {
        $url = $url . '&sign' . base64_encode(json_encode($userInfo));
    } else {
        $url = $url . '?sign' . base64_encode(json_encode($userInfo));
    }
    \Phalcon\Di::getDefault()->getResponse()->redirect($url, true);
});


$app->get('/redirect', function () use ($app) {
    echo $app['view']->render('404');
});


/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo $app['view']->render('404');
});
