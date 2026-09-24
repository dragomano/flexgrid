<?php

declare(strict_types=1);

namespace FlexGrid\Benchmarks;

/**
 * Minimal, dependency-free benchmark helpers.
 *
 * Runs each scenario several times and reports the best wall-clock time
 * (least noisy sample) together with peak memory for the process.
 */
final class Bench
{
    /** @var list<array{label: string, ms: float, extra: string}> */
    private array $rows = [];

    public function __construct(private readonly int $reps = 7) {}

    public function run(string $label, callable $fn, string $extra = ''): mixed
    {
        $best   = PHP_FLOAT_MAX;
        $result = null;

        for ($i = 0; $i < $this->reps; $i++) {
            $start  = hrtime(true);
            $result = $fn();
            $best   = min($best, (hrtime(true) - $start) / 1e6);
        }

        $this->rows[] = ['label' => $label, 'ms' => $best, 'extra' => $extra];

        return $result;
    }

    public function section(string $title): void
    {
        echo "\n== $title ==\n";
    }

    public function report(): void
    {
        foreach ($this->rows as $row) {
            printf("  %-46s %9.3f ms   %s\n", $row['label'], $row['ms'], $row['extra']);
        }

        $this->rows = [];
    }

    public static function peakMemory(): string
    {
        return sprintf('%.1f MB peak', memory_get_peak_usage(true) / 1024 / 1024);
    }
}
