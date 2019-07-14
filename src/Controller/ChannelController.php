<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Category;
use App\Entity\Channel;
use App\Entity\CategoryChannel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/channel")
 */
class ChannelController extends Controller
{
    /**
     * @Route("/{category}", name="app_channel_category")
     */
    public function categoryAction(Category $category, Request $request)
    {
        $categoryChannels = $this->getDoctrine()
            ->getRepository(CategoryChannel::class)
            ->orderFindByCategory($category);
        $channels = [];
        foreach ($categoryChannels as $categoryChannel) {
            $channels[] = $categoryChannel->getChannel();
        }

        $serializer = SerializerBuilder::create()->build();
        $context = SerializationContext::create()->setSerializeNull(true);
        $context->setGroups('home');
        $json = $serializer->serialize(['data' => $channels], 'json', $context);
        $response = new JsonResponse($json, Response::HTTP_OK, [], true);

        return $response;
    }

    /**
     * @Route("/", name="app_channel_index")
     */
    public function indexAction(Request $request)
    {
        $channels = $this->getDoctrine()
            ->getRepository(Channel::class)
            ->createQueryBuilder('c');
        if (($title = $request->query->get('title')) !== null) {
            $channels = $channels->where('c.title like :title')
                ->setParameter('title', '%'.$title.'%');
        }
        $channels = $channels->getQuery()->getResult();

        $serializer = $this->get('jms_serializer');
        $context = SerializationContext::create()->setSerializeNull(true);
        $context->setGroups('home');
        $json = $serializer->serialize(['data' => $channels], 'json', $context);
        $response = new JsonResponse($json, Response::HTTP_OK, [], true);

        return $response;
    }
}
