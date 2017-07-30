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
 * TODO ===========================   微信扫码登录   ============================================
 */

/**
 * 获取登录二维码
 */
/**
 * 微信扫码授权
 * 授权数据存入缓存
 * 同时跳转页面到是否同意登录申请，同意则登录，不同意则取消
 */
$app->get('/getQrcode', function () {
    $key = !empty($_GET['authKey']) ? $_GET['authKey'] : 123;
    $qrcode = new SimpleSoftwareIO\QrCode\BaconQrCodeGenerator();
    echo $qrcode->size(500)->generate('https://wechat.zhoujianjun.cn/authLogin?timestamp=' . (time() + 300) . '&authKey=' . $key);
});


/**
 * 微信扫码授权
 * 授权数据存入缓存
 * 同时跳转页面到是否同意登录申请，同意则登录，不同意则取消
 */
$app->get('/authLogin', function () use ($cache) {
    $key = !empty($_GET['authKey']) ? $_GET['authKey'] : '';
    $timestamp = !empty($_GET['timestamp']) ? $_GET['timestamp'] : '';
    if (empty($key)) {
        $this['view']->error = '授权KEY获取失败';
        exit($this['view']->render('auth-login-error'));
    }
    if ($timestamp < time()) {
        $this['view']->error = '该二维码已失效，请重新二维码';
        exit($this['view']->render('auth-login-error'));
    }
    $wechatInfo = $cache->get($key);
    if (!$wechatInfo) {
        $wechat = new Services\WechatAuth();
        $wechatInfo = ['userInfo' => $wechat->auth(), 'status' => 0];
        $cache->save($key, $wechatInfo);
    } else {
        if ($wechatInfo['status']) {
            $this['view']->error = '该二维码已失效，请重新二维码';
            exit($this['view']->render('auth-login-error'));
        }
    }
    $this->session->set('openid', $wechatInfo['userInfo']->openid);
    $this['view']->authKey = $key;
    echo $this['view']->render('auth-login');
});

/**
 * 设置缓存授权状态
 */
$app->post('/setAuth', function () use ($cache) {
    $key = !empty($_POST['authKey']) ? $_POST['authKey'] : '';
    $status = !empty($_POST['status']) ? $_POST['status'] : '';
    if (empty($key)) {
        responseData(-1, '授权key不能为空');
    }
    $authCache = $cache->get($key);
    if (!$authCache) {
        responseData(-100, '该用户未微信授权，请重新授权登录');
    }
    if ($this->session->get('openid') != $authCache['userInfo']->openid) {
        responseData(-1, '不可操作其他用户的数据');
    }
    $authCache['status'] = $status ? 1 : 2;
    $flag = $cache->save($key, $authCache);
    if ($flag) {
        responseData(1, $status?'授权成功':'已取消');
    } else {
        responseData(-1, '授权失败');
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
    if ($authCache['status'] == 2) {
        responseData(-5, '该用户已取消登录');
    }
    $token = \Services\JwtAuth::type()->encode($authCache['userInfo']);
    responseData(1, '授权成功', compact('token'));
});


/**
 * TODO ===========================   微信公众号重定向授权登录   ============================================
 */

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
    $userInfo->expire_time = time() + 300;
    $checkParam = strpos($url, '?');
    if ($checkParam) {
        $url = $url . '&wechatToken=' . \Services\JwtAuth::type()->encode($userInfo);
    } else {
        $url = $url . '?wechatToken=' . \Services\JwtAuth::type()->encode($userInfo);
    }
    header("Location:" . $url);
});


/**
 * 微信web公众号授权
 * 授权获取用户信息，直接跳转到改用户设置的回调地址上同时带上用户信息
 */
$app->get('/getUser', function () use ($cache) {
    $userI = \Services\JwtAuth::type()->decode($_GET['wechatToken']);
    var_dump($userI, 111111);
});


/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo $app['view']->render('404');
});
