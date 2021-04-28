<?php

namespace App\Handler;

use App\Entity\Transfer;
use App\Repository\TransferRepositoryInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class TransferHandler
{
    private $transferRepository;
    private $transferFilesystem;
    private $mailer;

    public function __construct(
        TransferRepositoryInterface $transferRepository,
        FilesystemInterface $transferFilesystem,
        MailerInterface $mailer
    ) {
        $this->transferFilesystem = $transferFilesystem;
        $this->transferRepository = $transferRepository;
        $this->mailer = $mailer;
    }

    /**
     * Create transfer.
     *
     * @return JsonResponse
     */
    public function createTransfer(FormInterface $form)
    {
        /** @var UploadedFile $file */
        $file = $form->get('file')->getData();

        $transfer = new Transfer();
        $transfer->setRecipient($form->get('recipient')->getData());
        $transfer->setFilename($file->getClientOriginalName());
        $transfer->setUploaded(false);
        $transfer->setSended(false);
        $this->transferRepository->save($transfer);

        try {
            $this->uploadFile($file, $transfer);
            $this->sendEmail($transfer);
        } catch (Exception $e) {
            if ($transfer->getUploaded()) {
                $this->transferFilesystem->deleteDir($transfer->getId());
            }

            return new JsonResponse($e->getMessage(), 400, []);
        }

        $this->transferFilesystem->deleteDir($transfer->getId());

        return new JsonResponse(null, 201, []);
    }

    /**
     * Upload file.
     */
    private function uploadFile(UploadedFile $file, Transfer $transfer)
    {
        $stream = fopen($file->getPathname(), 'r');

        $result = $this->transferFilesystem->writeStream(
            $transfer->getId().'/'.$file->getClientOriginalName(),
            $stream
        );

        if (false === $result) {
            throw new Exception(sprintf('Le fichier "%s" n\'a pas pu être écrit', $file->getClientOriginalName()));
        }

        if (is_resource($stream)) {
            fclose($stream);
        }

        $transfer->setUploaded(true);
        $this->transferRepository->save($transfer);
    }

    /**
     * Send uploaded file to recipient.
     */
    private function sendEmail(Transfer $transfer)
    {
        $filepath = $transfer->getId().'/'.$transfer->getFilename();
        $file = $this->transferFilesystem->read($filepath);

        $email = (new TemplatedEmail())
            ->from('noreply@wayatransfer.com')
            ->to($transfer->getRecipient())
            ->subject('Un fichier vous a été envoyé via WayaTransfer')
            ->text('Bonjour, Veuillez trouver en pièce le fichier '.$transfer->getFilename().' qui vous a été envoyé via WayaTransfer. Bonne réception. L\'équipe de WayaTransfer')
            ->htmlTemplate('emails/transfer.html.twig')
            ->context([
                'filename' => $transfer->getFilename(),
            ])
            ->attach($file, $transfer->getFilename())
        ;

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw new Exception(sprintf('L\'email destiné à "%s" n\'a pas pu être envoyé.', $transfer->getRecipient()));
        }

        $transfer->setSended(true);
        $this->transferRepository->save($transfer);
    }
}
