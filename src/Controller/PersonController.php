<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RandomFact;
use App\Form\PersonType;
use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    public function __construct(private MailService $mailService)
    {
    }

    #[Route('/person', name: 'app_person')]
    public function index(Request $request): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $fact = new RandomFact();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $to = $person->getEmail();
            $subject = 'Успешная отправка формы';
            $body = "Пользователь отправил форму: \n\nИмя: {$person->getName()}";

            $this->mailService->sendEmail($to, $subject, $body);

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
