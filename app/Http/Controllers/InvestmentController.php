<?php

// Implementation in InvestmentController.php
namespace App\Http\Controllers;

use App\Http\Resources\InvestmentResource;
use App\Models\Investment;
use App\Models\InvestmentPlan;
use App\Models\Transaction;
use App\Notifications\InvestmentWithdrawn;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvestmentController extends Controller
{
    /**
     * Display a listing of user's investments
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = $request->user()->investments();

        if ($status) {
            $query->where('status', $status);
        }

        $investments = $query->with('investmentPlan')
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data'   => InvestmentResource::collection($investments),
        ]);
    }

    /**
     * Display the specified investment
     *
     * @param  Request  $request
     * @param  Investment  $investment
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Investment $investment)
    {
        // Check if investment belongs to authenticated user
        if ($investment->user_id !== $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized access to investment',
            ], 403);
        }

        // Force recalculation of current value
        $investment->updateCurrentValue();

        return response()->json([
            'status' => 'success',
            'data'   => InvestmentResource::make($investment->load('investmentPlan')),
        ]);
    }

    /**
     * Create a new investment for the authenticated user
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'investment_plan_id' => 'required|exists:investment_plans,id',
            'amount'             => 'required|numeric|min:1',
        ]);

        $user = $request->user();
        $plan = InvestmentPlan::findOrFail($request->investment_plan_id);

        // Check if plan is active
        if (! $plan->is_active) {
            return response()->json([
                'status'  => 'error',
                'message' => 'The selected investment plan is not available',
            ], 400);
        }

        // Check if amount meets minimum investment
        if ($request->amount < $plan->minimum_investment) {
            return response()->json([
                'status'  => 'error',
                'message' => "Minimum investment amount is {$plan->minimum_investment}",
            ], 400);
        }

        // Process the investment
        DB::beginTransaction();

        try {
            // Calculate dates
            $startDate = Carbon::today();
            $endDate   = $startDate->copy()->addDays($plan->lock_period);

            // Create investment
            $investment = Investment::create([
                'user_id'            => $user->id,
                'investment_plan_id' => $plan->id,
                'reference_id'       => Investment::generateReferenceId(),
                'amount'             => $request->amount,
                'current_value'      => $request->amount, // Initial value equals amount
                'return_rate'        => $plan->return_rate,
                'lock_period'        => $plan->lock_period,
                'start_date'         => $startDate,
                'end_date'           => $endDate,
                'status'             => 'active',
            ]);

            // Create transaction record
            Transaction::create([
                'user_id'       => $user->id,
                'investment_id' => $investment->id,
                'reference_id'  => Transaction::generateReferenceId(),
                'type'          => 'investment',
                'amount'        => $request->amount,
                'description'   => "Investment in {$plan->name}",
                'metadata'      => [
                    'plan_id'     => $plan->id,
                    'plan_name'   => $plan->name,
                    'return_rate' => $plan->return_rate,
                    'lock_period' => $plan->lock_period,
                ],
            ]);

            DB::commit();

            // Send notification
            // $user->notify(new InvestmentCreated($investment));

            return response()->json([
                'status'  => 'success',
                'message' => 'Investment created successfully',
                'data'    => InvestmentResource::make($investment->load('investmentPlan')),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process investment',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Withdraw an investment
     *
     * @param  Request  $request
     * @param  Investment  $investment
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(Request $request, Investment $investment)
    {
        // Check if investment belongs to authenticated user
        if ($investment->user_id !== $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized access to investment',
            ], 403);
        }

        // Check if investment is active
        if ($investment->status !== 'active') {
            return response()->json([
                'status'  => 'error',
                'message' => 'This investment has already been withdrawn or cancelled',
            ], 400);
        }

        // Check if investment is eligible for withdrawal
        if (! $investment->is_withdrawable) {
            return response()->json([
                'status'  => 'error',
                'message' => 'This investment is still locked and cannot be withdrawn until ' .
                $investment->end_date->format('Y-m-d'),
            ], 400);
        }

        // Process the withdrawal
        DB::beginTransaction();

        try {
            $user = $request->user();

            // Update current value before withdrawal
            $investment->updateCurrentValue();
            $withdrawalAmount = $investment->current_value;

            // Update investment status
            $investment->status       = 'completed';
            $investment->withdrawn_at = Carbon::now();
            $investment->save();

            // Create transaction record
            Transaction::create([
                'user_id'       => $user->id,
                'investment_id' => $investment->id,
                'reference_id'  => Transaction::generateReferenceId(),
                'type'          => 'withdrawal',
                'amount'        => $withdrawalAmount,
                'description'   => "Withdrawal from investment {$investment->reference_id}",
                'metadata'      => [
                    'investment_id'   => $investment->id,
                    'initial_amount'  => $investment->amount,
                    'interest_earned' => $withdrawalAmount - $investment->amount,
                    'days_invested'   => $investment->start_date->diffInDays($investment->withdrawn_at),
                ],
            ]);

            DB::commit();

            // Send notification
            $user->notify(new InvestmentWithdrawn($investment, $withdrawalAmount));

            return response()->json([
                'status'  => 'success',
                'message' => 'Investment withdrawn successfully',
                'data'    => [
                    'withdrawal_amount'  => number_format($withdrawalAmount),
                    'initial_investment' => number_format($investment->amount),
                    'interest_earned'    => number_format($withdrawalAmount - $investment->amount),
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to process withdrawal',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
