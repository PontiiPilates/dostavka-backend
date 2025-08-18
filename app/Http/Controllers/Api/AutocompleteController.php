<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AutocompleteRequest;
use App\Models\Location;

class AutocompleteController extends Controller
{
    public function handle(AutocompleteRequest $request)
    {
        $locations = Location::where('name', 'like', $request->name . "%")
            ->with('region')
            ->with('district')
            ->with('country')
            ->get();

        $dataset = [];

        foreach ($locations as $location) {

            $id = $location->id;
            $name = $location->name;
            $type = $location->type;
            $district = $location->district->name ?? null;
            $region = $location->region->name ?? null;
            $country = mb_ucfirst(mb_strtolower($location->country->name));

            if ($name == $region) {
                $dataset[$id] = "$name ($type), $country";
                continue;
            }

            if (!$district && $region) {
                $dataset[$id] = "$name ($type), $region, $country";
                continue;
            }

            if (!$region && $district) {
                $dataset[$id] = "$name ($type), $district, $country";
                continue;
            }

            if ($region && $district) {
                $dataset[$id] = "$name ($type), $district, $region, $country";
                continue;
            }

            if (!$region && !$district) {
                $dataset[$id] = "$name ($type), $country";
                continue;
            }
        }

        return response()->json([
            "success" => true,
            "message" => "",
            "data" => $dataset
        ]);
    }
}
