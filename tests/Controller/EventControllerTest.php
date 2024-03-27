<?php

namespace App\Test\Controller;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/event/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Event::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'event[name]' => 'Testing',
            'event[dateStart]' => 'Testing',
            'event[duration]' => 'Testing',
            'event[dateLimitRegistration]' => 'Testing',
            'event[maxRegistration]' => 'Testing',
            'event[eventInfo]' => 'Testing',
            'event[location]' => 'Testing',
            'event[eventOrganizer]' => 'Testing',
            'event[participate]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->repository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Event();
        $fixture->setName('My Title');
        $fixture->setDateStart('My Title');
        $fixture->setDuration('My Title');
        $fixture->setDateLimitRegistration('My Title');
        $fixture->setMaxRegistration('My Title');
        $fixture->setEventInfo('My Title');
        $fixture->setLocation('My Title');
        $fixture->setEventOrganizer('My Title');
        $fixture->setParticipate('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Event();
        $fixture->setName('Value');
        $fixture->setDateStart('Value');
        $fixture->setDuration('Value');
        $fixture->setDateLimitRegistration('Value');
        $fixture->setMaxRegistration('Value');
        $fixture->setEventInfo('Value');
        $fixture->setLocation('Value');
        $fixture->setEventOrganizer('Value');
        $fixture->setParticipate('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'event[name]' => 'Something New',
            'event[dateStart]' => 'Something New',
            'event[duration]' => 'Something New',
            'event[dateLimitRegistration]' => 'Something New',
            'event[maxRegistration]' => 'Something New',
            'event[eventInfo]' => 'Something New',
            'event[location]' => 'Something New',
            'event[eventOrganizer]' => 'Something New',
            'event[participate]' => 'Something New',
        ]);

        self::assertResponseRedirects('/event/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDateStart());
        self::assertSame('Something New', $fixture[0]->getDuration());
        self::assertSame('Something New', $fixture[0]->getDateLimitRegistration());
        self::assertSame('Something New', $fixture[0]->getMaxRegistration());
        self::assertSame('Something New', $fixture[0]->getEventInfo());
        self::assertSame('Something New', $fixture[0]->getLocation());
        self::assertSame('Something New', $fixture[0]->getEventOrganizer());
        self::assertSame('Something New', $fixture[0]->getParticipate());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Event();
        $fixture->setName('Value');
        $fixture->setDateStart('Value');
        $fixture->setDuration('Value');
        $fixture->setDateLimitRegistration('Value');
        $fixture->setMaxRegistration('Value');
        $fixture->setEventInfo('Value');
        $fixture->setLocation('Value');
        $fixture->setEventOrganizer('Value');
        $fixture->setParticipate('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/event/');
        self::assertSame(0, $this->repository->count([]));
    }
}
