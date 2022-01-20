<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductController extends AbstractController
{
    /**
     * @Route("/product", name="product")
     */
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * @Route("/product/{id}", name="product.show")
     */
    public function show(ProductRepository $productRepository, Request $request): Response
    {
        $id = $request->attributes->get('id');
        $product = $productRepository->find($id);
        if (!$product)
        {
            throw $this->createNotFoundException('The product does not exist');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

}
