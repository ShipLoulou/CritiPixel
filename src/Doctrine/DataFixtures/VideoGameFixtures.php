<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue $countRatingsPerValue,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();

        $users = $manager->getRepository(User::class)->findAll();

        /** @var VideoGame[] $videoGames */
        $videoGames = \array_fill_callback(
            0,
            50,
            fn(int $index): VideoGame => (new VideoGame())
                ->setTitle(sprintf('Jeu vidéo %d', $index))
                ->setDescription(is_array($desc = $this->faker->paragraphs(10)) ? implode("\n", $desc) : $desc)
                ->setReleaseDate(new \DateTimeImmutable())
                ->setTest(is_array($test = $this->faker->paragraphs(6)) ? implode("\n", $test) : $test)
                ->setRating(($index % 5) + 1)
                ->setImageName(sprintf('video_game_%d.png', $index))
                ->setImageSize(2_098_872)
        );

        // TODO : Ajouter les tags aux vidéos
        array_walk($videoGames, static function (VideoGame $videoGame) use ($tags) {
            for ($index = 0; $index < 2; ++$index) {
                $videoGame->getTags()->add($tags[random_int(0, 5)]);
            }
        });

        array_walk($videoGames, [$manager, 'persist']);

        // TODO : Ajouter des reviews aux vidéos

        array_walk($videoGames, function (VideoGame $videoGame, int $index) use ($users, $manager) {
            $selectedUsers = [];

            for ($i = 0; $i < random_int(3, 7); ++$i) {
                $selectedUsers[] = $users[random_int(0, 9)];
            }

            foreach ($selectedUsers as $user) {
                $review = (new Review())
                    ->setUser($user)
                    ->setVideoGame($videoGame)
                    ->setRating($this->faker->numberBetween(1, 5))
                    ->setComment(is_array($comment = $this->faker->paragraphs(1)) ? implode("\n", $comment) : $comment);

                $videoGame->getReviews()->add($review);
                $manager->persist($review);

                $this->calculateAverageRating->calculateAverage($videoGame);
                $this->countRatingsPerValue->countRatingsPerValue($videoGame);
            }
        });

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
