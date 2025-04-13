<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'user_id',
        'investment_plan_id',
        'reference_id',
        'amount',
        'current_value',
        'return_rate',
        'lock_period',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts()
    {
        return [
            'amount'        => 'decimal:2',
            'current_value' => 'decimal:2',
            'return_rate'   => 'decimal:4',
            'start_date'    => 'datetime',
            'end_date'      => 'datetime',
            'withdrawn_at'  => 'datetime',
            'created_at'    => 'datetime',
        ];
    }
    /**
     * Get the user that owns the investment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment plan that the investment belongs to.
     */
    public function investmentPlan()
    {
        return $this->belongsTo(InvestmentPlan::class);
    }

    /**
     * Get the transactions for this investment.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if the investment is eligible for withdrawal.
     */
    public function getIsWithdrawableAttribute()
    {
        return ($this->status === 'active') &&
            (Carbon::now()->greaterThanOrEqualTo($this->end_date));
    }

    /**
     * Calculate accrued interest to date.
     */
    public function getAccruedInterestAttribute()
    {
        if ($this->status !== 'active') {
            return 0;
        }

        // Calculate days passed since investment start
        $daysElapsed = min(
            $this->start_date->diffInDays(Carbon::now()),
            $this->lock_period
        );

        // Calculate the interest based on compound interest formula
        // A = P(1 + r)^t
        // Where A is final amount, P is principal, r is rate, t is time
        $interestAmount = $this->amount * (1 + $this->return_rate) ** $daysElapsed - $this->amount;

        return round($interestAmount, 2);
    }

    /**
     * Update the current value of the investment.
     */
    public function updateCurrentValue()
    {
        if ($this->status === 'active') {
            $this->current_value = $this->amount + $this->accrued_interest;
            $this->save();
        }

        return $this->current_value;
    }

    /**
     * Generate a unique reference ID for a new investment.
     */
    public static function generateReferenceId()
    {
        return 'INV-' . strtoupper(uniqid());
    }
}
