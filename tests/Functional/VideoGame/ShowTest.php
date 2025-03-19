<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\User;
use App\Model\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;




class ShowTest extends WebTestCase
{
    private KernelBrowser|null $client = null;
    private UrlGeneratorInterface $urlGenerator;
    protected AbstractDatabaseTool $databaseTool;

    // Contenu par défault
    private int $ratingContent = 4;
    private string $commentContent = 'Je suis un commentaire de test.';

    public function setUp(): void
    {
        $this->client = static::createClient();

        // Suppression et réinsertion des fixtures pour nettoyer la base de donnée de test. 
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAllFixtures();

        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        $user = $this->service(EntityManagerInterface::class)->getRepository(User::class)->findOneByEmail('user+0@email.com');
        $this->client->loginUser($user);
    }

    /**
     * @return object
     */
    protected function getEntityManager(): object
    {
        return $this->service(EntityManagerInterface::class);
    }

    protected function service(string $id): object
    {
        return $this->client->getContainer()->get($id);
    }

    /**
     * @param string $uri
     * @param array<string, string> $parameters
     * @return Crawler
     */
    protected function get(string $uri, array $parameters = []): Crawler
    {
        return $this->client->request('GET', $uri, $parameters);
    }


    // Ajout d'une note et d'un commentaire par un utilisateur.
    public function testAddReview(string $slug = 'jeu-video-0'): void
    {

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $slug]));
        $form = $crawler->selectButton('Poster')->form();
        $form['review[rating]'] = "$this->ratingContent";
        $form['review[comment]'] = $this->commentContent;
        $this->client->submit($form);

        // Vérifier la réponse HTTP que reçoit l'utilisateur.
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        // Vérifier que les informations sont enregistrées dans le base de donnée.
        $review = $this->service(EntityManagerInterface::class)->getRepository(Review::class)->findOneBy(['rating' => $this->ratingContent, 'comment' => $this->commentContent]);
        // $this->reviewRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Review::class);
        // $review = $this->reviewRepository->findOneBy(['rating' => $this->ratingContent, 'comment' => $this->commentContent]);

        $this->assertNotNull($review);

        // Vérifier que le formulaire d'ajout de note n'est plus affiché à l'utilisateur.
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $slug]));
        $this->assertEquals(0, $crawler->filter('form[name="review"]')->count());
    }
}
