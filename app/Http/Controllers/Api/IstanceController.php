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

public function market(Request $request){

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

     $open_postion = $data['open_postion'] ?? null;

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

        // Creare una nuova entità simble_datas
        if ($symbol_data) {
            DB::table('simble_datas')->insert([
                'istance_key' => $license_key,
                'simble_name' => $symbol_data['simble_name'] ?? null,
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

        if ($account_data) {
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
        }

        if ($open_positions) {


            foreach ($open_positions as $position) {
                // Verifica se esiste già un record con istance_key e ticket
                $existingRecord = DB::table('open_positions')
                    ->where('istance_key', $license_key)
                    ->where('ticket', $position->ticket)
                    ->first();
        
                if ($existingRecord) {
                    // Aggiorna il record esistente
                    DB::table('open_positions')
                        ->where('id', $existingRecord->id)
                        ->update([
                            'pair' => $position->pair,
                            'profit' => $position->profit,
                            'open_price' => $position->open_price,
                            'take_profit' => $position->take_profit,
                            'stop_loss' => $position->stop_loss,
                            'side' => $position->side,
                            'lot_size' => $position->lot_size,
                            'magic_number' => $position->magic_number,
                            'comment' => $position->comment,
                            'ispending_order' => $position->ipending_order,
                            'updated_at' => Carbon::now('Europe/Rome'),
                        ]);
                } else {
                    // Crea un nuovo record
                    DB::table('open_positions')->insert([
                        'istance_key' => $position->istance_key,
                        'license_key' => $license_key,
                        'ticket' => $position->ticket,
                        'pair' => $position->pair,
                        'profit' => $position->profit,
                        'open_price' => $position->open_price,
                        'take_profit' => $position->take_profit,
                        'stop_loss' => $position->stop_loss,
                        'side' => $position->side,
                        'lot_size' => $position->lot_size,
                        'magic_number' => $position->magic_number,
                        'comment' => $position->comment,
                        'ispending_order' => $position->ipending_order,
                        'created_at' => Carbon::now('Europe/Rome'),
                        'updated_at' => Carbon::now('Europe/Rome'),
                    ]);
                }
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Operation completed successfully.'
        ]);
    }

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
}
