<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(SessionInterface $session): Response
    {

        $cart = $session->get('panier', []);
        print_r($cart);
        return $this->render('cart/index.html.twig', [
            'cart' => $cart,
        ]);
    }

    /**
     * @Route("/cart/add/{id}", name="cart.add")
     */
    public function add(SessionInterface $session, Request $request, ProductRepository $productRepository): Response
    {

        try{
            $id = $request->attributes->get('id');
            $product = $productRepository->find($id);
            if (!$product)
            {
                return $this->json([
                    'status' => 404,
                    'message' => 'nok'
                ], 404);
            }

            $cart = $session->get('panier', []);
            $cart[$id] = 1;
            $session->set('panier', $cart);

            return $this->json([
                'status' => 200,
                'message' => 'ok',
            ], 200);
        }catch(Exception $e){
            return $this->json([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
        
    }
}
