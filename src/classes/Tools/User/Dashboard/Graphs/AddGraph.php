<?php

namespace dhope0000\LXDClient\Tools\User\Dashboard\Graphs;

use dhope0000\LXDClient\Model\Users\Dashboard\Graphs\InsertDashboardGraph;

class AddGraph
{
    public function __construct(InsertDashboardGraph $insertDashboardGraph)
    {
        $this->insertDashboardGraph = $insertDashboardGraph;
    }

    public function add(
        int $userId,
        int $dashboardId,
        string $name,
        int $hostId,
        string $instance,
        int $metricId,
        string $filter
    ) {
        $this->insertDashboardGraph->insert(
            $dashboardId,
            $name,
            $hostId,
            $instance,
            $metricId,
            $filter
        );
    }
}
