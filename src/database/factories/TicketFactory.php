<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(6),
            'description' => fake()->paragraph(3),
            'attachment_path' => null,
            'attachment_original_name' => null,
            'attachment_mime' => null,
        ];
    }

    /**
     * @param string $diskPath e.g. "ticket_attachments/file.json"
     * @param string $originalName e.g. "file.json"
     * @param string $mime e.g. "application/json"
     * @return static
     */
    public function withAttachment(string $diskPath, string $originalName, string $mime): static
    {
        return $this->state(fn () => [
            'attachment_path' => $diskPath,
            'attachment_original_name' => $originalName,
            'attachment_mime' => $mime,
        ]);
    }
}