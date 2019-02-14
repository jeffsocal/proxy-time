<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyTime;

use ProxyIO\Cli;

class ProgressTimer extends Timer
{

    private $show_memory;

    private $show_progress;

    private $step;

    private $step_size;

    private $step_count;

    private $array_secs;

    private $start_time_unix;

    private $has_echo_nl;

    public $cli;

    function __construct()
    {
        parent::__construct();
        $this->cli = new Cli();
        $this->show_progress = false;
        $this->show_memory = false;
    }

    public function start(string $text = "progress ", int $size = NULL)
    {
        $this->text = $text;
        $this->step_size = $size;
        $this->start_time_unix = date('U');
        $this->array_secs = array();
        $this->has_echo_nl = FALSE;
    }

    public function print()
    {
        $str_left = $this->text;
        $str_rght = time_toString($this->elapsedTime());
        $this->cli->message($str_left, $str_rght);
    }

    public function showProgress(bool $boolean = false)
    {
        $this->show_progress = $boolean;
    }

    public function showMemory(bool $boolean = false)
    {
        $this->show_memory = $boolean;
    }

    public function barPercent(int $percent = 1)
    {
        $this->step_count ++;
        $progress = $this->step_count / $this->step_size;
        if ($progress <= 1 and $progress % $percent == 0)
            $this->showProgressBar($progress);
        
        // if ($progress >= 1 & is_false($this->has_echo_nl)) {
        // $this->has_echo_nl = TRUE;
        // echo PHP_EOL;
        // }
    }

    public function barCount(int $chunk = 1000, $subn = FALSE)
    {
        $this->step_count ++;
        if ($this->step_count % $chunk == 0)
            $this->showCountBar($subn);
    }

    private function elapsedTime()
    {
        return date('U') - $this->start_time_unix;
    }

    private function showCountBar($subn = FALSE)
    {
        $elapsed = $this->elapsedTime();
        $rate = $elapsed / $this->step_count;
        
        $str_left = $this->text;
        
        $str_rght = time_toString($elapsed);
        $str_rght .= " n:";
        
        if (! is_false($subn))
            $str_rght .= $subn . '/';
        
        $str_rght .= $this->step_count;
        
        if ($rate > 0)
            $str_rght .= " rate:" . round(1 / $rate, 1) . "/sec ";
        
        $this->cli->flush($str_left, $str_rght);
    }

    private function showProgressBar(float $progress)
    {
        $str_left = $this->text;
        $str_rght = '';
        
        $msg_leng = $this->cli->getValueStrLength();
        
        $elapsed = $this->elapsedTime();
        $rate = $elapsed / $this->step_count;
        $left = $this->step_size - $this->step_count;
        $eta = round($rate * $left);
        if ($eta < 60 * 60)
            $str_eta = time_toString($eta);
        else
            $str_eta = date("H:i:s", $time_now + $eta);
        
        $str_eta = str_pad($str_eta, 10, ' ');
        
        $str_bar = '';
        $str_prg = time_toString($elapsed);
        $str_prc = str_pad(round($progress * 100, 0) . '%', 5, ' ');
        $len_bar = min(15, $msg_leng - 5 - strlen($str_prg) - strlen($str_prc) - strlen($str_eta));
        if ($len_bar >= 1) {
            $len_prc = floor($len_bar * $progress);
            $str_bar = '[' . str_pad(str_repeat("=", $len_prc), $len_bar, ' ', STR_PAD_RIGHT) . ']';
        }
        
        $str_rght = $str_prc . $str_prg . ' ' . $str_bar . ' ' . $str_eta;
        if ($this->step_count == $this->step_size)
            $str_rght = $str_prg;
        
        $this->cli->flush($str_left, $str_rght);
    }
}

?>