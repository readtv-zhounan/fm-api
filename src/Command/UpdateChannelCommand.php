<?php

namespace App\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use App\Service\TokenGenerator;
use Symfony\Component\HttpClient\HttpClient;
use App\Entity\Category;
use App\Entity\Channel;
use App\Entity\CategoryChannel;

class UpdateChannelCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    private $channels;
    private $em;

    protected static $defaultName = 'app:channel:update';

    protected function configure()
    {
        $this->addArgument('categoryId', InputArgument::OPTIONAL, 'Category ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $token = $container->get(TokenGenerator::class)->getToken();
        if (!$token) {
            throw new \Exception('get token failed!');
        }

        $this->em = $container->get('doctrine.orm.entity_manager');
        if ($categoryId = $input->getArgument('categoryId')) {
            $category = $this->em->getRepository('App:Category')->findOneByEntityId($categoryId);
            if (!$category) {
                $output->writeln(sprintf('<comment>Category %s not found</comment>', $categoryId));
                return;
            }
            $this->updateChannels($token, $category);
            $this->em->flush();

            $output->writeln(sprintf('<info>Category %s channel updated</info>', $categoryId));
            return;
        }
        $categorys = [$input->getArgument('categoryId')];
        if ($categorys[0] === null) {
            $categorys = $this->updateCategory($token);
        }
        $output->writeln('<info>Done!</info>');
    }

    private function updateCategory($token)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $_ENV['API_HOST'].'/media/v7/channellive_categories?access_token='.$token);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('get categories failed!');
        }
        $data = json_decode($response->getContent(), true);

        $savedRegionsCategories = $this->em->getRepository('App:Category')
            ->findByTypeOrderBySequence('regions')
        ;
        $savedContentsCategories = $this->em->getRepository('App:Category')
            ->findByTypeOrderBySequence('content')
        ;
        $categorys = [];
        foreach ($data['data']['regions'] as $sequence => $region) {
            $currentRegion = current($savedRegionsCategories);
            // 第一次保存
            if (!$currentRegion or $region['id'] !== $currentRegion->getEntityId()) {
                $category = $this->em->getRepository('App:Category')->findOneByEntityId($region['id']);
                if ($category) {
                    $category->setSequence($sequence);
                } else {
                    $category = new Category($region['id'], $region['title'], 'regions', $sequence);
                }
                $this->em->persist($category);

                $categorys[] = $category;
                // $this->updateChannels($token, $category);

                continue;
            }
            $categorys[] = $currentRegion;
            // 所有数据都相等
            if ($region['title'] == $currentRegion->getTitle()
                && $sequence == $currentRegion->getSequence()
            ) {
                // $this->updateChannels($token, $currentRegion);
                next($savedRegionsCategories);

                continue;
            }

            if ($region['title'] !== $currentRegion->getTitle()) {
                $currentRegion->setTitle($region['title']);
            }

            if ($sequence !== $currentRegion->getSequence()) {
                $currentRegion->setSequence($sequence);
            }
            $this->em->persist($currentRegion);
            next($savedRegionsCategories);
        }

        foreach ($data['data']['content'] as $sequence => $content) {
            $currentContent = current($savedContentsCategories);
            if (!$currentContent or $content['id'] !== $currentContent->getEntityId()) {
                $category = $this->em->getRepository('App:Category')->findOneByEntityId($content['id']);
                if ($category) {
                    $category->setSequence($sequence);
                } else {
                    $category = new Category($content['id'], $content['title'], 'content', $sequence);
                }
                // $this->updateChannels($token, $category);
                $this->em->persist($category);
                $categorys[] = $category;

                continue;
            }
            $categorys[] = $currentContent;
            // 所有数据都相等
            if ($region['title'] == $currentContent->getTitle()
                && $sequence == $currentContent->getSequence()
            ) {
                // $this->updateChannels($token, $currentContent);
                // $em->persist($currentContent);
                next($savedContentsCategories);

                continue;
            }

            if ($region['title'] !== $currentContent->getTitle()) {
                $currentContent->setTitle($content['title']);
            }

            if ($sequence !== $currentContent->getSequence()) {
                $currentContent->setSequence($sequence);
            }
            $this->em->persist($currentContent);
        }
        $this->em->flush();

        // 必须先保存category数据，才能保存对应Channel信息
        foreach($categorys as $category) {
            $this->updateChannels($token, $category);
        }

        return $categorys;
    }

    private function updateChannels($token, $category)
    {
        $savedChannel = [];
        $data = $this->getAllChannel($token, $category);

        $channels = [];
        $categoryChannels = $this->em->getRepository('App:CategoryChannel')
            ->orderFindByCategory($category)
        ;
        $deleteCategoryChannels = [];
        // $currentCategoryChannel = $category->getCategoryChannels();
        foreach ($data as $sequence => $channelData) {
            if (isset($this->channels[$channelData['id']])) {
                $channel = $this->channels[$channelData['id']];
            } else {
                $channel = $this->em->getRepository('App:Channel')->findOneByEntityId($channelData['id']);
            }

            if ($channel) {
                if ($channel->getUpdateTime()->format('Y-m-d H:i:s') !== $channelData['update_time']) {
                    $channel = $channel->setPopularity($channelData['popularity'])
                        ->setTitle($channelData['title'])
                        ->setDescription($channelData['description'])
                        ->setUpdateTime(new \DateTime($channelData['update_time']))
                    ;
                }
            } else {
                $channel = new Channel(
                    $channelData['id'],
                    $channelData['popularity'],
                    $channelData['title'],
                    $channelData['description'],
                    new \DateTime($channelData['update_time']),
                    $channelData['thumbs']['small_thumb']
                );
            }
            $this->em->persist($channel);
            $this->channels[$channelData['id']] = $channel;
            if (current($categoryChannels)) {
                if (current($categoryChannels)->getChannel()->getEntityId() == $channelData['id']
                && current($categoryChannels)->getSequence() == $sequence) {
                    next($categoryChannels);
                    continue;
                }
                $deleteCategoryChanels[] = current($categoryChannels)->getId();
                $category->removeCategoryChannels(current($categoryChannels));
            }
            $savedCategoryChannel = $this->em->getRepository('App:CategoryChannel')
                ->findOneBy([
                    'category' => $category,
                    'channel' => $channel,
                ])
            ;
            if ($savedCategoryChannel && !in_array($savedCategoryChannel->getId(), $deleteCategoryChanels)) {
                $savedCategoryChannel->setSequence($sequence);
                $this->em->persist($savedCategoryChannel);
                continue;
            }

            $categoryChannel = new CategoryChannel($category, $channel, $sequence);
            $this->em->persist($categoryChannel);

            $channel->addCategoryChannels($categoryChannel);
            $this->em->persist($channel);
            $category->addCategoryChannels($categoryChannel);
            $this->em->persist($category);
            $this->em->flush();
        }

        return $category;
    }

    private function getAllChannel($token, $category)
    {
        $total = 1;
        $result = [];

        $url = $_ENV['API_HOST'].'/media/v7/channellives?access_token='.$token.'&category_id=%s&page=%s';
        $httpClient = HttpClient::create();
        for ($page=1; $page <= $total; $page++) {
            $response = $httpClient->request('GET', sprintf($url, $category->getEntityId(), $page));
            $data = json_decode($response->getContent(), true);
            $total = (int)ceil($data['total']/30);
            $result = array_merge($result, $data['data']);
        }

        return $result;
    }
}
