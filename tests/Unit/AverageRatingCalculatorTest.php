<?php

namespace App\Tests\Unit;

use App\Model\Entity\Review;
use App\Rating\RatingHandler;
use App\Model\Entity\VideoGame;
use PHPUnit\Framework\TestCase;

class AverageRatingCalculatorTest extends TestCase
{
    /**
     * Création d'une entité VideoGame
     * 
     * @param int ...$ratings - Tableau avec un ensemble de note
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
        yield 'Pas de note' => [new VideoGame, null];

        // Test le fonctionnement avec une seul note.
        yield 'Une seul note' => [self::createVideoGame(3), 3];

        // Test le fonctionnement avec plusieurs notes.
        yield 'Plusieurs notes' => [self::createVideoGame(1, 5, 3, 2, 2, 1, 5, 4, 3, 2), 3];
    }

    /**
     * @dataProvider providerVideoGame
     */
    public function testShouldCalculateAverageRating(VideoGame $videoGame, ?int $expectedAverageRating): void
    {
        // Appel de la fonction calculateAverage.
        $ratingHandler = new RatingHandler();
        $ratingHandler->calculateAverage($videoGame);

        // Comparaison entre le résultat attendu et le résultat obtenue.
        $this->assertSame($expectedAverageRating, $videoGame->getAverageRating());
    }
}
