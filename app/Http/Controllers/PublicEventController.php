<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\CMS\Services\PublicSiteCopyService;
use App\Domains\Events\Services\PublicEventCatalogueService;
use App\Domains\Ticketing\Contracts\TicketingProvider;
use App\Http\Requests\PublicEventIndexRequest;
use App\Http\Requests\PublicEventShowRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicEventController extends Controller
{
    public function __construct(
        private readonly PublicEventCatalogueService $catalogue,
        private readonly PublicSiteCopyService $publicSiteCopy,
        private readonly TicketingProvider $ticketingProvider,
    ) {}

    public function index(PublicEventIndexRequest $request): View
    {
        $filters = $request->filters();

        return view('events.index', [
            'events' => $this->catalogue->paginateUpcoming($filters),
            'filters' => $filters,
            'filterOptions' => $this->catalogue->filterOptions(),
            'siteCopy' => $this->publicSiteCopy->current(),
            'customerSession' => $this->ticketingProvider->customerSession(),
        ]);
    }

    public function show(PublicEventShowRequest $request, string $slug): View|RedirectResponse
    {
        $event = $this->catalogue->findBySlug($slug);

        if ($event !== null) {
            $filters = $request->filters();

            return view('events.show', [
                'event' => $event,
                'performanceListing' => $this->catalogue->performanceListing($event, $filters),
                'performanceFilters' => $filters,
                'bookingPerformance' => $event->bookingPerformance($request->performanceId()),
                'siteCopy' => $this->publicSiteCopy->current(),
                'customerSession' => $this->ticketingProvider->customerSession(),
            ]);
        }

        $redirect = $this->catalogue->redirectForPath($request->path());

        if ($redirect !== null) {
            return redirect($redirect->destination_path, $redirect->status_code);
        }

        throw new NotFoundHttpException;
    }
}
