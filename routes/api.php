<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;

Route::post('/actualizar-token-fcm', function (Request $request) {
    $request->validate([
        'user_id' => 'required',
        'fcm_token' => 'required'
    ]);

    User::where('id', $request->user_id)->update([
        'fcm_token' => $request->fcm_token
    ]);

    return response()->json(['status' => 'success']);
});