<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use App\Models\Currency;

use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/auth', function(Request $request) {
  return response()->json(['status' => 'error', 'message' => 'Authentication requred!'], 401);
});

Route::post('/auth', [ApiController::class, 'auth'])->name('login');

Route::group(['prefix' => 'currencies'], function () {
    Route::middleware('auth:sanctum')->get('/', function() {
        return response()->json(Currency::select(['currency', 'date', 'amount'])->get(), 200);
    });

    Route::middleware('auth:sanctum')->get('/{date}', function(string $date) {
        return response()->json(Currency::where('date', $date)->select(['currency', 'date', 'amount'])->get(), 200);
    })->where('date', '^[1-2][0-9]{3}-[0-9]{2}-[0-9]{2}$');

    Route::middleware('auth:sanctum')->get('/{date}/{currency}', function(string $date, string $currency) {
        return response()->json(Currency::where(['date' => $date, 'currency' => $currency])->select(['currency', 'date', 'amount'])->get(), 200);
    })->where('date', '^[1-2][0-9]{3}-[0-9]{2}-[0-9]{2}$')->where('currency', '^[A-Z]{3,}$');

    Route::middleware('auth:sanctum')->post('/', [ApiController::class, 'add']);
});

Route::fallback(function () {
    return response()->json(['status' => 'error', 'message' => 'Not Found!'], 404);
});
