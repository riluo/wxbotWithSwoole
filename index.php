<?php
require_once __DIR__.'/vendor/autoload.php';
use PHPQRCode\QRcode;


$client = new GuzzleHttp\Client();
$res = $client->request('GET', 'https://login.weixin.qq.com/jslogin', [
    'appid' => 'wx782c26e4c19acffb',
    'fun'   => 'new',
    'lang'  => 'zh_CN',
    '_'     => time(),
]);
var_dump($res);

preg_match('/window.QRLogin.code = (\d+); window.QRLogin.uuid = \"(\S+?)\"/', $res, $matches);

if (!$matches) {
    throw new Exception('fetch uuid failed.');
}

$uuid =  $matches[2];

$code = new QRcode();
$code::png("https://login.weixin.qq.com/l/".$uuid, "./img/".$uuid.".png", 'H', 4, 2);
?>
<p align="center"><img src="./img/<?php echo $imgName;?>.png" style="margin-top:10px;" /></p>
<?php
$cmd = "/usr/local/php/bin/php /data/wwwroot/wxbotWithSwoole/daemon.php $uuid";
pclose(popen($cmd.' > /tmp/vbot.log &', 'r'));
?>


