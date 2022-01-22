<?php

namespace App\Controller;

use Exception;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class CartController extends AbstractController
{
    /**
     * @Route("/cart", name="cart")
     */
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {

        $cart = $session->get('panier', []);
        $products = [];
        $total = 0;
        foreach ($cart as $key => $value) {
            $product = $productRepository->find($key);
            $products[] = [
                "id" => $product->getId(),
                "name" => $product->getName(),
                "price" => $product->getPrice(),
                "quantite" => $value
            ];
            $total += $product->getPrice();
        }
        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'total' => $total,
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

    /**
     * @Route("/cart/delete/{id}", name="cart.delete")
     */
    public function delete(SessionInterface $session, Request $request, ProductRepository $productRepository): Response
    {

        $id = $request->attributes->get('id');
        $product = $productRepository->find($id);
        if (!$product)
        {
            throw $this->createNotFoundException('The product does not exist');
        }
        $csrfToken = $_GET['token'];
        if ($this->isCsrfTokenValid('delete-item', $csrfToken))
        {
            $cart = $session->get('panier', []);
            if(!isset($cart[$id])){
                return $this->json([
                    'status' => 404,
                    'message' => 'nok'
                ], 404);
            }
            unset($cart[$id]);
            $session->set('panier', $cart);

            $this->addFlash('alert', "Le produit {$product->getName()} a bien été supprimé !");
            
            return $this->redirectToRoute('cart');
        }else{
            throw new InvalidCsrfTokenException();
        }
        
    }
}
