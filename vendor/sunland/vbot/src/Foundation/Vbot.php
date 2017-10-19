<?php
/**
 * Created by PhpStorm.
 * User: zhaoliang
 * Date: 2017/10/19
 * Time: 10:01.
 */

namespace Sunland\Vbot\Foundation;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;

class Vbot extends Container
{
    private $uin = 0;
    /**
     * Service Providers.
     *
     * @var array
     */
    protected $providers = [
        ServiceProviders\LogServiceProvider::class,
        ServiceProviders\ServerServiceProvider::class,
        //ServiceProviders\ExceptionServiceProvider::class,
        //ServiceProviders\CacheServiceProvider::class,
        ServiceProviders\HttpServiceProvider::class,
        //ServiceProviders\ObserverServiceProvider::class,
        //ServiceProviders\ConsoleServiceProvider::class,
        //ServiceProviders\MessageServiceProvider::class,
        //ServiceProviders\ContactServiceProvider::class,
        //ServiceProviders\ApiServiceProvider::class,
        //ServiceProviders\ExtensionServiceProvider::class,
    ];


    public function __construct(array $config)
    {
        $this->initializeConfig($config);

        //(new Kernel($this))->bootstrap();

        static::$instance = $this;

        $this->registerProviders();
        $this->initializePath();
    }

    private function initializeConfig(array $config)
    {
        $this->config = new Repository($config);

        if (!is_dir($this->vbot->config['path'])) {
            mkdir($this->vbot->config['path'], 0755, true);
        }

        $this->vbot->config['storage'] = $this->vbot->config['storage'] ?: 'collection';

        $this->vbot->config['path'] = realpath($this->vbot->config['path']);

    }

    /**
     * Register providers.
     */
    public function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    private function initializePath()
    {
        if (!is_dir($this->vbot->config['path'].'/cookies')) {
            mkdir($this->vbot->config['path'].'/cookies', 0755, true);
        }

        if (!is_dir($this->vbot->config['path'].'/users')) {
            mkdir($this->vbot->config['path'].'/users', 0755, true);
        }

        if (!is_dir($this->vbot->config['download.emoticon_path'])) {
            mkdir($this->vbot->config['download.emoticon_path'], 0755, true);
        }

        //$this->vbot->config['cookie_file'] = $this->vbot->config['path'].'/cookies/'.$this->vbot->config['session'];
        $this->vbot->config['user_path'] = $this->vbot->config['path'].'/users/';
    }


}
