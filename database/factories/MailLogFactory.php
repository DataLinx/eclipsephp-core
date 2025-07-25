<?php

namespace Eclipse\Core\Database\Factories;

use Eclipse\Core\Models\MailLog;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<MailLog>
 */
class MailLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = MailLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::inRandomOrder()->first()?->id ?? Site::factory()->create()->id,
            'message_id' => fake()->uuid(),
            'from' => fake()->email(),
            'to' => fake()->email(),
            'cc' => null,
            'bcc' => null,
            'subject' => fake()->sentence(),
            'body' => '<p>'.fake()->paragraph().'</p>',
            'headers' => [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Mailer' => 'Laravel',
            ],
            'attachments' => [],
            'sender_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'recipient_id' => null,
            'status' => fake()->randomElement(['sent', 'sending', 'failed']),
            'sent_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'data' => [],
            'opened' => null,
            'delivered' => null,
            'complaint' => null,
            'bounced' => null,
        ];
    }

    /**
     * Indicate that the mail log is sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Indicate that the mail log is sending.
     */
    public function sending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sending',
            'sent_at' => null,
        ]);
    }

    /**
     * Indicate that the mail log failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'sent_at' => null,
        ]);
    }
}
