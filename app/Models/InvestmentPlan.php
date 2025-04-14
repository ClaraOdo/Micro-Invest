<?php
namespace App\Models;

use App\Models\Investment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'return_rate',
        'lock_period',
        'minimum_investment',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'return_rate'        => 'decimal:4',
        'minimum_investment' => 'decimal:2',
        'is_active'          => 'boolean',
    ];

    /**
     * Get all investments for this plan.
     */
    public function investments()
    {
        return $this->hasMany(Investment::class);
    }

    /**
     * Get the formatted return rate percentage.
     */
    public function getReturnRatePercentAttribute()
    {
        return $this->return_rate * 100 . '%';
    }

    /**
     * Calculate the expected return for a given amount and duration.
     */
    public function calculateExpectedReturn($amount, $days = null)
    {
        $days = $days ?? $this->lock_period;
        return $amount * (1 + $this->return_rate) ** $days - $amount;
    }
}
