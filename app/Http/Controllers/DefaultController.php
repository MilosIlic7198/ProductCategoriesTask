<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DefaultController extends Controller
{
    //
    public function getDefaultResponse(Request $request) {
        $response = [
            'success' => true,
            'message' => 'This is default reponse.',
            'payload' => null,
        ];
        return response()->json($response);
    }
}
