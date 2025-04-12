<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvestmentPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'description'            => $this->description,
            'return_rate'            => $this->return_rate,
            'return_rate_percentage' => $this->return_rate_percent,
            'lock_period'            => $this->lock_period,
            'lock_period_text'       => $this->lock_period . ' days',
            'minimum_investment'     => $this->minimum_investment,
            'created_at'             => $this->created_at->format('Y-m-d h:i:s'),
        ];

    }
}
