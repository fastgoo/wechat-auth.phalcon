<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/7/29
 * Time: 下午1:40
 */

namespace Services;

use Thenbsp\Wechat\OAuth\Client;
use Phalcon\Cache\Backend\File as BackFile;
use Phalcon\Cache\Frontend\Data as FrontData;

class Wechat
{

    private $config;
    private $key;

    public function __construct()
    {
        $this->config = \Phalcon\Di::getDefault()->get('wechat_config');
    }
    /**
     * 微信授权登录
     */
    public function auth($key,$url)
    {
        $client = new Client($this->config->auth->app_id, $this->config->auth->secret);
        $client->setScope('snsapi_userinfo');
        $client->setRedirectUri('https://'.$_SERVER['HTTP_HOST'].'/redirect');

        if (!isset($_GET['code'])) {
            header('Location: ' . $client->getAuthorizeUrl());
        }
        $accessToken = $client->getAccessToken($_GET['code']);
        $userinfo = $accessToken->getUser()->toArray();

        $cache =  new BackFile(new FrontData(["lifetime" => 120]), ["cacheDir" => BASE_PATH."/cache/"]);
        $cache->save($key, ['redirectUrl'=>$url,'userInfo'=>$userinfo,'accessToken'=>$accessToken->toArray()]);
    }

    /**
     * 获取授权缓存信息
     * @param $key
     * @return mixed
     */
    public function getAuth($key)
    {
        $cache =  new BackFile(new FrontData(["lifetime" => 120]), ["cacheDir" => BASE_PATH."/cache/"]);
        return $cache->get($key);
    }
}