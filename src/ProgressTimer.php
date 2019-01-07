<?php

/*
 * Written by Jeff Jones (jeff@socalbioinformatics.com)
 * Copyright (2016) SoCal Bioinformatics Inc.
 *
 * See LICENSE.txt for the license.
 */
namespace ProxyTime;

class ProgressTimer extends Timer
{

    private $showMemory;

    private $progressText;

    private $progressStep;

    private $progressStepUp;

    private $progressStepSize;

    private $showprogressTime;

    private $progressStartUnixTime;

    private $secTotal;

    private $textPadLength;

    function __construct($pad_length = 30)
    {
        parent::__construct();
        $this->progressStep = 0;
        $this->progressStepUp = 0.001;
        $this->progressStepSize = 10;
        $this->showprogressTime = false;
        $this->showMemory = false;
        $this->textPadLength = $pad_length;
    }

    public function start($text = "progress ", $size = 10)
    {
        $this->progressText = $text;
        $this->progressStepSize = $size;
        $this->progressStepCount = 0;
        $this->progressStep = 0;
        $this->progressStartUnixTime = date('U');
        $this->secTotal = array();
    }

    public function percentStep($size = 10)
    {
        $this->progressStepUp = $size / 100;
        $this->progressStep = $this->progressStepUp;
    }

    public function showTime($boolean = false)
    {
        $this->showprogressTime = $boolean;
    }

    public function showMemory($boolean = false)
    {
        $this->showMemory = $boolean;
    }

    public function barPercent()
    {
        $this->progressStepCount ++;
        $progress = $this->progressStepCount / $this->progressStepSize;
        if ($progress == 1 or $progress >= $this->progressStep) {
            $this->showProgressBar();
            $this->progressStep += $this->progressStepUp;
        }
    }

    public function barCount($chunk = 1000, $subn = FALSE)
    {
        $this->progressStepCount ++;
        if ($this->progressStepCount % $chunk == 0)
            $this->showCountBar($subn);
    }

    private function showCountBar($subn = FALSE)
    {
        $barSize = $this->textPadLength - strlen($this->progressText) - 1;
        
        $time_start = $this->progressStartUnixTime;
        $time_now = date('U');
        
        $status_bar = "\r$this->progressText ";
        $status_bar .= str_repeat(".", $barLength);
        if ($barLength < $barSize) {
            $status_bar .= str_repeat(".", ($barSize - $barLength));
        }
        
        $elapsed = $time_now - $time_start;
        $rate = $elapsed / $this->progressStepCount;
        
        $status_bar .= " time:" . time_toString($elapsed);
        $status_bar .= " n:";
        if (! is_false($subn))
            $status_bar .= $subn . '/';
        
        $status_bar .= $this->progressStepCount;
        
        if ($rate > 0)
            $status_bar .= " rate:" . round(1 / $rate, 1) . "/sec ";
        
        echo str_pad($status_bar, $this->textPadLength + 50, " ", STR_PAD_RIGHT);
        
        flush();
    }

    private function showProgressBar()
    {
        $barSize = $this->textPadLength - strlen($this->progressText) - 1;
        
        // if we go over our bound, just ignore it
        if ($this->progressStepCount > $this->progressStepSize)
            return;
        
        $time_start = $this->progressStartUnixTime;
        $time_now = date('U');
        
        $progDone = ($this->progressStepCount / $this->progressStepSize);
        
        $barLength = floor($progDone * $barSize);
        
        $status_bar = "\r$this->progressText ";
        $status_bar .= str_repeat(".", $barLength);
        if ($barLength < $barSize) {
            $status_bar .= str_repeat(" ", ($barSize - $barLength));
        }
        
        $dispProgDone = round($progDone * 100, 0);
        
        $elapsed = $time_now - $time_start;
        $rate = $elapsed / $this->progressStepCount;
        $left = $this->progressStepSize - $this->progressStepCount;
        $eta = round($rate * $left);
        
        if ($this->progressStepCount == $this->progressStepSize) {
            $status_bar .= " " . time_toString($elapsed);
        } elseif ($eta < 60 * 60) {
            $status_bar .= " $dispProgDone% ";
            $status_bar .= " elapsed: " . time_toString($elapsed) . " remaining: " . time_toString($eta);
        } else {
            $status_bar .= " $dispProgDone% ";
            $eta = date("H:i:s", $time_now + $eta);
            $status_bar .= " finished at: $eta elapsed: " . time_toString($elapsed);
        }
        
        echo str_pad($status_bar, $this->textPadLength + 50, " ", STR_PAD_RIGHT);
        
        flush();
        
        // when done, send a newline
        if ($this->progressStepCount == $this->progressStepSize) {
            echo "\n";
        }
    }
}

?>