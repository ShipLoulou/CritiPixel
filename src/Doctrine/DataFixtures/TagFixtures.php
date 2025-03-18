<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class TagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $nameTage = ['Action', 'Aventure', 'Réfléxion', 'Guerre', 'Simulation', 'Arcade'];

        $index = 1;

        foreach ($nameTage as $value) {
            $tag = (new Tag())
                ->setId($index)
                ->setName($value);

            $manager->persist($tag);
            ++$index;
        }

        $manager->flush();
    }
}
