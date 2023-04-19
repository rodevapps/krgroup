<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

use App\Models\User;
use App\Models\Currency;

class ApiController extends Controller
{
    public function auth(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), ['email' => 'required|email', 'password' => 'required|min:6']);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Bad login or password!', 'errors' => $validator->errors()], 401);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['status' => 'error', 'message' => 'Authentication error!'], 401);
            }

            return response()->json(['status' => 'success', 'message' => 'Login success!', 'token' => $user->createToken('API TOKEN', [$user->role])->plainTextToken], 200);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }

    public function add(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), ['currency' => 'required|regex:/^[A-Z]{3,}$/', 'date' => 'required|regex:/^[1-2][0-9]{3}-[0-9]{2}-[0-9]{2}$/', 'amount' => 'required|regex:/^[0-9]+[.,]*[0-9]*$/']);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => 'Bad request data!', 'errors' => $validator->errors()], 400);
            }

            if ($request->user()->tokenCan('api:create')) {
                Currency::create(['currency' => $request->currency, 'amount' => round($request->amount, 2), 'date' => $request->date]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Permission error!'], 401);
            }

            return response()->json(['status' => 'success', 'message' => 'Currency added successfully!'], 201);
        } catch (\Throwable $th) {
            return response()->json(['status' => 'error', 'message' => $th->getMessage()], 500);
        }
    }
}
