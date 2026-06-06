<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'cpu' => $this->getCpuUsage(),
            'ram' => $this->getRamUsage(),
            'disk' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
        ]);
    }

    private function getCpuUsage()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'N/A';
        }

        $load = sys_getloadavg();

        return round($load[0], 2);
    }

    private function getRamUsage()
    {
        if (!file_exists('/proc/meminfo')) {
            return 'N/A';
        }

        $meminfo = file_get_contents('/proc/meminfo');

        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);

        if (!$total || !$available) {
            return 'N/A';
        }

        $used = $total[1] - $available[1];
        $percent = ($used / $total[1]) * 100;

        return round($percent, 1);
    }

    private function getDiskUsage()
    {
        $total = disk_total_space('/');
        $free = disk_free_space('/');

        if (!$total || !$free) {
            return 'N/A';
        }

        $used = $total - $free;
        $percent = ($used / $total) * 100;

        return round($percent, 1);
    }

    private function getUptime()
    {
        if (!file_exists('/proc/uptime')) {
            return 'N/A';
        }

        $uptime = file_get_contents('/proc/uptime');
        $seconds = (int) explode(' ', $uptime)[0];

        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }
}