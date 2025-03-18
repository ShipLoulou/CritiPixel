<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    // Filtrage des tags.
    public function providerTag(): iterable
    {
        yield 'Aucun tag' => [[]];

        yield 'Un seul tag' => [['1' => 'Action']];

        yield 'Deux tags' => [['1' => 'Action', '2' => 'Aventure']];
    }

    /**
     * @dataProvider providerTag
     */
    public function testShouldFilterVideoGamesByTags(array $tags): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');

        // Soumet le formulaire avec les tags donnés
        $this->client->submitForm('Filtrer', ['filter[tags]' => array_keys($tags)], 'GET');
        self::assertResponseIsSuccessful();

        // Vérifie que chaque article contient bien les tags filtrés
        $crawler = $this->client->getCrawler();
        $articles = $crawler->filter('article.game-card');

        foreach ($articles as $article) {
            foreach ($tags as $tagValue) {
                self::assertStringContainsString($tagValue, $article->textContent);
            }
        }
    }

    /**
     * Teste le comportement lorsqu'un utilisateur spécifie un tag qui n'existe pas..
     */
    public function testIfTagDoesNotExist(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->request('GET', '/', ['filter' => ['tags' => ['999']]]);
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
    }
}
