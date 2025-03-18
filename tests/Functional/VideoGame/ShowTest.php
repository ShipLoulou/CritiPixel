<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\User;
use App\Model\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class ShowTest extends WebTestCase
{
    private KernelBrowser|null $client = null;
    // private $userRepository;
    // private $reviewRepository;
    // private $user;
    // private $urlGenerator;
    // protected $databaseTool;
    private $userRepository;
    private $reviewRepository;
    private ?User $user = null;
    private UrlGeneratorInterface $urlGenerator;
    protected AbstractDatabaseTool $databaseTool;

    // Contenu par défault
    private $ratingContent = 4;
    private $commentContent = 'Je suis un commentaire de test.';

    public function setUp(): void
    {
        $this->client = static::createClient();

        // Suppression et réinsertion des fixtures pour nettoyer la base de donnée de test. 
        $this->databaseTool = $this->getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadAllFixtures();

        $entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        // Créer un nouvel utilisateur
        $user = (new User())
            ->setEmail('test@test.com')
            ->setUsername('User Test');

        $hashedPassword = $passwordHasher->hashPassword($user, 'password');
        $user->setPlainPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        // Connexion à la base de donnée de test.
        $this->userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $this->userRepository->findOneByEmail('test@test.com');

        $this->urlGenerator = $this->client->getContainer()->get('router.default');
        $this->client->loginUser($this->user);
    }

    // Ajout d'une note et d'un commentaire par un utilisateur.
    public function testAddReview($slug = 'jeu-video-0')
    {

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $slug]));
        $form = $crawler->selectButton('Poster')->form();
        $form['review[rating]'] = $this->ratingContent;
        $form['review[comment]'] = $this->commentContent;
        $this->client->submit($form);

        // Vérifier la réponse HTTP que reçoit l'utilisateur.
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        // Vérifier que les informations sont enregistrées dans le base de donnée.
        $this->reviewRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Review::class);
        $review = $this->reviewRepository->findOneBy(['rating' => $this->ratingContent, 'comment' => $this->commentContent]);

        $this->assertNotNull($review);

        // Vérifier que le formulaire d'ajout de note n'est plus affiché à l'utilisateur.
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('video_games_show', ['slug' => $slug]));

        $this->assertEquals(0, $crawler->filter('form[name="review"]')->count());
    }
}
