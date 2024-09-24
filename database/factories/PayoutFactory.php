<?php

namespace Database\Factories;

use App\Enums\PayoutStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $status = $this->faker->randomElement(PayoutStatus::cases());

        if ($status !== PayoutStatus::Pending) {
            $stripeCheckoutSessionId = 'cs_test_' . Str::random(58);
        } else {
            $stripeCheckoutSessionId = null;
        }

        if ($status === PayoutStatus::Paid) {
            $stripePaymentIntentId = 'pi_' . Str::random(24);
        } else {
            $stripePaymentIntentId = null;
        }

        return [
            'status' => $status,
            'stripe_checkout_session_id' => $stripeCheckoutSessionId,
            'stripe_payment_intent_id' => $stripePaymentIntentId,
        ];
    }
}
