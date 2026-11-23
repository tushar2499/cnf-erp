<?php

namespace App\Services\Accounting\Sources;

use InvalidArgumentException;

/**
 * Every operational document type that can be posted to accounts.
 * Adding a new document type = one AccountingSource class + one line here.
 */
class SourceRegistry
{
    /** @var list<class-string<AccountingSource>> */
    private const SOURCES = [
        Chevron\BillSource::class,
        Chevron\JobExpenseSource::class,
        Chevron\MoneyReceiptSource::class,
        NasFreights\CustomerBillSource::class,
        NasFreights\SupplierBillSource::class,
        NasFreights\MoneyReceiptSource::class,
        NasFreights\SupplierPaymentSource::class,
        NasFreights\BookingExpenseSource::class,
        NasFreights\ExportBookingBillSource::class,
        NasTrading\CustomerBillSource::class,
        NasTrading\MoneyReceiptSource::class,
        NasTrading\LcExpenseSource::class,
        NasTrading\LcPaymentSource::class,
        NasTrading\LcBillPaidSource::class,
        NasTrading\LcDutyAdvanceSource::class,
    ];

    /** @var array<string, AccountingSource>|null */
    private ?array $sources = null;

    /**
     * @param  list<class-string<AccountingSource>>|null  $classes  Limit the registry (tests); defaults to every source.
     */
    public function __construct(private ?array $classes = null) {}

    /**
     * @return array<string, AccountingSource>
     */
    public function all(): array
    {
        if ($this->sources === null) {
            $this->sources = [];
            foreach ($this->classes ?? self::SOURCES as $class) {
                $source = app($class);
                $this->sources[$source->key()] = $source;
            }
        }

        return $this->sources;
    }

    /**
     * @return array<string, AccountingSource>
     */
    public function forCompany(string $slug): array
    {
        return array_filter($this->all(), fn (AccountingSource $s) => $s->companySlug() === $slug);
    }

    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }

    public function get(string $key): AccountingSource
    {
        return $this->all()[$key] ?? throw new InvalidArgumentException("Unknown accounting source [{$key}].");
    }
}
