<?php

namespace Webkul\Admin\Helpers\Reporting;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Webkul\Core\Repositories\VisitorLogRepository;

class Visitor extends AbstractReporting
{
    /**
     * Create a helper instance.
     *
     * @return void
     */
    public function __construct(protected VisitorLogRepository $visitorLogRepository)
    {
        parent::__construct();
    }

    /**
     * Returns total unique visitors (by session) for current period vs previous period.
     */
    public function getTotalVisitorsProgress(): array
    {
        $current = $this->getTotalVisitors($this->startDate, $this->endDate);
        $previous = $this->getTotalVisitors($this->lastStartDate, $this->lastEndDate);

        return [
            'previous' => $previous,
            'current' => $current,
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Returns total unique (by session) page views for the period.
     */
    public function getTotalPageViewsProgress(): array
    {
        $current = $this->getTotalPageViews($this->startDate, $this->endDate);
        $previous = $this->getTotalPageViews($this->lastStartDate, $this->lastEndDate);

        return [
            'previous' => $previous,
            'current' => $current,
            'progress' => $this->getPercentageChange($previous, $current),
        ];
    }

    /**
     * Returns today's unique visitors count.
     */
    public function getTodayVisitors(): int
    {
        return $this->visitorLogRepository
            ->getModel()
            ->whereDate('created_at', today())
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Returns device breakdown for the current period.
     */
    public function getDeviceBreakdown(): array
    {
        return $this->visitorLogRepository
            ->getModel()
            ->select('device_type', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->device_type ?? 'unknown' => $row->count])
            ->toArray();
    }

    /**
     * Returns the top 10 most visited URLs in the current period.
     */
    public function getTopPages(int $limit = 10): array
    {
        return $this->visitorLogRepository
            ->getModel()
            ->select('url', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->groupBy('url')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Returns recent visitor log entries.
     */
    public function getRecentVisitors(int $limit = 20): Collection
    {
        return $this->visitorLogRepository
            ->getModel()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Returns visitor count between two dates.
     */
    protected function getTotalVisitors($start, $end): int
    {
        return $this->visitorLogRepository
            ->getModel()
            ->whereBetween('created_at', [$start, $end])
            ->distinct('session_id')
            ->count('session_id');
    }

    /**
     * Returns total page view count between two dates.
     */
    protected function getTotalPageViews($start, $end): int
    {
        return $this->visitorLogRepository
            ->getModel()
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }
}
