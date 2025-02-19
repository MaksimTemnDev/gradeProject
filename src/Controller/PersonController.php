<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RandomFact;
use App\Form\PersonType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    #[Route('/person', name: 'app_person')]
    public function index(Request $request): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $fact = new RandomFact();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Данные успешно отправлены и валидированы
            return $this->render('person/success.html.twig', [
                'person' => $person,
                'fact' => $fact->getRandomFact()
            ]);
        }

        return $this->render('person/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
