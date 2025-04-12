<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'investment_id',
        'reference_id',
        'type',
        'amount',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the investment associated with the transaction, if any.
     */
    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    /**
     * Generate a unique reference ID for a new transaction.
     */
    public static function generateReferenceId()
    {
        return 'TXN-' . strtoupper(uniqid());
    }
}
