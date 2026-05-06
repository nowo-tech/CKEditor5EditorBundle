<?php

declare(strict_types=1);

namespace App\Controller;

use Nowo\Ckeditor5EditorBundle\Form\Ckeditor5EditorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

use function in_array;

/**
 * CKEditor 5 SimpleUploadAdapter endpoint — multipart field "upload", CSRF in X-CSRF-TOKEN.
 */
final class CkeditorUploadController extends AbstractController
{
    #[Route('/upload/ckeditor', name: 'app_ckeditor_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $token = (string) $request->headers->get('X-CSRF-TOKEN', '');
        if (!$this->isCsrfTokenValid(Ckeditor5EditorType::CSRF_UPLOAD_TOKEN_ID, $token)) {
            return new JsonResponse(['error' => ['message' => 'Invalid CSRF token']], 403);
        }

        /** @var UploadedFile|null $file */
        $file = $request->files->get('upload');
        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return new JsonResponse(['error' => ['message' => 'No valid upload']], 400);
        }

        $mime    = (string) $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            return new JsonResponse(['error' => ['message' => 'Unsupported image type']], 415);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return new JsonResponse(['error' => ['message' => 'Max 5 MB']], 413);
        }

        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $dir        = $projectDir . '/public/uploads/ckeditor';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $ext  = $file->guessExtension() ?: 'bin';
        $safe = bin2hex(random_bytes(16)) . '.' . $ext;

        try {
            $file->move($dir, $safe);
        } catch (FileException) {
            return new JsonResponse(['error' => ['message' => 'Could not store file']], 500);
        }

        $path = '/uploads/ckeditor/' . $safe;

        return new JsonResponse([
            'url' => $request->getSchemeAndHttpHost() . $path,
        ]);
    }
}
