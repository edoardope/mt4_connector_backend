<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\TradingService\Alpha;

class TradingSimulationSeeder extends Seeder
{
    // Array globale per tracciare le posizioni aperte durante la simulazione
    protected $openPositions = [];

    // Array globale per tracciare le posizioni chiuse che verranno utilizzate per generare il report finale
    protected $closedPositions = [];

    protected $past_candle_json_raw = [];

    // Array globale per tracciare i comandi generati dalla strategia durante la simulazione
    protected $tradeCommands = [];

    //starting attribute
    protected $balance = 1000;
    protected $license_key = "";
    protected $max_past_candle = 200;
    protected $tested_symble = "";
    protected $current_spread = 0; //in point
    protected $time_frame = "";
    protected $test_strategy = "";

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Percorso del file CSV
        $csvFile = storage_path('app/data/EURUSD_GMT+1_EU-DST_M5 (1).csv');

        // Apertura del file CSV
        if (($handle = fopen($csvFile, 'r')) !== false) {
            // Salta la prima riga (intestazione)
            fgetcsv($handle, 1000, ',');

            // Cicla attraverso ogni riga del CSV
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Estrai i dati della candela
                $candle = [
                    'date' => $data[0],
                    'time' => $data[1],
                    'open' => (float) $data[2],
                    'high' => (float) $data[3],
                    'low' => (float) $data[4],
                    'close' => (float) $data[5],
                    'volume' => (int) $data[6],
                ];

                // Aggiungi la candela corrente a $past_candle_json_raw
                array_unshift($this->past_candle_json_raw, $candle);

                // Limita il numero di candele nell'array
                if (count($this->past_candle_json_raw) > $this->max_past_candle) {
                    array_pop($this->past_candle_json_raw); // Rimuove la candela più vecchia
                }

                // Converte $past_candle_json_raw in una stringa JSON formattata
                $pastCandleJsonString = json_encode($this->past_candle_json_raw);

                // Inserisci i dati nel database utilizzando i soli dati di $candle
                DB::table('simble_datas')->insert([
                    'istance_key' => $this->license_key,
                    'simble_name' => $this->tested_symble,
                    'current_ask' => $candle["close"],
                    'current_bid' => $candle["close"],
                    'current_spread' => $this->current_spread,
                    'trading_is_active' => 1,
                    'time_frame' => $this->time_frame,
                    'open' => $candle['open'],
                    'current_high' => $candle['high'],
                    'current_low' => $candle['low'],
                    'past_candle_json' => $pastCandleJsonString,
                    'first' => 1,
                    'created_at' => Carbon::now('Europe/Rome')
                ]);

                // Itera su tutte le posizioni aperte per aggiornare il profitto
                foreach ($this->openPositions as &$position) {
                    // Calcola il profitto basato sui dati più recenti della candela
                    $profit = $this->calculateProfit(
                        $position['side'],
                        $position['lot_size'],
                        $position['open_price'],
                        $candle['close']
                    );

                    // Aggiorna il profitto nella posizione
                    $position['profit'] = $profit;

                    // Aggiorna la posizione nel database
                    DB::table('istance_open_positions')
                        ->where('ticket', $position['ticket'])
                        ->update(['profit' => $profit]);
                }

                // Esegui la logica della strategia per ogni candela
                if ($this->test_strategy == "alpha") {
                    $strategy = new Alpha();
                    $strategy->execute($this->tested_symble, $this->license_key, $this->time_frame);
                }

                // esegui eventuali comandi 
                $command = DB::table('command_queues')->where('istance_key', $this->license_key)->first();

                if ($command) {
                    // Esegui la cancellazione dopo aver ottenuto l'oggetto
                    DB::table('command_queues')->where('id', $command->id)->delete();

                    if ($command["open"]) {
                        $randomTicket = mt_rand(10000000, 99999999);

                        $position = [
                            'istance_key' => $this->license_key,
                            'ticket' => $randomTicket,
                            'pair' => $this->tested_symble,
                            'profit' => 0,
                            'open_price' => $candle["close"],
                            'take_profit' => $command["tp"],
                            'stop_loss' => $command["sl"],
                            'side' => $command["side"],
                            'lot_size' => $command["lot"],
                            'magic_number' => $command["magnum"],
                            'pending_order' => 0,
                            'comment' => $command["comment"],
                        ];

                        // Inserisce la posizione aperta nell'array $openPositions
                        $this->openPositions[] = $position;

                        // Inserisce la posizione aperta nel database
                        DB::table('istance_open_positions')->insert($position);

                    } else if ($command["close"]) {
                        // Gestione della chiusura della posizione

                    } else if ($command["modify"]) {
                        // Gestione della modifica della posizione

                    }

                    Log::info('command found', ['istance_key' => $this->license_key]);
                }


            }

            fclose($handle);
        }
    }

    /**
     * Calcola il profitto basato sui dati più recenti della candela
     */
    protected function calculateProfit($side, $lotSize, $openPrice, $currentPrice)
    {
        // Se la posizione è long (buy), il profitto è calcolato come (currentPrice - openPrice) * lotSize
        // Se la posizione è short (sell), il profitto è calcolato come (openPrice - currentPrice) * lotSize
        if ($side == 0) { // Buy
            return ($currentPrice - $openPrice) * $lotSize * 100000;
        } else { // Sell
            return ($openPrice - $currentPrice) * $lotSize * 100000;
        }
    }

    // Puoi aggiungere metodi qui per gestire la logica di apertura e chiusura delle posizioni,
    // l'elaborazione dei comandi e la generazione del report finale.
}
