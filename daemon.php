<?php
/**
 * Created by PhpStorm.
 * User: zhaoliang
 * Date: 17/10/19
 * Time: 下午3:14
 */
require_once __DIR__.'/vendor/autoload.php';

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Cookie\FileCookieJar;


$path = __DIR__.'/tmp/';
$options = [
    'path'     => $path,
];

class Server
{
    private $client;
    private $urlRedirect;
    private $uriFile;
    private $uriPush;
    private $uriBase;
    private $cookFile;

    private $uuid;
    private $skey;
    private $sid;
    private $uin;
    private $passTicket;
    private $deviceId;
    private $baseRequest;


    public function __construct($uuid)
    {
        $this->uuid = $uuid;
        $this->cookFile = __DIR__.'/tmp/'.'/cookies/'.$this->uuid;
        $this->http = new Sunland\Vbot\Support\Http($this->cookFile);
    }

    public function serve()
    {
        $this->login();

        $this->init();
    }

    /**
     * login.
     */
    public function login()
    {
        $this->waitForLogin();
        $this->getLogin();
    }


    /**
     * waiting user to login.
     *
     * @throws \Exception
     */
    protected function waitForLogin()
    {
        $retryTime = 10;
        $tip = 1;

        echo 'please scan the qrCode with wechat.';
        while ($retryTime > 0) {
            $url = sprintf('https://login.weixin.qq.com/cgi-bin/mmwebwx-bin/login?tip=%s&uuid=%s&_=%s', $tip, $this->uuid, time());
            $content = $this->http->get($url, ['timeout' => 35, 'connect_timeout' => 6]);

            preg_match('/window.code=(\d+);/', $content, $matches);

            $code = $matches[1];
            switch ($code) {
                case '201':
                    echo 'please confirm login in wechat.';
                    $tip = 0;
                    break;
                case '200':
                    preg_match('/window.redirect_uri="(https:\/\/(\S+?)\/\S+?)";/', $content, $matches);

                    $this->urlRedirect = $matches[1].'&fun=new';
                    $url = 'https://%s/cgi-bin/mmwebwx-bin';
                    $this->uriFile = sprintf($url, 'file.'.$matches[2]);
                    $this->uriPush = sprintf($url, 'webpush.'.$matches[2]);
                    $this->uriBase = sprintf($url, $matches[2]);

                    return;
                case '408':
                    $tip = 1;
                    $retryTime -= 1;
                    sleep(1);
                    break;
                default:
                    $tip = 1;
                    $retryTime -= 1;
                    sleep(1);
                    break;
            }
        }

        echo 'login time out!';
        throw new Exception('Login time out.');
    }

    /**
     * login wechat.
     *
     * @throws \Exception
     */
    private function getLogin()
    {
        $content = $this->http->get($this->urlRedirect);

        $data = (array) simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (in_array('', [$data['wxsid'], $data['wxuin'], $data['pass_ticket']])) {
            throw new Exception('Login failed.');
        }

        $this->skey = $data['skey'];
        $this->sid = $data['wxsid'];
        $this->uin = $data['wxuin'];
        $this->passTicket = $data['pass_ticket'];
        $this->deviceId = 'e'.substr(mt_rand().mt_rand(), 1, 15);

        $this->baseRequest = [
            'Uin'      => $data['wxuin'],
            'Sid'      => $data['wxsid'],
            'Skey'     => $data['skey'],
            'DeviceID' => $this->deviceId,
        ];

        $this->saveServer();
    }

    /**
     * store config to cache.
     */
    private function saveServer()
    {
        //将信息保存到redis或者用户配置文件中

    }

    /**
     * init.
     *
     * @param bool $first
     *
     * @throws InitFailException
     */
    protected function init($first = true)
    {
        $this->beforeInitSuccess();
        $url = $this->uriBase.'/webwxinit?r='.time();

        $result = $this->http->json($url, [
            'BaseRequest' => $this->baseRequest,
        ], true);

        $this->afterInitSuccess($result);
    }

    /**
     * before init success.
     */
    private function beforeInitSuccess()
    {
        echo 'init begin.';
    }

    /**
     * after init success.
     *
     * @param $content
     */
    private function afterInitSuccess($content)
    {
        echo 'response:'.json_encode($content);
        echo 'init success.';
    }

}

$server = new Server($argv[1]);
$server->serve();