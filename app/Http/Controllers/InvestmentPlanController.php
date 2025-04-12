<?php
namespace App\Http\Controllers;

use App\Http\Resources\InvestmentPlanResource;
use App\Models\InvestmentPlan;

class InvestmentPlanController extends Controller
{
    public function index()
    {
        $plans = InvestmentPlan::where('is_active', true)->get();

        return response()->json([
            'status' => 'success',
            'data'   => InvestmentPlanResource::collection($plans),
        ]);
    }

    public function show(InvestmentPlan $plan)
    {
        if (! $plan->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Investment plan not available',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new InvestmentPlanResource($plan),
        ]);
    }

}
