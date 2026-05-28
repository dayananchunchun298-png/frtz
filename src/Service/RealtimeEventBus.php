<?php

namespace App\Service;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Lightweight in-memory event log (Symfony cache) for mobile polling and admin live refresh.
 * Events use dotted names, e.g. appointment.created, inventory.stock.updated.
 */
final class RealtimeEventBus
{
    private const CACHE_KEY = 'fritz.realtime.events';
    private const MAX_EVENTS = 200;

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function publish(string $type, array $payload = []): void
    {
        $type = strtolower(trim($type));

        $item = $this->cache->getItem(self::CACHE_KEY);
        /** @var list<array{type: string, payload: array<string, mixed>, at: string}> $events */
        $events = $item->isHit() && \is_array($item->get()) ? $item->get() : [];

        $events[] = [
            'type' => $type,
            'payload' => $payload,
            'at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        if (\count($events) > self::MAX_EVENTS) {
            $events = \array_slice($events, -\self::MAX_EVENTS);
        }

        $item->set($events);
        $this->cache->save($item);
    }

    /**
     * @return list<array{type: string, payload: array<string, mixed>, at: string}>
     */
    public function getSince(?string $sinceIso): array
    {
        $item = $this->cache->getItem(self::CACHE_KEY);
        if (!$item->isHit() || !\is_array($item->get())) {
            return [];
        }

        /** @var list<array{type: string, payload: array<string, mixed>, at: string}> $events */
        $events = $item->get();

        if ($sinceIso === null || $sinceIso === '') {
            return $events;
        }

        $sinceTs = strtotime($sinceIso);
        if ($sinceTs === false) {
            return $events;
        }

        return array_values(array_filter(
            $events,
            static fn (array $event): bool => strtotime($event['at'] ?? '') > $sinceTs,
        ));
    }
}
