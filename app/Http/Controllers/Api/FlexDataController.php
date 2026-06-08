<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class FlexDataController extends Controller
{
    public function listFlexDataPlans(Request $request)
    {
        // TODO: Implement listFlexDataPlans
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }

    public function purchaseFlexDataPlan(Request $request)
    {
        // TODO: Implement purchaseFlexDataPlan
        return response()->json(['message' => 'Method not implemented'], ResponseAlias::HTTP_NOT_IMPLEMENTED);
    }
}
