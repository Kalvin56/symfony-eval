<?php

namespace App\Controller;

use Exception;
use App\Entity\Command;
use App\Form\CommandType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    public function index(SessionInterface $session, ProductRepository $productRepository, Request $request, EntityManagerInterface $em): Response
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
            $total += $product->getPrice()*$value;
        }

        $command = new Command();

        $commandForm = $this->createForm(CommandType::class, $command);

        $commandForm->handleRequest($request);

        if ($commandForm->isSubmitted() && $commandForm->isValid())
        {
            if(count($cart) < 1){
                $this->addFlash('alert-error', "Aucun produit dans le panier");
                return $this->redirectToRoute('cart');
            }
            $command->setCreatedAt(new \DateTime);
            foreach ($cart as $key => $value) {
                $product = $productRepository->find($key);
                $command->addProduct($product);
                unset($cart[$key]);
            }
            $em->persist($command);
            $em->flush();

            $session->set('panier', $cart);

            $this->addFlash('alert-success', "La commande a bien été effectuée !");
            return $this->redirectToRoute('cart');
        }

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'total' => $total,
            'commandForm' => $commandForm->createView()
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
            $this->addFlash('alert-error', "Le produit n'existe pas");
            return $this->redirectToRoute('cart');
        }
        $csrfToken = $_GET['token'];
        if ($this->isCsrfTokenValid('delete-item', $csrfToken))
        {
            $cart = $session->get('panier', []);
            if(!isset($cart[$id])){
                $this->addFlash('alert-error', "Le produit n'est pas présent dans le panier");
                return $this->redirectToRoute('cart');
            }
            unset($cart[$id]);
            $session->set('panier', $cart);

            $this->addFlash('alert-success', "Le produit {$product->getName()} a bien été supprimé !");
            return $this->redirectToRoute('cart');
        }else{
            throw new InvalidCsrfTokenException();
        }
        
    }

    /**
     * @Route("/cart/update/{id}", name="cart.update")
     */
    public function update(SessionInterface $session, Request $request, ProductRepository $productRepository): Response
    {

        $id = $request->attributes->get('id');
        $product = $productRepository->find($id);
        if (!$product)
        {
            $this->addFlash('alert-error', "Le produit n'existe pas");
            return $this->redirectToRoute('cart');
        }
        $csrfToken = $_GET['token'];
        if ($this->isCsrfTokenValid('update-item', $csrfToken))
        {
            $cart = $session->get('panier', []);
            if(!isset($cart[$id])){
                $this->addFlash('alert-error', "Le produit n'est pas présent dans le panier");
                return $this->redirectToRoute('cart');
            }
            $quantity = $_GET['quantity'];
            if(!is_numeric($quantity) || !($quantity > 0 and $quantity < 11)){
                $this->addFlash('alert-error', "Erreur de l'ajout de la quantité");
                return $this->redirectToRoute('cart');
            }
            $cart[$id] = $quantity;
            $session->set('panier', $cart);

            $this->addFlash('alert-success', "La quantité du produit {$product->getName()} a bien été mise à jour !");
            return $this->redirectToRoute('cart');
        }else{
            throw new InvalidCsrfTokenException();
        }
        
    }
}
