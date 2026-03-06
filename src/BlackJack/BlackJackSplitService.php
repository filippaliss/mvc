<?php

namespace App\BlackJack;

use App\DeckClass\DeckOfCards;

/**
 * Handles split operation for a current player hand.
 */
class BlackJackSplitService
{
    public function splitCurrentHand(BlackJackPlayer $player, DeckOfCards $deck, int $currentHandIndex): bool
    {
        $hand = $player->getHand($currentHandIndex);
        if ($hand === null || !$hand->canSplit()) {
            return false;
        }

        $bet = $hand->getBet();
        if (!$player->canAfford($bet)) {
            return false;
        }

        $player->placeBet($bet);

        [$originalHand, $newHand] = $this->buildSplitHands($hand, $bet);
        $this->dealSplitCards($deck, $originalHand, $newHand);
        $this->replaceCurrentHandWith($player, $currentHandIndex, $originalHand, $newHand);

        return true;
    }

    /**
     * @return array{0: BlackJackHand, 1: BlackJackHand}
     */
    private function buildSplitHands(BlackJackHand $hand, int $bet): array
    {
        $cards = $hand->getCards();

        $newHand = new BlackJackHand();
        $newHand->setBet($bet);
        $newHand->addCard($cards[1]);
        $newHand->markAsSplit();

        $originalHand = new BlackJackHand();
        $originalHand->setBet($bet);
        $originalHand->addCard($cards[0]);
        $originalHand->markAsSplit();

        return [$originalHand, $newHand];
    }

    private function dealSplitCards(DeckOfCards $deck, BlackJackHand $originalHand, BlackJackHand $newHand): void
    {
        $card1 = $deck->drawCard();
        $card2 = $deck->drawCard();

        if ($card1 !== null) {
            $originalHand->addCard($card1);
        }

        if ($card2 !== null) {
            $newHand->addCard($card2);
        }
    }

    private function replaceCurrentHandWith(
        BlackJackPlayer $player,
        int $currentHandIndex,
        BlackJackHand $originalHand,
        BlackJackHand $newHand
    ): void {
        $hands = $player->getHands();
        $hands[$currentHandIndex] = $originalHand;
        array_splice($hands, $currentHandIndex + 1, 0, [$newHand]);

        $player->clearHands();
        foreach ($hands as $h) {
            $player->addHand($h);
        }
    }
}
