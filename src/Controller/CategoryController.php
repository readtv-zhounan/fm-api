<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity\Category;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerBuilder;

/**
 * @Route("/category")
 */
class CategoryController extends Controller
{
    /**
     * @Route("/", name="app_category_index")
     */
    public function indexAction()
    {
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->orderBy('c.type', 'desc')
            ->addOrderBy('c.sequence')
            ->getQuery()
            ->getResult()
        ;

        $regions = $content = [];
        foreach ($categories as $category) {
            if ($category->getType() == 'regions') {
                $regions[] = $category;
                continue;
            }
            $content[] = $category;
        }

        $serializer = SerializerBuilder::create()->build();
        $context = SerializationContext::create()->setSerializeNull(true);
        $context->setGroups('home');
        $json = $serializer->serialize(['data' => compact('regions', 'content')], 'json', $context);
        $response = new JsonResponse($json, Response::HTTP_OK, [], true);

        return $response;
    }
}
