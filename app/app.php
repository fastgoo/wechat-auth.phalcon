<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */
$app->get('/', function () {
    echo $this['view']->render('index');
});



$app->get('/wechat', function () {
    $wechat = new Services\WechatAuth();
    var_dump($wechat->auth());

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
