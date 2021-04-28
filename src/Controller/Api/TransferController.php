<?php

namespace App\Controller\Api;

use App\Entity\Transfer;
use App\Form\TransferType;
use App\Handler\TransferHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class TransferController extends AbstractController
{
    /**
     * @Route("/transfers", name="transfer.create", methods={"POST"})
     */
    public function createTransfer(Request $request, TransferHandler $transferHandler): JsonResponse
    {
        $transfer = new Transfer();
        /**
         * @var FormInterface $form
         */
        $form = $this->createForm(TransferType::class, $transfer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var JsonResponse */
            $jsonResponse = $transferHandler->createTransfer($form);
        } else {
            $errors = [];
            foreach ($form->getErrors(true, false) as $error) {
                $errors[] = $error->current()->getMessage();
            }

            /** @var JsonResponse */
            $jsonResponse = $this->json($errors, 400, []);
        }

        return $jsonResponse;
    }
}
