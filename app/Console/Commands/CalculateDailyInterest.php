<?php
namespace App\Console\Commands;

use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Console\Command;

class CalculateDailyInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-daily-interest';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get all active investments
        $activeInvestments = Investment::where('status', 'active')->get();

        foreach ($activeInvestments as $investment) {
            // Calculate daily interest
            $dailyInterest = $investment->amount * $investment->return_rate;

            // Update current value
            $investment->current_value += $dailyInterest;
            $investment->save();

            // Record interest transaction
            Transaction::create([
                'user_id'       => $investment->user_id,
                'investment_id' => $investment->id,
                'reference_id'  => Transaction::generateReferenceId(),
                'type'          => 'interest',
                'amount'        => $dailyInterest,
                'description'   => 'Daily interest accrued',
            ]);
        }

    }
}
