<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatecityList extends Model
{
    public $timestamps = false;

    protected $table = 'statecity_list';

    protected $fillable = [
        'state',
        'district',
        'taluka',
        'village',
    ];

    /**
     * @return array<string, string>
    */
    public static function stateOptions(): array
    {
        return static::query()
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state', 'state')
            ->mapWithKeys(fn ($state, $key) => [
                $key => ucwords(strtolower($state))
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function districtOptions(?string $state): array
    {
        if ($state === null || $state === '') {
            return [];
        }

        return static::query()
            ->where('state', $state)
            ->select('district')
            ->distinct()
            ->orderBy('district')
            ->pluck('district', 'district')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function talukaOptions(?string $state, ?string $district): array
    {
        if ($state === null || $state === '' || $district === null || $district === '') {
            return [];
        }

        return static::query()
            ->where('state', $state)
            ->where('district', $district)
            ->select('taluka')
            ->distinct()
            ->orderBy('taluka')
            ->pluck('taluka', 'taluka')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function villageOptions(?string $state, ?string $district, ?string $taluka): array
    {
        if (
            $state === null || $state === ''
            || $district === null || $district === ''
            || $taluka === null || $taluka === ''
        ) {
            return [];
        }

        return static::query()
            ->where('state', $state)
            ->where('district', $district)
            ->where('taluka', $taluka)
            ->select('village')
            ->distinct()
            ->orderBy('village')
            ->pluck('village', 'village')
            ->all();
    }
}
