<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RandomFact;
use App\Exception\Custom\GradeException;
use App\Form\PersonType;
use App\Service\MailService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    private string $errorLogPath = '/var/log/errors.log';

    public function __construct(
        private MailService $mailService,
        private LoggerInterface $logger
    ) {
    }

    #[Route('/person', name: 'app_person')]
    public function index(Request $request): Response
    {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $fact = new RandomFact();

        $form->handleRequest($request);

        try {
            if ($form->isSubmitted()) {
                if (!$form->isValid()) {
                    $this->logFormErrors($form);
                    throw new GradeException($form->getErrors(true));
                }

                $to = $person->getEmail();
                $subject = 'Успешная отправка формы';
                $body = "Пользователь отправил форму: \n\nИмя: {$person->getName()}";

                $this->mailService->sendEmail($to, $subject, $body);

                $this->logger->info('Форма успешно отправлена', [
                    'email' => $to,
                    'person' => $person->getName()
                ]);

                return $this->render('person/success.html.twig', [
                    'person' => $person,
                    'fact' => $fact->getRandomFact(),
                ]);
            }
        } catch (GradeException $e) {
            $this->logError($e, [
                'form_data' => $request->request->all(),
                'validation_errors' => $this->getFormErrors($form)
            ]);

            $this->addFlash('error', 'Ошибка валидации формы: '.$e->getMessage());
        } catch (\Exception $e) {
            $this->logError($e, [
                'form_data' => $request->request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->addFlash('error', 'Произошла ошибка при обработке формы');
        }

        return $this->render('person/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function logError(\Throwable $e, array $context = []): void
    {
        $this->logger->error($e->getMessage(), [
            'exception' => $e,
            'context' => $context
        ]);

        $logEntry = sprintf(
            "[%s] %s: %s\nFile: %s:%d\nContext: %s\nTrace:\n%s\n\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            json_encode($context),
            $e->getTraceAsString()
        );

        file_put_contents($this->errorLogPath, $logEntry, FILE_APPEND);
    }

    private function logFormErrors($form): void
    {
        $errors = $this->getFormErrors($form);
        $this->logger->debug('Form validation errors', ['errors' => $errors]);
    }

    private function getFormErrors($form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = [
                'field' => $error->getOrigin()?->getName(),
                'message' => $error->getMessage()
            ];
        }
        return $errors;
    }
}