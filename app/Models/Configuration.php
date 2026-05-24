<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    public const GPS_RADIUS_TOLERANCE_KEY = 'gps_radius_tolerance';
    public const DEFAULT_GPS_RADIUS_TOLERANCE = 100;

    protected $table = 'configurations';

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get configuration by key
     */
    public static function getValue($key, $default = null)
    {
        $config = static::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        // Cast value based on type
        switch ($config->type) {
            case 'integer':
                return (int) $config->value;
            case 'boolean':
                return filter_var($config->value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($config->value, true);
            default:
                return $config->value;
        }
    }

    /**
     * Set configuration value
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) && $type === 'json' ? json_encode($value) : $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get GPS radius tolerance for check-in validation, in meters.
     */
    public static function getGpsRadiusTolerance(): int
    {
        return static::getValue(
            self::GPS_RADIUS_TOLERANCE_KEY,
            self::DEFAULT_GPS_RADIUS_TOLERANCE
        );
    }
}
