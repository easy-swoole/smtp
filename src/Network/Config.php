<?php
/**
 * Created by PhpStorm.
 * User: xcg
 * Date: 2020/4/14
 * Time: 15:28
 */

namespace EasySwoole\Smtp\Network;

use EasySwoole\Spl\SplBean;

class Config extends SplBean
{
    protected $timeout = 3.0;
    protected $maxPackage = 2 * 1024 * 1024;


    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }

    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * @return float|int
     */
    public function getMaxPackage()
    {
        return $this->maxPackage;
    }

    /**
     * @param float|int $maxPackage
     */
    public function setMaxPackage($maxPackage): void
    {
        $this->maxPackage = $maxPackage;
    }


}