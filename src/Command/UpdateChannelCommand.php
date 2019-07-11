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

class UpdateChannelCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:channel:update';

    protected function configure()
    {
        $this
            ->addArgument('categoryId', InputArgument::OPTIONAL, 'Category ID')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $token = $container->get(TokenGenerator::class)->getToken();
        if (!$token) {
            throw new \Exception('get token failed!');
        }

        $categorys = [$input->getArgument('categoryId')];
        if ($categorys[0] === null) {
            $categorys = $this->updateCategory($container, $token);
        }

        // $this->updateChannel($container, $token, $categorys);
    }

    private function updateCategory($container, $token)
    {
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', $_ENV['API_HOST'].'/media/v7/channellive_categories?access_token='.$token);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception('get categories failed!');
        }
        $data = json_decode($response->getContent(), true);

        $em = $container->get('doctrine.orm.entity_manager');
        $savedRegionsCategories = $em->getRepository('App:Category')
            ->findByTypeOrderBySequence('regions')
        ;
        $savedContentsCategories = $em->getRepository('App:Category')
            ->findByTypeOrderBySequence('content')
        ;
        $categorys = [];
        // foreach ($data['data']['regions'] as $sequence => $region) {
        //     $currentRegion = current($savedRegionsCategories);
        //     // 第一次保存
        //     if (!$currentRegion or $region['id'] !== $currentRegion->getEntityId()) {
        //         $category = new Category($region['id'], $region['title'], 'regions', $sequence);
        //         $category = $this->updateChannels($container, $token, $category);
        //         $em->persist($category);
        //         $categorys[] = $category;
        //
        //         continue;
        //     }
        //     $categorys[] = $currentRegion;
        //     // 所有数据都相等
        //     if ($region['title'] == $currentRegion->getTitle()
        //         && $sequence == $currentRegion->getSequence()
        //     ) {
        //         $currentRegion = $this->updateChannels($container, $token, $currentRegion);
        //         $em->persist($currentRegion);
        //         next($savedRegionsCategories);
        //
        //         continue;
        //     }
        //
        //     if ($region['title'] !== $currentRegion->getTitle()) {
        //         $currentRegion->setTitle($region['title']);
        //     }
        //
        //     if ($sequence !== $currentRegion->getSequence()) {
        //         $currentRegion->setSequence($sequence);
        //     }
        //     $em->persist($currentRegion);
        // }

        foreach ($data['data']['content'] as $sequence => $content) {
            $currentContent = current($savedContentsCategories);
            // 第一次保存
            if (!$currentContent or $content['id'] !== $currentContent->getEntityId()) {
                $category = new Category($content['id'], $content['title'], 'content', $sequence);
                $category = $this->updateChannels($container, $token, $category);
                $em->persist($category);
                $categorys[] = $category;

                continue;
            }
            $categorys[] = $currentContent;
            // 所有数据都相等
            if ($region['title'] == $currentContent->getTitle()
                && $sequence == $currentContent->getSequence()
            ) {
                $currentContent = $this->updateChannels($container, $token, $currentContent);
                $em->persist($currentContent);
                next($savedContentsCategories);

                continue;
            }

            if ($region['title'] !== $currentContent->getTitle()) {
                $currentContent->setTitle($content['title']);
            }

            if ($sequence !== $currentContent->getSequence()) {
                $currentContent->setSequence($sequence);
            }
            $em->persist($currentContent);
        }
        $em->flush();

        return $categorys;
    }

    private function updateChannel($container, $token, $categorys)
    {
        $url = $_ENV['API_HOST'].'/media/v7/channellives?access_token='.$token.'&category_id=%categoryId';
        $em = $container->get('doctrine.orm.entity_manager');
        foreach ($category->getChannels() as $a) {
            dump($a);die;
        }
        foreach ($categorys as $category) {
            $httpClient = HttpClient::create();
            $response = $httpClient->request('GET', sprintf($url, compact('categoryId')));

            $data = json_decode($response->getContent(), true);
            foreach ($data['data'] as $channelData) {
                $channel = new Channel(
                    $category,
                    $channelData['id'],
                    $channelData['popularity'],
                    $channelData['title'],
                    $channelData['description'],
                    new \DateTime($channelData['update_time']),
                    $channelData['thumbs']['small_thumb']
                );
                $em->persist($channel);
            }
        }
        $em->flush();
    }

    private function updateChannels($container, $token, $category)
    {
        $savedChannel = [];
        if ($category->getChannels()->count()) {

        }
        $url = $_ENV['API_HOST'].'/media/v7/channellives?access_token='.$token.'&category_id=%categoryId';
        $httpClient = HttpClient::create();
        $response = $httpClient->request('GET', sprintf($url, ['categoryId' => $category->getEntityId()]));
        $data = json_decode($response->getContent(), true);
        $channels = [];
        $em = $container->get('doctrine.orm.entity_manager');
        foreach ($data['data'] as $channelData) {
            $channel = $em->getRepository('App:Channel')->findOneByEntityId($channelData['id']);
            if (!$channel) {
                $channel = new Channel(
                    $category,
                    $channelData['id'],
                    $channelData['popularity'],
                    $channelData['title'],
                    $channelData['description'],
                    new \DateTime($channelData['update_time']),
                    $channelData['thumbs']['small_thumb']
                );
            }
            $channel->addCategory($category);
            $em->persist($channel);
            $channels[] = $channel;
        }
        $category->setChannels($channels);

        return $category;
    }
}
