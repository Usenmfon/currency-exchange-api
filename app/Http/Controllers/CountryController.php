<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $service;

    public function __construct(CountryService $service)
    {
        $this->service = $service;
    }

    public function refresh()
    {
        $result = $this->service->refreshCountries();

        return response()->json($result);
    }

    public function index(Request $request)
    {
        $query = Country::query();
        if ($request->has('region')) {
            $query->where('region', $request->region);
        }
        if ($request->has('currency')) {
            $query->where('currency_code', $request->currency);
        }
        if ($request->has('sort')) {
            $query->orderByDesc('estimated_gdp') && $request->sort == 'gdp_desc';
            $query->orderBy('estimated_gdp') && $request->sort == 'gdp_asc';
        }

        return response()->json($query->get());
    }

    public function show($name)
    {
        $country = Country::where('name', $name)->first();
        if (! $country) {
            return response()->json(['error' => 'Country not found'], 404);
        }

        return response()->json($country);
    }

    public function destroy($name)
    {
        $country = Country::where('name', $name)->first();
        if (! $country) {
            return response()->json(['error' => 'Country not found'], 404);
        }
        $country->delete();

        return response()->json(['message' => 'Country deleted']);
    }

    public function status()
    {
        $lastRefreshed = Country::max('last_refreshed_at');

        return response()->json([
            'total_countries' => Country::count(),
            'last_refreshed_at' => $lastRefreshed
                ? \Carbon\Carbon::parse($lastRefreshed)->toISOString()
                : null,
        ]);
    }

    public function image()
    {
        $path = storage_path('app/cache/summary.png');
        if (! file_exists($path)) {
            return response()->json(['error' => 'Summary image not found'], 404);
        }

        return response()->file($path);
    }
}
