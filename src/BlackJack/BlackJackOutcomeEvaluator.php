<?php

namespace App\BlackJack;

/**
 * Evaluates hand outcomes and related payouts/messages in Black Jack.
 */
class BlackJackOutcomeEvaluator
{
    public const RESULT_BUST = 'bust';
    public const RESULT_BLACKJACK = 'blackjack';
    public const RESULT_PUSH = 'push';
    public const RESULT_WIN = 'win';
    public const RESULT_LOSE = 'lose';

    public function determineOutcome(BlackJackHand $playerHand, BlackJackHand $dealerHand): string
    {
        $dealerValue = $dealerHand->getValue();
        $dealerBust = $dealerHand->isBust();
        $dealerBlackJack = $dealerHand->isBlackJack();

        $playerValue = $playerHand->getValue();
        $playerBust = $playerHand->isBust();
        $playerBlackJack = $playerHand->isBlackJack();

        if ($playerBust) {
            return self::RESULT_BUST;
        }

        if ($playerBlackJack && !$dealerBlackJack) {
            return self::RESULT_BLACKJACK;
        }

        if ($playerValue === $dealerValue) {
            return self::RESULT_PUSH;
        }

        if ($dealerBust || $playerValue > $dealerValue) {
            return self::RESULT_WIN;
        }

        return self::RESULT_LOSE;
    }

    public function payoutForOutcome(string $outcome, int $bet): int
    {
        return match ($outcome) {
            self::RESULT_BLACKJACK => $bet + (int) ($bet * 1.5),
            self::RESULT_PUSH => $bet,
            self::RESULT_WIN => $bet * 2,
            default => 0,
        };
    }

    public function messageForOutcome(string $outcome): string
    {
        return match ($outcome) {
            self::RESULT_BUST => 'BUST - You lose',
            self::RESULT_BLACKJACK => 'BLACKJACK! - You win 1.5x',
            self::RESULT_PUSH => 'PUSH - Tie',
            self::RESULT_WIN => 'WIN',
            default => 'LOSE',
        };
    }
}
