<?php

namespace App\Services;

class ChartDataService
{
    /**
     * Format data for pie chart
     */
    public function formatPieChart($data, $colors = null)
    {
        if (empty($colors)) {
            $colors = $this->getDefaultColors(count($data));
        }

        return [
            'labels' => array_keys($data),
            'datasets' => [
                [
                    'data' => array_values($data),
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    /**
     * Format data for bar chart
     */
    public function formatBarChart($data, $label = 'Total', $color = null)
    {
        if (empty($color)) {
            $color = 'rgba(59, 130, 246, 0.8)'; // Blue
        }

        $labels = [];
        $values = [];

        foreach ($data as $item) {
            if (is_array($item) || is_object($item)) {
                $item = (array) $item;
                $labels[] = $item['nama'] ?? $item['opd'] ?? 'Unknown';
                $values[] = $item['total'] ?? $item['bezetting'] ?? 0;
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $values,
                    'backgroundColor' => $color,
                    'borderColor' => str_replace('0.8', '1', $color),
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    /**
     * Format data for stacked bar chart (bezetting vs kebutuhan)
     */
    public function formatStackedBarChart($data)
    {
        $labels = [];
        $bezettingData = [];
        $kebutuhanData = [];

        foreach ($data as $item) {
            $item = (array) $item;
            $labels[] = $item['nama'] ?? $item['opd'] ?? 'Unknown';
            $bezettingData[] = $item['bezetting'] ?? 0;
            $kebutuhanData[] = $item['kebutuhan'] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Bezetting',
                    'data' => $bezettingData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.8)', // Green
                    'borderColor' => 'rgba(34, 197, 94, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Kebutuhan',
                    'data' => $kebutuhanData,
                    'backgroundColor' => 'rgba(249, 115, 22, 0.8)', // Orange
                    'borderColor' => 'rgba(249, 115, 22, 1)',
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    /**
     * Format data for donut chart
     */
    public function formatDonutChart($data, $colors = null)
    {
        return $this->formatPieChart($data, $colors);
    }

    /**
     * Format data for gauge chart (persentase pemenuhan)
     */
    public function formatGaugeChart($percentage)
    {
        // Determine color based on percentage
        $color = $this->getGaugeColor($percentage);

        return [
            'percentage' => $percentage,
            'color' => $color,
            'label' => $this->getGaugeLabel($percentage),
        ];
    }

    /**
     * Format data for heat map
     */
    public function formatHeatMapData($data)
    {
        // Convert to array if it's a Collection
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        return array_map(function ($item) {
            $item = (array) $item;
            return [
                'label' => $item['opd'] ?? 'Unknown',
                'value' => $item['selisih'] ?? 0,
                'percentage' => $item['persentase'] ?? 0,
                'bezetting' => $item['bezetting'] ?? 0,
                'kebutuhan' => $item['kebutuhan'] ?? 0,
                'color' => $this->getHeatMapColor($item['selisih'] ?? 0),
            ];
        }, $data);
    }

    /**
     * Get default color palette
     */
    private function getDefaultColors($count)
    {
        $palette = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(34, 197, 94, 0.8)',    // Green
            'rgba(249, 115, 22, 0.8)',   // Orange
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(168, 85, 247, 0.8)',   // Purple
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(14, 165, 233, 0.8)',   // Sky
            'rgba(245, 158, 11, 0.8)',   // Amber
            'rgba(16, 185, 129, 0.8)',   // Emerald
            'rgba(99, 102, 241, 0.8)',   // Indigo
        ];

        // Repeat colors if needed
        while (count($palette) < $count) {
            $palette = array_merge($palette, $palette);
        }

        return array_slice($palette, 0, $count);
    }

    /**
     * Get color for gauge based on percentage
     */
    private function getGaugeColor($percentage)
    {
        if ($percentage >= 90) {
            return '#22c55e'; // Green - Excellent
        } elseif ($percentage >= 75) {
            return '#3b82f6'; // Blue - Good
        } elseif ($percentage >= 50) {
            return '#f59e0b'; // Amber - Fair
        } elseif ($percentage >= 25) {
            return '#f97316'; // Orange - Poor
        } else {
            return '#ef4444'; // Red - Critical
        }
    }

    /**
     * Get label for gauge
     */
    private function getGaugeLabel($percentage)
    {
        if ($percentage >= 90) {
            return 'Sangat Baik';
        } elseif ($percentage >= 75) {
            return 'Baik';
        } elseif ($percentage >= 50) {
            return 'Cukup';
        } elseif ($percentage >= 25) {
            return 'Kurang';
        } else {
            return 'Sangat Kurang';
        }
    }

    /**
     * Get color for heat map based on selisih
     */
    private function getHeatMapColor($selisih)
    {
        if ($selisih > 10) {
            return '#3b82f6'; // Blue - Overstaffed
        } elseif ($selisih > 0) {
            return '#22c55e'; // Green - Slightly over
        } elseif ($selisih == 0) {
            return '#94a3b8'; // Gray - Perfect
        } elseif ($selisih >= -10) {
            return '#f59e0b'; // Amber - Slightly under
        } else {
            return '#ef4444'; // Red - Critical understaffing
        }
    }

    /**
     * Format comparison chart data
     */
    public function formatComparisonChart($label1, $data1, $label2, $data2, $labels)
    {
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label1,
                    'data' => $data1,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'borderWidth' => 1,
                ],
                [
                    'label' => $label2,
                    'data' => $data2,
                    'backgroundColor' => 'rgba(249, 115, 22, 0.8)',
                    'borderColor' => 'rgba(249, 115, 22, 1)',
                    'borderWidth' => 1,
                ]
            ]
        ];
    }

    /**
     * Format line chart data (for trends if needed)
     */
    public function formatLineChart($data, $label = 'Trend')
    {
        $labels = array_keys($data);
        $values = array_values($data);

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $values,
                    'borderColor' => 'rgba(59, 130, 246, 1)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'borderWidth' => 2,
                ]
            ]
        ];
    }
}
