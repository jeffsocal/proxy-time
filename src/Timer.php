<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyTime;

class Timer
{

    protected $time_start;

    protected $time_stop;

    function __construct()
    {
        $this->start();
    }

    function getTime()
    {
        return microtime(TRUE);
    }

    protected function start()
    {
        $this->time_start = $this->getTime();
    }

    protected function stop()
    {
        $this->time_stop = $this->getTime();
    }

    public function timeinmsec(){
        $this->stop();
        return ($this->time_stop - $this->time_start);
    }
    
    public function timeinsec()
    {
        return round($this->timeinmsec());
    }

    public function timeinstr($show_msec = FALSE)
    {
        return time_toString($this->timeinmsec(), $show_msec);
    }
}
?>