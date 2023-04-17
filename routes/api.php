<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

use App\Models\User;
use App\Models\Currency;

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

Route::post('/auth', function(Request $request) {
    try {
        $validator = Validator::make($request->all(), ['email' => 'required|email', 'password' => 'required|min:6']);

        if ($validator->fails()) return response()->json(['status' => 'error', 'message' => 'Bad login or password!', 'errors' => $validator->errors()], 401);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) return response()->json(['status' => 'error', 'message' => 'Authentication error!'], 401);

        return response()->json(['status' => 'success', 'message' => 'Login success!', 'token' => $user->createToken('API TOKEN', [$user->role])->plainTextToken], 200);
    } catch (\Throwable $th) {
        return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
    }
})->name('login');

Route::group(['prefix' => 'currencies'], function () {
    Route::get('/', function() {
        return response()->json(Currency::select(['currency', 'date', 'amount'])->get(), 200);
    });

    Route::get('/{date}', function(string $date) {
        return response()->json(Currency::where('date', $date)->select(['currency', 'date', 'amount'])->get(), 200);
    })->where('date', '^[1-2][0-9]{3}-[0-9]{2}-[0-9]{2}$');

    Route::get('/{date}/{currency}', function(string $date, string $currency) {
        return response()->json(Currency::where(['date' => $date, 'currency' => $currency])->select(['currency', 'date', 'amount'])->get(), 200);
    })->where('date', '^[1-2][0-9]{3}-[0-9]{2}-[0-9]{2}$')->where('currency', '^[A-Z]{3,}$');

    Route::middleware('auth:sanctum')->post('/', function(Request $request) {
        try {
            $validator = Validator::make($request->all(), ['currency' => 'required|regex:/^[A-Z]{3,}$/', 'date' => 'required|regex:/^[1-2][0-9]{3}-[0-9]{2}-[0-9]{2}$/', 'amount' => 'required|regex:/^[0-9]+[.,]*[0-9]*$/']);

            if ($validator->fails()) return response()->json(['status' => 'error', 'message' => 'Bad request data!', 'errors' => $validator->errors()], 400);

            if ($request->user()->tokenCan('api:create')) Currency::create(['currency' => $request->currency, 'amount' => round($request->amount, 2), 'date' => $request->date]);
            else return response()->json(['status' => 'error', 'message' => 'Permission error!'], 401);

            return response()->json(['status' => 'success', 'message' => 'Currency added successfully!'], 201);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    });
});

Route::fallback(function () {
    return response()->json(['status' => 'error', 'message' => 'Not Found!'], 404);
});
