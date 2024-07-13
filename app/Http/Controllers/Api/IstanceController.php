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
