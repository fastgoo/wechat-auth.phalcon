<?php
/**
 * Created by PhpStorm.
 * User: Mr.Zhou
 * Date: 2017/7/29
 * Time: 下午1:40
 */

namespace Services;

use Thenbsp\Wechat\OAuth\Client;


class Wechat
{
    /**
     * 微信授权登录
     */
    public function auth()
    {
        $client = new Client('appid', 'appsecret');
        $client->setScope('snsapi_userinfo');
        $client->setRedirectUri('http://example.com/callback.php');

        if (!isset($_GET['code'])) {
            header('Location: ' . $client->getAuthorizeUrl());
        }
        $accessToken = $client->getAccessToken($_GET['code']);
        var_dump($accessToken->toArray());
        $userinfo = $accessToken->getUser();
        var_dump($userinfo->toArray());
    }
}