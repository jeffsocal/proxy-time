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

    private $fractionText;

    private $fractionStep;

    private $fractionStepUp;

    private $fractionStepSize;

    private $showFractionTime;

    private $fractionStartUnixTime;

    private $secTotal;

    private $textPadLength;

    function __construct($pad_length = 30)
    {
        parent::__construct();
        $this->fractionStep = 0;
        $this->fractionStepUp = 0.001;
        $this->fractionStepSize = 10;
        $this->showFractionTime = false;
        $this->showMemory = false;
        $this->textPadLength = $pad_length;
    }

    public function fractionalTimerStart($text = "fraction complete\n")
    {
        $this->fractionText = $text;
        $this->fractionStepCount = 0;
        $this->fractionStep = 0;
        $this->start();
        $this->fractionStartUnixTime = date('U');
        $this->secTotal = array();
    }

    public function fractionalTimerSize($size = 10)
    {
        $this->fractionStepSize = $size;
    }

    public function fractionalTimerPercentStep($size = 10)
    {
        $this->fractionStepUp = $size / 100;
        $this->fractionStep = $this->fractionStepUp;
    }

    public function fractionalTimerShowTime($boolean = false)
    {
        $this->showFractionTime = $boolean;
    }

    public function fractionalTimerShowMemory($boolean = false)
    {
        $this->showMemory = $boolean;
    }

    public function fractionalTimerPrint()
    {
        $this->fractionStepCount ++;
        $fraction = $this->fractionStepCount / $this->fractionStepSize;
        if ($fraction == 1 or $fraction >= $this->fractionStep) {
            $this->showProgressBar($fraction);
            $this->fractionStep += $this->fractionStepUp;
        }
    }

    private function showProgressBar()
    {
        $barSize = $this->textPadLength - strlen($this->fractionText) - 1;
        
        // if we go over our bound, just ignore it
        if ($this->fractionStepCount > $this->fractionStepSize)
            return;
        
        $time_start = $this->fractionStartUnixTime;
        $time_now = date('U');
        
        $fracDone = ($this->fractionStepCount / $this->fractionStepSize);
        
        $barLength = floor($fracDone * $barSize);
        
        $status_bar = "\r$this->fractionText ";
        $status_bar .= str_repeat(".", $barLength);
        if ($barLength < $barSize) {
            $status_bar .= str_repeat(" ", ($barSize - $barLength));
        }
        
        $dispFracDone = round($fracDone * 100, 0);
        
        $elapsed = $time_now - $time_start;
        $rate = $elapsed / $this->fractionStepCount;
        $left = $this->fractionStepSize - $this->fractionStepCount;
        $eta = round($rate * $left);
        
        if ($this->fractionStepCount == $this->fractionStepSize) {
            $status_bar .= " " . time_toString($elapsed);
        } elseif ($eta < 60 * 60) {
            $status_bar .= " $dispFracDone% ";
            $status_bar .= " elapsed: " . time_toString($elapsed) . " remaining: " . time_toString($eta);
        } else {
            $status_bar .= " $dispFracDone% ";
            $eta = date("H:i:s", $time_now + $eta);
            $status_bar .= " finished at: $eta elapsed: " . time_toString($elapsed);
        }
        
        echo str_pad($status_bar, $this->textPadLength + 50, " ", STR_PAD_RIGHT);
        
        flush();
        
        // when done, send a newline
        if ($this->fractionStepCount == $this->fractionStepSize) {
            echo "\n";
        }
    }
}

?>