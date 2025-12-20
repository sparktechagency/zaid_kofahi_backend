<?php

namespace App\Services\Player;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NearMeService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function nearMeEvents1(float $userLat, float $userLng,?int $limit)
    {
        $events = Event::query()
            ->select([
                'id',
                'organizer_id',
                'title',
                'latitude',
                'longitude',
                'image',
                'location',
                'sport_type',
                'sport_name',
                DB::raw("
                (6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(latitude))
                )) AS distance
            ")
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->addBinding([$userLat, $userLng, $userLat], 'select')
            ->orderBy('distance', 'asc')
            ->limit($limit??10)
            ->latest()
            ->get()
            ->map(function ($event) use ($userLat, $userLng) {
                return [
                    'id' => $event->id,
                    'organizer_id' => $event->organizer_id,
                    'title' => $event->title,
                    'latitude' => $event->latitude,
                    'longitude' => $event->longitude,
                    'image' => $event->image,
                    'location' => $event->location,
                    'sport_type' => $event->sport_type,
                    'sport_name' => $event->sport_name,
                    'distance_km' => round($event->distance, 2),
                ];

            });

        return [
            'current' => [
                'latitude' => $userLat,
                'longitude' => $userLng,
            ],
            'evnets' => $events,
        ];
    }

    public function nearMeEvents(
    float $userLat,
    float $userLng,
    ?int $limit,
    ?string $search = null
) {
    $events = Event::query()
        ->select([
            'id',
            'organizer_id',
            'title',
            'latitude',
            'longitude',
            'image',
            'location',
            'sport_type',
            'sport_name',
            DB::raw("
                (6371 * acos(
                    cos(radians(?))
                    * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?))
                    * sin(radians(latitude))
                )) AS distance
            ")
        ])
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')

        /* 🔍 SEARCH */
        ->when($search, function ($q) use ($search) {
            $q->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('location', 'LIKE', "%{$search}%")
                      ->orWhere('sport_name', 'LIKE', "%{$search}%");
            });
        })

        ->addBinding([$userLat, $userLng, $userLat], 'select')
        ->orderBy('distance', 'asc')
        ->limit($limit ?? 10)
        ->get()
        ->map(function ($event) {
            return [
                'id'            => $event->id,
                'organizer_id'  => $event->organizer_id,
                'title'         => $event->title,
                'sport_type'    => $event->sport_type,
                'sport_name'    => $event->sport_name,
                'image'         => $event->image,
                'location'      => $event->location,
                'latitude'      => $event->latitude,
                'longitude'     => $event->longitude,
                'distance_km'   => round($event->distance, 2),
            ];
        });

    return [
        'current' => [
            'latitude'  => $userLat,
            'longitude' => $userLng,
        ],
        'events' => $events,
    ];
}

}
