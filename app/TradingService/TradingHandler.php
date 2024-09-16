<?php

namespace App\Services;


use Illuminate\Support\Facades\DB;

class TradingHandler
{
    public function placeOrder($istance_key, $lot, $side, $tp, $sl, $comment, $magnum)
    {
        DB::table('command_queues')->insert([
            'istance_key' => $istance_key,
            'cmd_name' => 'open',
            'side' => $side,
            'lot' => $lot,
            'tp' => $tp,
            'sl' => $sl,
            'comment' => $comment,
            'magnum' => $magnum,
            'created_at' => Carbon::now('Europe/Rome')
        ]);

    }

    public function closeOrder($istance_key, $ticket, $lot)
    {
        DB::table('command_queues')->insert([
            'istance_key' => $istance_key,
            'cmd_name' => 'close',
            'ticket' => $ticket,
            'lot' => $lot,
            'created_at' => Carbon::now('Europe/Rome')
        ]);
    }

    public function modifyOrder($istance_key, $ticket, $tp, $sl){

        DB::table('command_queues')->insert([
            'istance_key' => $istance_key,
            'cmd_name' => 'modify',
            'ticket' => $ticket,
            'tp' => $tp,
            'sl' => $sl,
            'created_at' => Carbon::now('Europe/Rome')
        ]);

    }

    public function WaveTrendLB($istance_key, $symbol, $Clenght, $Alenght, $ObLevel1, $ObLevel2, $OsLevel1, $OsLevel2){

        // Recupera la data o il timestamp della candela con `first` a `true`
        $firstCandle = DB::table('simble_datas')
        ->where('simble_name', $symble)
        ->where('istance_key', $istance_key)
        ->where('first', true)
        ->orderBy('created_at', 'desc') // Ordina per data, più recente prima
        ->first(['created_at']); // Recupera solo la colonna `created_at`
        
        if ($firstCandle) {
           // Usa la data della candela con `first` a `true` per trovare la candela successiva
            $nextCandle = DB::table('simble_datas')
                ->where('simble_name', $symble)
                ->where('istance_key', $istance_key)
                ->where('first', false)
                ->where('created_at', '>', $firstCandle->created_at) // Trova la candela successiva
                ->orderBy('created_at', 'asc') // Ordina per data, più recente prima
                ->first();
            
            if ($nextCandle) {
            
                $avaragePrice = ($lastCandle->current_high + $lastCandle->current_low + $nextCandle->open) / 3;

                
            }
        }
    }
}
