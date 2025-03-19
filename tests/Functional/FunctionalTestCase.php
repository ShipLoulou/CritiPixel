<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Model\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;

abstract class FunctionalTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    /**
     * @return object
     */
    protected function getEntityManager(): object
    {
        return $this->service(EntityManagerInterface::class);
    }

    /**
     * @return object
     */
    protected function service(string $id): object
    {
        return $this->client->getContainer()->get($id);
    }

    protected function get(string $uri, array $parameters = []): Crawler
    {
        return $this->client->request('GET', $uri, $parameters);
    }

    protected function login(string $email = 'user+0@email.com'): void
    {
        $user = $this->service(EntityManagerInterface::class)->getRepository(User::class)->findOneByEmail($email);

        $this->client->loginUser($user);
    }
}
