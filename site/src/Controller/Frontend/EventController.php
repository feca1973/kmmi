<?php

namespace App\Controller\Frontend;

use App\Repository\Event\EventsRepository;
use App\Repository\Event\EventTypeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends PagedController
{
    #[Route('/rendezvenyek', name: 'event_list', methods: ['GET'])]
    public function list(Request $request, EventsRepository $events): Response
    {
        return $this->render('frontend/event/event_list.html.twig', [
            'appointments' => $this->paginate($events->queryActive(), $request),
            'type' => null,
        ]);
    }

    #[Route('/rendezvenyek/{type}', name: 'event_list_by_type', methods: ['GET'])]
    public function listByType(
        string $type,
        Request $request,
        EventsRepository $events,
        EventTypeRepository $types,
    ): Response {
        $eventType = $types->findOneBySlug($type);
        if ($eventType === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/event/event_list.html.twig', [
            'appointments' => $this->paginate($events->queryActive($type), $request),
            'type' => $eventType,
        ]);
    }

    #[Route('/rendezvenyek/{type}/{slug}', name: 'event_single', methods: ['GET'])]
    public function show(string $slug, EventsRepository $events): Response
    {
        $event = $events->findActiveBySlug($slug);
        if ($event === null) {
            throw $this->createNotFoundException('A keresett oldalt nem találtuk');
        }

        return $this->render('frontend/event/event_show.html.twig', ['event' => $event]);
    }
}
