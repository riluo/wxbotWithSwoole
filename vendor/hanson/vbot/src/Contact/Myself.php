<?php

namespace Hanson\Vbot\Contact;

use Hanson\Vbot\Support\Content;
use Monolog\Handler\StreamHandler;

class Myself
{
    public $nickname;

    public $username;

    public $uin;

    public $sex;

    public function init($user)
    {
        $this->nickname = Content::emojiHandle($user['NickName']);
        $this->username = $user['UserName'];
        $this->sex = $user['Sex'];
        $this->uin = $user['Uin'];

        $this->log();

        $this->setPath();
        $this->setLog();
    }

    private function log()
    {
        $friends = vbot('friends');
        vbot('console')->log('current user\'s nickname:'.$this->nickname);
        vbot('console')->log('current user\'s username:'.$this->username);
        vbot('console')->log('current user\'s uin:'.$this->uin);

        $pdo = new \PDO("mysql:host=localhost;dbname=sd_chat","root","Sunland16");
        $q = $pdo->query("SELECT count(*) as count from config where Uin = ".$this->uin);
        $q->setFetchMode(\PDO::FETCH_ASSOC);

        $data = $friends->getAvatar($this->username);
        $headImageUrl = "http://119.29.133.42/img/avatar/".$this->uin.'/'.md5($this->nickname).'.jpg';

        $avatar_dir = './img/avatar/'.$this->uin;
        $avatar_file = './img/avatar/'.$this->uin.'/'.md5($this->nickname).'.jpg';
        if(!is_dir($avatar_dir)) {
            mkdir($avatar_dir,0775);
        }

        if(!file_exists($avatar_file)){
            $fp = fopen('./img/avatar/'.$this->uin.'/'.md5($this->nickname).'.jpg', 'wb');
            fwrite($fp, $data);
            fclose($fp);
        }

        vbot('console')->log('current user\'s img:'.$headImageUrl);

        $rows = $q->fetch();
        if($rows["count"]>0) {
            $pdo->exec("UPDATE config set username='".$this->username."',nickname='".$this->nickname."',HeadImgUrl='".$headImageUrl."' where Uin = '".$this->uin."'");
        }
    }

    private function setPath()
    {
        $path = vbot('config')['user_path'];

        vbot('config')['user_path'] = $path.$this->uin.DIRECTORY_SEPARATOR;

        if (!is_dir(vbot('config')['user_path']) && $this->uin) {
            mkdir(vbot('config')['user_path'], 0755, true);
        }
    }

    private function setLog()
    {
        vbot('log')->pushHandler(new StreamHandler(
            vbot('config')->get('log.system').DIRECTORY_SEPARATOR.$this->uin.DIRECTORY_SEPARATOR.'vbot.log',
            vbot('config')->get('log.level'),
            true,
            vbot('config')->get('log.permission')
        ));

        vbot('messageLog')->pushHandler(new StreamHandler(
            vbot('config')->get('log.message').DIRECTORY_SEPARATOR.$this->uin.DIRECTORY_SEPARATOR.'message.log',
            vbot('config')->get('log.level'),
            true,
            vbot('config')->get('log.permission')
        ));
    }
}
