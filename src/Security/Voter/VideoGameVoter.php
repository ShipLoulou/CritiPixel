<?php

namespace App\Security\Voter;

use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @phpstan-extends Voter<'review', VideoGame>
 */
class VideoGameVoter extends Voter
{
    public const REVIEW = 'review';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::REVIEW === $attribute && $subject instanceof VideoGame;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        // Make sure subject is of type VideoGame
        if (!$subject instanceof VideoGame) {
            return false;
        }

        return !$subject->hasAlreadyReview($user);
    }
}
