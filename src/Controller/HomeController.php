<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(ProductRepository $productRepository): Response
    {

        $productsPrice = $productRepository->findBy([],["price" => "ASC"], 5);
        $productsDate = $productRepository->findBy([],["createdAt" => "DESC"], 5);

        return $this->render('home/index.html.twig', [
            'productsPrice' => $productsPrice,
            'productsDate' => $productsDate,
        ]);
    }
}
