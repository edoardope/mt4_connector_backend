<?php

namespace App\Services;

use App\TradingService\TradingHandler;

class Alpha extends TradingHandler
{
    public function Execute($symbol)
    {

        $this->buy($symbol);

        $candle = $this->getCandleData();

        return "Order placed: $action $volume of $symbol";
    }

}
