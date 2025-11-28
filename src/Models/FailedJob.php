<?php

namespace DissNik\MoonShineQueueDashboard\Models;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class FailedJob extends Model
{
    public function getTable()
    {
        return config('queue.failed.table', 'failed_jobs');
    }

    protected function failedAgo(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => Carbon::parse($attributes['failed_at'])->diffForHumans()
        );
    }

    protected function exceptionMessage(): Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                try {
                    if (preg_match('/O:\d+:"[^"]+"/', $attributes['exception'])) {
                        if (preg_match('/"message";s:\d+:"([^"]+)";/', $attributes['exception'], $matches)) {
                            return $matches[1];
                        }
                    }

                    if (preg_match('/"message":"([^"]+)"/', $attributes['exception'], $matches)) {
                        return stripslashes($matches[1]);
                    }

                    $patterns = [
                        '/message\': \'([^\']+)\'/',
                        '/message": "([^"]+)"/',
                        '/Exception: ([^\n]+)/',
                        '/Error: ([^\n]+)/',
                    ];

                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $attributes['exception'], $matches)) {
                            return $matches[1] ?? $attributes['exception'];
                        }
                    }

                } catch (Exception) {
                }

                return $attributes['exception'];
            }
        );
    }
}
