<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketDetail>
 */
class TicketDetailFactory extends Factory
{
    protected $model = TicketDetail::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'technical_data' => null,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return static
     */
    public function withTechnicalData(array $data): static
    {
        return $this->state(fn () => [
            'technical_data' => $data,
        ]);
    }
}
