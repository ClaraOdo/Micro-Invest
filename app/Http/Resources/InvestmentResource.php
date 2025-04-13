<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'reference_id'          => $this->reference_id,
            'amount'                => $this->amount,
            'current_value'         => $this->current_value,
            'accrued_interest'      => $this->accrued_interest,
            'return_rate'           => $this->return_rate,
            'return_rate_formatted' => ($this->return_rate * 100) . '%',
            'lock_period'           => $this->lock_period,
            'lock_period_formatted' => $this->lock_period . ' days',
            'start_date'            => $this->start_date->format('Y-m-d'),
            'end_date'              => $this->end_date->format('Y-m-d'),
            'withdrawn_at'          => $this->when($this->withdrawn_at, $this->withdrawn_at?->format('Y-m-d')),
            'status'                => $this->status,
            'is_withdrawable'       => $this->is_withdrawable,
            'days_remaining'        => floor($this->when($this->status === 'active', max(0, now()->diffInDays($this->end_date, false)))),
            'days_active'           => floor($this->start_date->diffInDays(now())),
            'plan'                  => $this->whenLoaded('investmentPlan', function () {
                return [
                    'id'          => $this->investmentPlan->id,
                    'name'        => $this->investmentPlan->name,
                    'description' => $this->investmentPlan->description,
                ];
            }),
            'created_at'            => $this->created_at->format('Y-m-d h:i:s'),
        ];

    }
}
