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
use App\Mail\PasswordResetMail;
use App\Mail\VerifyEmailMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\UtilityService;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Random\RandomException;
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

    public function verifyEmail(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'code' => ['required', 'string', 'min:4', 'max:4'],
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => __('email_not_found'),
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $otp = (new Otp)->validate($user->email, $request->code);

        if (! $otp->status) {
            return response()->json([
                'message' => __('invalid_email_otp'),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->markEmailAsVerified();
        $user->is_active = true;
        $user->save();

        Mail::to($user->email)->send(new WelcomeMail($user));

        return response()->json([
            'message' => 'Verification Successful',
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function resendEmailVerifyCode(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'User not found',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $otp = (new Otp)->generate($user->email, 'numeric', 6, 120);
        Mail::to($user->email)->send(new VerifyEmailMail($otp->token));

        return response()->json([
            'message' => 'New code has been send to your email',
            'user' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function lockscreen(Request $request)
    {
        $user = $request->user();
        $pin = Hash::make($request->pin);

        $user->lockscreen = $pin;
        $user->save();

        return response()->json([
            'message' => 'Login pin set successfully',
            'user_data' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    public function verifyLockscreen(Request $request)
    {
        $user = $request->user();

        if (Hash::check($request->pin, $user->lockscreen)) {
            return response()->json([
                'message' => 'Success',
            ], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'message' => 'Invalid login pin',
        ], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first(),
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $input = $request->input();

        if (! Auth::attempt($input)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Your account is not active',
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user_data' => $user,
            'token' => $token,
        ], ResponseAlias::HTTP_OK);
    }

    public function transferPin(Request $request)
    {
        $user = $request->user();
        $pin = Hash::make($request->pin);

        $user->transfer_pin = $pin;
        $user->save();

        return response()->json([
            'message' => 'Transfer pin set successfully',
            'user_data' => $user,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @throws RandomException
     */
    public function forgetPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $newPassword = UtilityService::generateStrongPassword();

        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
            Mail::to($user->email)->send(new PasswordResetMail($newPassword));
        }

        return response()->json([
            'message' => 'New password sent to your email',
        ], ResponseAlias::HTTP_OK);
    }

    public function loggedUser(Request $request)
    {
        return response()->json($request->user(), ResponseAlias::HTTP_OK);
    }
}
