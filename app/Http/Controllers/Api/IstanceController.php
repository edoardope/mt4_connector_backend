<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;

class IstanceController extends Controller
{
    public function index(){
        $istances = DB::table('istances')->get();

        return response()->json([
            'succes' => true,
            'istance' => $istances
        ]);
    } 

    public function searchIstance($id)
    {
        $result = DB::table('istances')->where('id', $id)->first();

        return response()->json([
            'id' => $id,
            'result' => $result,
        ]);
    }

    public function createIstance($istance_name)
{
    // Tentativo di generare una chiave unica
    try {
        // Genera una chiave casuale di 60 caratteri
        $license_key = $this->generateRandomKey(60);

        // Verifica l'univocità della chiave di licenza
        while ($this->isLicenseKeyExists($license_key)) {
            $license_key = $this->generateRandomKey(60);
        }

        // Inserisci il nuovo record nella tabella 'istances'
        DB::table('istances')->insert([
            'license_name' => $istance_name,
            'license_key' => $license_key,
            'status' => false,
        ]);

        // Ritorna una risposta JSON di successo
        return response()->json([
            'success' => true,
            'message' => 'Instance created successfully',
        ]);
    } catch (\Exception $e) {
        // Gestione dell'errore
        return response()->json([
            'success' => false,
            'message' => 'Failed to create instance',
            'error' => $e->getMessage(),
        ], 500); // Codice di stato HTTP 500 per errore interno del server
    }
}
     
public function status(Request $request)
{
    // Leggi il contenuto della richiesta come stringa JSON
    $jsonString = $request->getContent();

    // Log the incoming request
    Log::info('Received status request:', ['request' => $jsonString]);

    // Remove control characters from the JSON string
    $jsonStringCleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonString);

    // Decodifica la stringa JSON in un array associativo
    $data = json_decode($jsonStringCleaned, true);

    // Check if json_decode was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error('JSON decode error: ' . json_last_error_msg());

        return response()->json([
            'success' => false,
            'message' => 'Invalid JSON format.'
        ], 400);
    }

    // Log the decoded data
    Log::info('Decoded JSON data:', ['data' => $data]);

    // Estrai il license_key e la version dall'array associativo
    $license_key = $data['license_key'] ?? null;
    $version = $data['version'] ?? null;

    // Controlla se license_key è presente
    if (!$license_key) {
        Log::warning('License key is missing in the request.');

        return response()->json([
            'success' => false,
            'message' => 'License key is required.'
        ], 400);
    }

    // Cerca il record con license_key uguale a $license_key
    $istance = DB::table('istances')->where('license_key', $license_key)->first();

    // Controlla se il record esiste
    if ($istance) {
        // Aggiorna la colonna status a true e last_contact con la data attuale
        DB::table('istances')->where('license_key', $license_key)->update([
            'status' => true,
            'last_contact' => Carbon::now('Europe/Rome'), // Usa Carbon per ottenere la data attuale
            'version' => $version
        ]);

        Log::info('Status and last_contact updated successfully for license key: ' . $license_key);

        return response()->json([
            'success' => true,
            'message' => 'Status and last_contact updated successfully.',
            'istance' => $istance
        ]);
    } else {
        Log::error('Istance not found for license key: ' . $license_key);

        // Se il record non esiste, ritorna un messaggio di errore
        return response()->json([
            'success' => false,
            'message' => 'Istance not found.'
        ], 404);
    }
}

public function market(Request $request)
{
    // Leggi il contenuto della richiesta come stringa JSON
    $jsonString = $request->getContent();

    // Log the incoming request
    Log::info('Received market request:', ['request' => $jsonString]);

    // Remove control characters from the JSON string
    $jsonStringCleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonString);

    // Decodifica la stringa JSON in un array associativo
    $data = json_decode($jsonStringCleaned, true);

    // Check if json_decode was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error('JSON decode error: ' . json_last_error_msg());

        return response()->json([
            'success' => false,
            'message' => 'Invalid JSON format.'
        ], 400);
    }

    // Log the decoded data
    Log::info('Decoded JSON data:', ['data' => $data]);

    // Estrai il license_key e la version dall'array associativo
    $license_key = $data['license_key'] ?? null;
    $symbol_data = $data['symbol_data'] ?? null;
    $account_data = $data['account_data'] ?? null;
    $open_position = $data['open_position'] ?? null;

    // Controlla se license_key è presente
    if (!$license_key) {
        Log::warning('License key is missing in the request.');

        return response()->json([
            'success' => false,
            'message' => 'License key is required.'
        ], 400);
    }

    $this->pac(1, $license_key, 1);

    // Cerca il record con license_key uguale a $license_key
    $istance = DB::table('istances')->where('license_key', $license_key)->first();

    // Controlla se il record esiste
    if ($istance) {
        // Creare una nuova entità simble_datas
        if ($symbol_data) {
            DB::table('simble_datas')->insert([
                'istance_key' => $license_key,
                'simble_name' => $symbol_data['symbol_name'] ?? null,
                'current_ask' => $symbol_data['current_ask'] ?? null,
                'current_bid' => $symbol_data['current_bid'] ?? null,
                'current_spread' => $symbol_data['current_spread'] ?? null,
                'trading_is_active' => $symbol_data['trading_is_active'] ?? null,
                'time_frame' => $symbol_data['time_frame'] ?? null,
                'open' => $symbol_data['open'] ?? null,
                'current_high' => $symbol_data['current_high'] ?? null,
                'current_low' => $symbol_data['current_low'] ?? null,
                'past_candle_json' => $symbol_data['past_candle_json'] ?? null,
                'created_at' => Carbon::now('Europe/Rome')
            ]);

            // Log the insertion
            Log::info('Symbol data inserted successfully:', ['license_key' => $license_key]);
        } else {
            Log::warning('Symbol data is missing in the request:', ['license_key' => $license_key]);
        }

        Log::info('checking for account data:', ['license_key' => $license_key]);

        if ($account_data) {
            Log::info('found account data:', ['license_key' => $license_key]);
            DB::table('account_datas')->insert([
                'istance_key' => $license_key,
                'profit' => $account_data['profit'],
                'balance' => $account_data['balance'],
                'account_number' => $account_data['account_number'],
                'broker_name' => $account_data['broker_name'],
                'account_name' => $account_data['account_name'],
                'created_at' => Carbon::now('Europe/Rome'),
                'updated_at' => Carbon::now('Europe/Rome'),
            ]);
        } else {
            Log::info('not found account data:', ['license_key' => $license_key]);
        }

        if ($open_position) {
            Log::info('found open_positions:', ['license_key' => $license_key]);

            foreach ($open_position as $position) {
                // Verifica se esiste già un record con istance_key e ticket
                $existingRecord = DB::table('istance_open_positions')
                    ->where('istance_key', $license_key)
                    ->where('ticket', $position['ticket']) // Nota: usiamo ['ticket'] invece di ->ticket
                    ->first();

                if ($existingRecord) {
                    // Aggiorna il record esistente
                    DB::table('istance_open_positions')
                        ->where('id', $existingRecord->id)
                        ->update([
                            'pair' => $position['pair'],
                            'profit' => $position['profit'],
                            'open_price' => $position['open_price'],
                            'take_profit' => $position['take_profit'],
                            'stop_loss' => $position['stop_loss'],
                            'side' => $position['side'],
                            'lot_size' => $position['lotsize'],
                            'magic_number' => $position['magic_number'],
                            'comment' => $position['comment'],
                            'pending_order' => $position['pending_order'],
                            'updated_at' => Carbon::now('Europe/Rome'),
                        ]);
                } else {
                    // Crea un nuovo record
                    DB::table('istance_open_positions')->insert([
                        'istance_key' => $license_key,
                        'ticket' => $position['ticket'],
                        'pair' => $position['pair'],
                        'profit' => $position['profit'],
                        'open_price' => $position['open_price'],
                        'take_profit' => $position['take_profit'],
                        'stop_loss' => $position['stop_loss'],
                        'side' => $position['side'],
                        'lot_size' => $position['lotsize'],
                        'magic_number' => $position['magic_number'],
                        'comment' => $position['comment'],
                        'pending_order' => $position['pending_order'],
                        'created_at' => Carbon::now('Europe/Rome'),
                        'updated_at' => Carbon::now('Europe/Rome'),
                    ]);
                }
            }
        } else {
            Log::info('not found open_positions:', ['license_key' => $license_key]);
        }

        Log::info('searching for commands:', ['license_key' => $license_key]);

        $command = DB::table('command_queues')->where('istance_key', $license_key)->first();

        if ($command) {
            // Esegui la cancellazione dopo aver ottenuto l'oggetto
            DB::table('command_queues')->where('id', $command->id)->delete();
    
            Log::info('command found', ['istance_key' => $istance_key]);
    
            if ($command->cmd_name == 'open') {
                return response()->json([
                    'success' => true,
                    'cmdname' => $command->cmd_name,
                    'side' => $command->side,
                    'lot' => $command->lot,
                    'tp' => $command->tp,
                    'sl' => $command->sl,
                    'magnum' => $command->magnum,
                    'comment' => $command->comment,
                ]);
            } else if ($command->cmd_name == 'close') {
                return response()->json([
                    'success' => true,
                    'cmdname' => $command->cmd_name,
                    'ticket' => $command->ticket,
                    'lot' => $command->lot
                ]);
            } else if ($command->cmd_name == 'modify') {
                return response()->json([
                    'success' => true,
                    'cmdname' => $command->cmd_name,
                    'ticket' => $command->ticket,
                    'price' => $command->price,
                    'tp' => $command->tp,
                    'sl' => $command->sl
                ]);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Operation completed successfully.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Operation completed successfully.'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Istance not found.'
    ], 404);
}

public function history(Request $request)
{
    // Leggi il contenuto della richiesta come stringa JSON
    $jsonString = $request->getContent();

    // Log the incoming request
    Log::info('Received market request:', ['request' => $jsonString]);

    // Remove control characters from the JSON string
    $jsonStringCleaned = preg_replace('/[\x00-\x1F\x7F]/u', '', $jsonString);

    // Decodifica la stringa JSON in un array associativo
    $data = json_decode($jsonStringCleaned, true);

    // Check if json_decode was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        Log::error('JSON decode error: ' . json_last_error_msg());

        return response()->json([
            'success' => false,
            'message' => 'Invalid JSON format.'
        ], 400);
    }

    // Log the decoded data
    Log::info('Decoded JSON data:', ['data' => $data]);

    // Estrai il license_key e la version dall'array associativo
    $license_key = $data['license_key'] ?? null;
    $istance_closed_order = $data['istance_closed_order'] ?? null;

    // Cerca il record con license_key uguale a $license_key nella tabella istance_open_positions
    $instance = DB::table('istance_open_positions')
        ->where('istance_key', $license_key)
        ->where('ticket', $istance_closed_order['ticket']) // Aggiunto il check sulla colonna ticket
        ->first();

    // Controlla se il record esiste
    if ($instance) {
        // Prepara i dati per inserire nella tabella istance_closed_positions
        $insertData = [
            'istance_key' => $license_key,
            'ticket' => $istance_closed_order["ticket"],
            'pair' => $istance_closed_order["pair"],
            'profit' => $istance_closed_order["profit"],
            'open_price' => $istance_closed_order["open_price"],
            'take_profit' => $istance_closed_order["take_profit"],
            'stop_loss' => $istance_closed_order["stop_loss"],
            'side' => $istance_closed_order["side"],
            'lot_size' => $istance_closed_order["lotsize"],
            'magic_number' => $istance_closed_order["magic_number"],
            'comment' => $istance_closed_order["comment"],
            'created_at' => Carbon::now('Europe/Rome'),
            'updated_at' => Carbon::now('Europe/Rome')
        ];

        // Insert into istance_closed_positions table
        DB::table('istance_closed_postions')->insert($insertData);

        // Delete the record from istance_open_positions
        DB::table('istance_open_positions')
        ->where('istance_key', $license_key)
        ->where('ticket', $istance_closed_order['ticket']) // Aggiunto il check sulla colonna ticket
        ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Operation completed successfully.'
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Instance not found in open positions.'
    ], 404);
}


/**
 * Genera una stringa casuale di lunghezza specificata.
 *
 * @param int $length Lunghezza della stringa casuale da generare
 * @return string Stringa casuale generata
 */
private function generateRandomKey($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!?@';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

/**
 * Verifica se una chiave di licenza esiste già nella tabella 'istances'.
 *
 * @param string $license_key Chiave di licenza da verificare
 * @return bool true se la chiave di licenza esiste già, false altrimenti
 */
private function isLicenseKeyExists($license_key)
{
    return DB::table('istances')->where('license_key', $license_key)->exists();
}


private function pac($timeframe, $istance_key, $magnum){

    $time = Carbon::now()->subMinutes(4);

    $candles = DB::table('simble_datas')->where('created_at', '>=', $time)->get();

    // Assicurati di avere almeno due record per fare il confronto
    if ($candles->count() < 2) {
        return false; // O qualsiasi altro valore o azione che desideri intraprendere
    }

    // Ottieni il primo e l'ultimo record
    $firstCandle = $candles->first();
    $lastCandle = $candles->last();

    // Calcola la differenza di tempo in minuti tra il primo e l'ultimo record
    $firstTime = Carbon::parse($firstCandle->created_at);
    $lastTime = Carbon::parse($lastCandle->created_at);
    $differenceInMinutes = $firstTime->diffInMinutes($lastTime);

    // Verifica se la differenza di tempo è di almeno 3 minuti
    if ($differenceInMinutes >= 3) {
        // Sono passati almeno 3 minuti
        DB::table('command_queues')->insert([
            'istance_key' => $istance_key,
            'cmd_name' => "open",
            'side' => 0,
            'lot' => 1,
            'tp' => 3500,
            'sl' => 3200,
            'comment' => 'pac',
            'magnum' => $magnum
        ]);
    } else {
        // Non sono passati almeno 3 minuti
        return false; // O qualsiasi altra azione che desideri intraprendere
    }
}
}
