项目介绍
=======
微信授权桥接项目，可能我们在做微信开发的时候会遇到一个问题，一个公众号只能绑定一个域名，那很多项目都是一个域名一个项目，那应该怎么办呢，我们又不能申请很多公众号，那这个项目就是为这种情况准备的
[线上地址](https://admin.fastgoo.net/vue/dist/index.html).

安装
------------
该框架需要安装扩展
[官方扩展安装](https://phalconphp.com/zh/download/linux)
[开发扩展工具](https://github.com/phalcon/phalcon-devtools)
[中文扩展安装](http://www.iphalcon.cn/reference/install.html)

```bash
git clone https://github.com/jungege520/wechat-auth.phalcon.git wechat-auth
cd phalcon
composer install
```

范例
------------
1、[扫描二维码登录](https://wechat.zhoujianjun.cn/qrcodeDemo)<br/>
```bash
设计思路：
生成一张待authKey参数的二维码，该参数随机且不重复
用户扫描二维码，先跳转到微信授权页面授权，同时把授权获得信息存入缓存，该缓存带有状态
用户点击确认登录，修改缓存状态为1，取消则为2 无操作默认0
网站通过计划任务向指定接口获取到对应key值的缓存数据（该缓存数据通过jwt加密成token），
获取到对应的用户信息的token以后跳转到预设好的回调地址，同时在url带上wechatToken参数，该参数需要在服务端解密
```


2、[跳转重定向登录](https://wechat.zhoujianjun.cn/redirectDemo)
```bash
设计思路：
网站跳转到指定的授权回调地址同时带上redirectUrl的参数，该参数为回调参数
授权回调地址通过微信授权以后直接把对应的数据加密成token拼装到redirectUrl上去
该参数需要在服务端解密
```

备注：为什么一定要加密，就是为了防止用户的数据通过缓存以后被劫持篡改，所以通过jwt加密以后提交数据的安全性，但是这也导致url链接变长

依赖包
-------

1、[PHP-JWT token认证](https://github.com/firebase/php-jwt)：集成JWT认证类，已封装加密解密的类方法<br>

2、[微信SDK](https://github.com/thenbsp/wechat)：微信的大部分常用的SDK都已封装，可查看WIKI文档<br>

2、[二维码生成类](https://github.com/SimpleSoftwareIO/simple-qrcode)：常用的二维码生成类包<br>



微型项目结构
-------

```bash
project/
  app/
    config/       ---配置文件
    services/     ---业务类（存放业务操作方法）
    views/        ---视图
  public/         ---公共资源
    css/
    img/
    js/
  cache/          ---缓存文件（缓存，视图）
  vendor/         ---composer包管理
```


感言
-------

1、命名空间是个大坑，写方法的时候一定要注意命名空间的使用，一不小心就坑的你吐。

2、不要重复造轮子，多去找找有没有composer包，[点击传送门](https://packagist.org/)

3、多查看手册  [官方英文手册](https://docs.phalconphp.com/en/3.2) [3.0的中文手册](http://www.iphalcon.cn/)

4、记住多看手册，基本上大部分遇到的坑都会在手册查看，类的用法可以多查API [点击传送门](https://docs.phalconphp.com/en/3.2/api/index)



加入我们
-------
交流群：150237524

我的QQ：773729704 记得备注github

我的微信：huoniaojugege  记得备注github
