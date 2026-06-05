<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: AuthController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/5/26
 * Time: 6:22 PM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMail;
use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
    /**
     * @throws \Exception
     */
    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $input = $request->all();
        $input['name'] = $input['firstname'].' '.$input['lastname'];
        $input['uuid'] = Str::uuid()->toString();

        $user = User::create($input);

        $otp = (new Otp)->generate($user->email, 'numeric', 4, 120);
        Mail::to($user->email)->send(new VerifyEmailMail($otp->token));

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user_data' => $user,
        ], ResponseAlias::HTTP_OK);
    }
}
