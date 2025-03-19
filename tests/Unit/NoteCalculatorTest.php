<?php

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Rating\RatingHandler;
use App\Model\Entity\VideoGame;
use PHPUnit\Framework\TestCase;

class NoteCalculatorTest extends TestCase
{
    /**
     * Création d'une entité VideoGame
     * 
     * @param integer ...$ratings - Tableau avec un ensemble de note
     * @return VideoGame
     */
    private static function createVideoGame(int ...$ratings): VideoGame
    {

        // Création d'un nouveau jeu vidéo
        $videoGame = new VideoGame;

        // Boucle sur l'ensemble des notes. Chaque note est associé à une Review et l'ensemble de ces Review à un VideoGame.
        foreach ($ratings as $rating) {
            $videoGame->getReviews()->add((new Review())->setRating($rating));
        }

        return $videoGame;
    }

    /**
     * Création des différents tests
     *
     * @return iterable
     */
    public static function providerVideoGame(): iterable
    {
        // Test le fonctionnement sans note.
        yield 'Pas de note' => [new VideoGame];

        // Test le fonctionnement avec une seul note.
        yield 'Une seul note' => [self::createVideoGame(1), 1];

        // Test le fonctionnement avec plusieurs notes.
        yield 'Plusieurs notes' => [self::createVideoGame(1, 5, 3, 2, 2, 1, 5, 4, 3, 2), 2, 3, 2, 1, 2];
    }

    /**
     * @dataProvider providerVideoGame
     */
    public function testShouldCalculateAverageRating(
        VideoGame $videoGame,
        ?int $numberExpectedForScoreOfOne = 0,
        ?int $numberExpectedForScoreOfTwo = 0,
        ?int $numberExpectedForScoreOfThree = 0,
        ?int $numberExpectedForScoreOfFour = 0,
        ?int $numberExpectedForScoreOfFive = 0
    ): void {
        // Appel de la fonction countRatingsPerValue.
        $ratingHandler = new RatingHandler();
        $ratingHandler->countRatingsPerValue($videoGame);

        // Comparaison entre le résultat attendu et le résultat obtenue.
        $this->assertSame($numberExpectedForScoreOfOne, $videoGame->getNumberOfRatingsPerValue()->getNumberOfOne());
        $this->assertSame($numberExpectedForScoreOfTwo, $videoGame->getNumberOfRatingsPerValue()->getNumberOfTwo());
        $this->assertSame($numberExpectedForScoreOfThree, $videoGame->getNumberOfRatingsPerValue()->getNumberOfThree());
        $this->assertSame($numberExpectedForScoreOfFour, $videoGame->getNumberOfRatingsPerValue()->getNumberOfFour());
        $this->assertSame($numberExpectedForScoreOfFive, $videoGame->getNumberOfRatingsPerValue()->getNumberOfFive());
    }
}
