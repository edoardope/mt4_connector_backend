<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;

class cmdController extends Controller
{
    public function createCommand($request){
        // Validazione dei dati ricevuti
    $validatedData = $request->validate([
        'istance_key' => 'required|string',
        'cmd_name' => 'required|string',
        'ticket' => 'nullable|integer',
        'side' => 'nullable|integer',
        'magnum' => 'nullable|integer',
        'Automatism_id' => 'nullable|integer',
        'lot' => 'nullable|numeric',
        'tp' => 'nullable|numeric',
        'sl' => 'nullable|numeric',
        'comment' => 'nullable|string',
    ]);

    // Creazione del nuovo record nella tabella command_queues
    DB::table('command_queues')->insert([
        'istance_key' => $validatedData['license_key'],
        'cmd_name' => $validatedData['cmdname'],
        'ticket' => $validatedData['ticket'],
        'side' => $validatedData['side'],
        'magnum' => $validatedData['magnum'],
        'Automatism_id' => $validatedData['Automatism_id'],
        'lot' => $validatedData['lot'],
        'tp' => $validatedData['tp'],
        'sl' => $validatedData['sl'],
        'comment' => $validatedData['comment'],
        'created_at' => Carbon::now('Europe/Rome'),
        'updated_at' => Carbon::now('Europe/Rome'),
    ]);

    // Puoi restituire una risposta o eseguire ulteriori azioni qui
    return response()->json([
        'succes' => true
    ]);

}
}
