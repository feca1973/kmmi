<?php

namespace App\Controller\Frontend;

use App\Enum\MediaType;
use App\Repository\MediaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The audio library (/hangtar) and the video library (/videotar).
 */
final class MediaController extends PagedController
{
    #[Route('/hangtar', name: 'audio_list', methods: ['GET'])]
    public function audioList(Request $request, MediaRepository $media): Response
    {
        return $this->list(MediaType::Audio, 'Hangtár', $request, $media);
    }

    #[Route('/hangtar/{slug}', name: 'audio_single', methods: ['GET'])]
    public function audioSingle(string $slug, MediaRepository $media): Response
    {
        return $this->single($slug, 'Hangtár', $media);
    }

    #[Route('/videotar', name: 'video_list', methods: ['GET'])]
    public function videoList(Request $request, MediaRepository $media): Response
    {
        return $this->list(MediaType::Video, 'Videótár', $request, $media);
    }

    #[Route('/videotar/{slug}', name: 'video_single', methods: ['GET'])]
    public function videoSingle(string $slug, MediaRepository $media): Response
    {
        return $this->single($slug, 'Videótár', $media);
    }

    private function list(MediaType $type, string $title, Request $request, MediaRepository $media): Response
    {
        return $this->render('frontend/media/list.html.twig', [
            'appointments' => $this->paginate($media->queryActiveByType($type), $request),
            'typeTitle' => $title,
        ]);
    }

    private function single(string $slug, string $title, MediaRepository $media): Response
    {
        $item = $media->findActiveBySlug($slug);
        if ($item === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk, vagy nincs kapcsolódó tartalom');
        }

        return $this->render('frontend/media/single.html.twig', ['media' => $item, 'typeTitle' => $title]);
    }
}
