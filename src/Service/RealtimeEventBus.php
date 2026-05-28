<?php

namespace App\Service;

/**
 * Append-only realtime event log (JSON file) for mobile polling and admin live refresh.
 * Events use dotted names, e.g. product.created, catalog.updated.
 */
final class RealtimeEventBus
{
    private const MAX_EVENTS = 200;

    public function __construct(
        private readonly string $projectDir,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function publish(string $type, array $payload = []): void
    {
        $type = strtolower(trim($type));
        $events = $this->readAll();
        $nextSeq = $events === [] ? 1 : ((int) ($events[\array_key_last($events)]['seq'] ?? 0)) + 1;

        $events[] = [
            'seq' => $nextSeq,
            'type' => $type,
            'payload' => $payload,
            'at' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        if (\count($events) > self::MAX_EVENTS) {
            $events = \array_slice($events, -\self::MAX_EVENTS);
        }

        $this->writeAll($events);
    }

    /**
     * @return list<array{seq: int, type: string, payload: array<string, mixed>, at: string}>
     */
    public function getAfter(int $afterSeq = 0): array
    {
        $events = $this->readAll();
        if ($afterSeq < 1) {
            return $events;
        }

        return array_values(array_filter(
            $events,
            static fn (array $event): bool => (int) ($event['seq'] ?? 0) > $afterSeq,
        ));
    }

    public function getLastSeq(): int
    {
        $events = $this->readAll();
        if ($events === []) {
            return 0;
        }

        return (int) ($events[\array_key_last($events)]['seq'] ?? 0);
    }

    /**
     * @return list<array{seq: int, type: string, payload: array<string, mixed>, at: string}>
     */
    private function readAll(): array
    {
        $path = $this->getStoragePath();
        if (!is_readable($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false || $raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!\is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(
            $decoded,
            static fn ($row): bool => \is_array($row) && isset($row['type'], $row['seq']),
        ));
    }

    /**
     * @param list<array<string, mixed>> $events
     */
    private function writeAll(array $events): void
    {
        $path = $this->getStoragePath();
        $dir = \dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents(
            $path,
            json_encode($events, \JSON_THROW_ON_ERROR),
            \LOCK_EX,
        );
    }

    private function getStoragePath(): string
    {
        return $this->projectDir.'/var/data/realtime-events.json';
    }
}
