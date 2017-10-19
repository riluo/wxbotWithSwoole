<?php
require_once __DIR__.'/vendor/autoload.php';

use PHPQRCode\QRcode;


$client = new GuzzleHttp\Client();

$response = $client->request('GET','https://login.weixin.qq.com/jslogin', [
    'appid' => 'wx782c26e4c19acffb',
    'fun'   => 'new',
    'lang'  => 'zh_CN',
    '_'     => time(),
    'timeout' => 10,
    'verify' => false
]);
$content = $response->getBody()->getContents();

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


