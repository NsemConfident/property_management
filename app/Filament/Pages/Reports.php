<?php

namespace App\Filament\Pages;

use App\Services\ReportService;
use Filament\Pages\Page;

class Reports extends Page
{
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-chart-bar';
    }

    protected string $view = 'filament.pages.reports';

    public static function getNavigationGroup(): ?string
    {
        return 'Reports';
    }

    protected static ?int $navigationSort = 1;

    public $startDate;
    public $endDate;
    public $propertyId = null;
    public $selectedReport = 'revenue';

    protected ReportService $reportService;

    public function boot(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function getRevenueData()
    {
        return $this->reportService->getRevenueReport([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'property_id' => $this->propertyId,
        ]);
    }

    public function getRevenueByProperty()
    {
        return $this->reportService->getRevenueByProperty([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }

    public function getOccupancyData()
    {
        return $this->reportService->getOccupancyRates([
            'property_id' => $this->propertyId,
        ]);
    }

    public function getPaymentHistory()
    {
        return $this->reportService->getPaymentHistory([
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'property_id' => $this->propertyId,
        ]);
    }

    public function getOverdueData()
    {
        return $this->reportService->getOverdueInvoices([
            'property_id' => $this->propertyId,
        ]);
    }

    public function getProperties()
    {
        return \App\Models\Property::all();
    }
}

