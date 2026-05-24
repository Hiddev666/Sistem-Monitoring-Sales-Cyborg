<?php

namespace App\Services;

class GpsValidationService
{
    const EARTH_RADIUS = 6371000; // meters

    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     *
     * @param float $lat1 - Current latitude
     * @param float $lng1 - Current longitude
     * @param float $lat2 - Target latitude
     * @param float $lng2 - Target longitude
     * @return float distance in meters
     */
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS * $c;
    }

    /**
     * Validate if current location is within tolerance of target
     *
     * @param float $currentLat
     * @param float $currentLng
     * @param float $targetLat
     * @param float $targetLng
     * @param int $toleranceMeters - Default 100 meters
     * @return array validation result with status, distance, message
     */
    public function validateCheckIn($currentLat, $currentLng, $targetLat, $targetLng, $toleranceMeters = 100)
    {
        try {
            // Validate coordinate ranges
            if (!$this->isValidCoordinate($currentLat, $currentLng)) {
                return [
                    'valid' => false,
                    'distance' => null,
                    'message' => 'Koordinat GPS tidak valid. Silahkan coba lagi.'
                ];
            }

            if (!$this->isValidCoordinate($targetLat, $targetLng)) {
                return [
                    'valid' => false,
                    'distance' => null,
                    'message' => 'Lokasi target tidak valid. Hubungi administrator.'
                ];
            }

            $distance = $this->calculateDistance($currentLat, $currentLng, $targetLat, $targetLng);

            return [
                'valid' => $distance <= $toleranceMeters,
                'distance' => round($distance, 2),
                'message' => $this->buildMessage($distance, $toleranceMeters),
                'tolerance' => $toleranceMeters,
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'distance' => null,
                'message' => 'Terjadi kesalahan dalam validasi GPS. ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if coordinates are within valid range
     *
     * @param float $lat - Latitude (-90 to 90)
     * @param float $lng - Longitude (-180 to 180)
     * @return bool
     */
    public function isValidCoordinate($lat, $lng)
    {
        return is_numeric($lat) && is_numeric($lng) &&
            $lat >= -90 && $lat <= 90 &&
            $lng >= -180 && $lng <= 180;
    }

    /**
     * Build user-friendly validation message
     *
     * @param float $distance
     * @param int $tolerance
     * @return string
     */
    private function buildMessage($distance, $tolerance)
    {
        if ($distance <= $tolerance) {
            return 'Lokasi Anda valid. Silahkan check-in.';
        } else {
            $diff = round($distance - $tolerance, 2);
            return "Anda masih {$diff}m dari target. Toleransi: {$tolerance}m. Harap mendekati lokasi target.";
        }
    }

    /**
     * Get human-readable accuracy level based on GPS accuracy
     *
     * @param float $accuracy - Accuracy in meters
     * @return string accuracy level
     */
    public function getAccuracyLevel($accuracy)
    {
        if ($accuracy <= 5) return 'Sangat Akurat';
        if ($accuracy <= 10) return 'Akurat';
        if ($accuracy <= 20) return 'Cukup Akurat';
        if ($accuracy <= 50) return 'Kurang Akurat';
        return 'Tidak Akurat';
    }

    /**
     * Get distance between two points in kilometers
     *
     * @param float $lat1, $lng1, $lat2, $lng2
     * @return float distance in kilometers
     */
    public function getDistanceKm($lat1, $lng1, $lat2, $lng2)
    {
        return round($this->calculateDistance($lat1, $lng1, $lat2, $lng2) / 1000, 2);
    }

    /**
     * Validate if user is within proximity of multiple klien
     *
     * @param float $userLat, $userLng
     * @param array $klienList - Array of klien with 'latitude', 'longitude' keys
     * @param int $tolerance
     * @return array klien within range with distances
     */
    public function findNearbyKlien($userLat, $userLng, $klienList, $tolerance = 100)
    {
        $nearby = [];

        foreach ($klienList as $klien) {
            $distance = $this->calculateDistance(
                $userLat,
                $userLng,
                $klien['latitude'] ?? 0,
                $klien['longitude'] ?? 0
            );

            if ($distance <= $tolerance) {
                $klien['distance'] = round($distance, 2);
                $nearby[] = $klien;
            }
        }

        // Sort by distance
        usort($nearby, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return $nearby;
    }
}
