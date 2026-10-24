<?php

namespace App\Services;

use App\Models\Facility;
use Illuminate\Support\Facades\Validator;

class FacilityRoomSelection
{
    public function validate(array $rooms): array
    {
        $rooms = array_values(array_map(fn ($room) => is_array($room) ? [
            'location' => is_string($room['location'] ?? null) ? trim($room['location']) : null,
            'reason' => is_string($room['reason'] ?? null) ? trim($room['reason']) : null,
        ] : $room, $rooms));

        $rules = [
            'rooms' => 'required|array|min:1|max:10',
            'rooms.*' => 'required|array:location,reason',
            'rooms.*.location' => ['required', 'string', 'max:255', 'distinct:ignore_case', 'exists:facilities,name'],
        ];
        foreach ($rooms as $index => $room) {
            $rules['rooms.'.$index.'.reason'] = ($index === 0 ? 'nullable' : 'required').'|string|max:500';
        }
        Validator::make(['rooms' => $rooms], $rules, [
            'rooms.*.location.distinct' => 'Select each room only once.',
            'rooms.*.location.exists' => 'Please select a listed facility.',
            'rooms.*.reason.required' => 'Explain why you need each additional room.',
        ])->validate();

        // Preserve the facility's canonical spelling for booking comparisons.
        $names = Facility::whereIn('name', array_column($rooms, 'location'))->pluck('name')
            ->mapWithKeys(fn ($name) => [mb_strtolower($name) => $name]);
        return array_values(array_map(fn ($room) => ['location' => $names[mb_strtolower($room['location'])], 'reason' => $room['reason']], $rooms));
    }

    public function locationSummary(array $rooms): string
    {
        $summary = implode(', ', array_column($rooms, 'location'));
        $suffix = ' (+'.(count($rooms) - 1).' more rooms)';
        return mb_strlen($summary) <= 255 ? $summary : mb_substr($rooms[0]['location'], 0, 255 - mb_strlen($suffix)).$suffix;
    }

    public function description(string $description, array $rooms): string
    {
        $rooms = array_values(array_filter($rooms, fn ($room) => filled($room['reason'] ?? null)));
        if (! $rooms) return trim($description);
        return trim($description)."\n\nRoom use:\n".implode("\n", array_map(
            fn ($room) => $room['location'].': '.$room['reason'], $rooms
        ));
    }
}
