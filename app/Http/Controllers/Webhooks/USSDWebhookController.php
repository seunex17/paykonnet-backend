<?php

/**
 * Copyright (C) ZubDev Digital Media - All Rights Reserved
 *
 * File: USSDWebhookController.php
 * Author: Zubayr Ganiyu
 *   Email: <seunexseun@gmail.com>
 *   Website: https://zubdev.net
 * Date: 6/24/26
 * Time: 8:37 AM
 */

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\UssdCard;
use App\Services\ClubConnectService;
use Illuminate\Http\Request;

class USSDWebhookController extends Controller
{
    public function handleMobileData(Request $request)
    {
        $sessionId = $request->sessionId;
        $serviceCode = $request->serviceCode;
        $phoneNumber = $request->phoneNumber;
        $text = $request->text;

        header('Content-Type: text/plain');

        if (! empty($text)) {
            $inputs = explode('*', $text);
            $text = end($inputs);
        }

        if (! empty($text)) {
            $card = UssdCard::where('number', $text)->first();

            if ($card) {
                if (! $card->is_valid) {

                    $mtnArray = ['803', '703', '903', '806', '706', '813', '810', '814', '816'];
                    $gloArray = ['805', '705', '905', '807', '815', '811', '906'];
                    $nineMobileArray = ['809', '909', '817', '818'];
                    $airtelArray = ['802', '902', '701', '808', '708', '812'];

                    $networkCode = config('smeplug.airtimeProductArray.'.$card->service);
                    $normalizedPhone = str_replace('+234', '0', $phoneNumber);
                    if (str_starts_with($normalizedPhone, '234')) {
                        $normalizedPhone = '0'.substr($normalizedPhone, 3);
                    }

                    $code = substr($normalizedPhone, 1, 3);

                    $service = null;
                    if (in_array($code, $mtnArray)) {
                        $service = 'mtn';
                    } elseif (in_array($code, $gloArray)) {
                        $service = 'glo';
                    } elseif (in_array($code, $nineMobileArray)) {
                        $service = '9mobile';
                    } elseif (in_array($code, $airtelArray)) {
                        $service = 'airtel';
                    }

                    if ($service && strtolower($service) === strtolower($networkCode)) {
                        try {
                            $response = ClubConnectService::purchaseMobileDataPlans([
                                'product' => $card->service,
                                'code' => $card->code,
                                'phone_no' => $normalizedPhone,
                                'amount' => $card->amount,
                                'references' => 'USSD_'.now()->timestamp,
                            ]);

                            if ($response['error']) {
                                $card->update([
                                    'is_valid' => true,
                                    'validated' => now(),
                                ]);

                                return 'END Recharge Successful';
                            }

                            return 'END Can not Recharge';

                        } catch (\Exception $e) {
                            \Log::error('USSD SmePlug Process Failure Exception: '.$e->getMessage());

                            return 'END Connection timeout. Please try again later.';
                        }
                    }

                    return 'END Invalid topup code';
                }

                return 'END This card has already been used';
            }

            return 'END Card pin is invalid';
        }

        $siteName = config('app.name', 'Paykonet');

        return 'CON Welcome! enter your '.$siteName." unique card pin: \n";
    }
}
