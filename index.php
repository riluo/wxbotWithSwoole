<?php
require_once __DIR__.'/vendor/autoload.php';

use PHPQRCode\QRcode;

function _get($url,$params=[],$api = false){
    $oCurl = curl_init();
    if(stripos($url,"https://")!==FALSE){
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    $header = [
        'User-Agent: '.'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/48.0.2564.109 Safari/537.36',
        'Referer: https://wx.qq.com/'
    ];
    if($api == 'webwxgetvoice')
        $header[]='Range: bytes=0-';
    if($api == 'webwxgetvideo')
        $header[]='Range: bytes=0-';
    curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
    if(!empty($params)){
        if(strpos($url,'?')!==false){
            $url .="&".http_build_query($params);
        }else{
            $url .="?".http_build_query($params);
        }
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
    curl_setopt($oCurl, CURLOPT_TIMEOUT, 36);
    curl_setopt($oCurl, CURLOPT_COOKIEFILE, $this->cookie);
    curl_setopt($oCurl, CURLOPT_COOKIEJAR, $this->cookie);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if(intval($aStatus["http_code"])==200){
        return $sContent;
    }else{
        return false;
    }
}

$client = new GuzzleHttp\Client();

$content = _get('https://login.weixin.qq.com/jslogin', [
    'appid' => 'wx782c26e4c19acffb',
    'fun'   => 'new',
    'lang'  => 'zh_CN',
    '_'     => time(),
]);
var_dump($content);

preg_match('/window.QRLogin.code = (\d+); window.QRLogin.uuid = \"(\S+?)\"/', $content, $matches);

if (!$matches) {
    throw new Exception('fetch uuid failed.');
}

$uuid =  $matches[2];

$code = new QRcode();
$code::png("https://login.weixin.qq.com/l/".$uuid, "./img/".$uuid.".png", 'H', 4, 2);
?>
<p align="center"><img src="./img/<?php echo $imgName;?>.png" style="margin-top:10px;" /></p>
<?php
exec("ps -ef | grep daemon.php | grep -v grep | awk '{print $2}' |xargs kill -9");
$cmd = "/usr/local/php/bin/php /data/wwwroot/wxbotWithSwoole/daemon.php $uuid";
pclose(popen($cmd.' > /tmp/vbot.log &', 'r'));
?>


