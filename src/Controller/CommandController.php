<?php

namespace App\Controller;

use App\Repository\CommandRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandController extends AbstractController
{
    /**
     * @Route("/command", name="command")
     */
    public function index(CommandRepository $commandRepository): Response
    {
        $commands = $commandRepository->findAll();

        return $this->render('command/index.html.twig', [
            'commands' => $commands,
        ]);
    }

    /**
     * @Route("/command/{id}", name="command.show")
     */
    public function show(CommandRepository $commandRepository, Request $request): Response
    {
        $id = $request->attributes->get('id');
        $command = $commandRepository->find($id);
        $products = $command->getProducts()->toArray();
        $nbProduits = count($products);
        $total = 0;
        foreach ($products as $product) {
            $total += $product->getPrice();
        }
        if (!$command)
        {
            throw $this->createNotFoundException('The command does not exist');
        }

        return $this->render('command/show.html.twig', [
            'command' => $command,
            'products' => $products,
            'total' => $total,
            'nbProduits' => $nbProduits,
        ]);
    }
}
