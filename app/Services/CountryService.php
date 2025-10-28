<?php

namespace App\Services;

use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Laravel\Facades\Image;

class CountryService
{
    public function refreshCountries()
    {
        DB::beginTransaction();
        try {
            $countriesResp = Http::timeout(10)->get('https://restcountries.com/v2/all?fields=name,capital,region,population,flag,currencies');
            if (! $countriesResp->successful()) {
                return $this->apiUnavailable('Countries API');
            }

            $exchangeResp = Http::timeout(10)->get('https://open.er-api.com/v6/latest/USD');
            if (! $exchangeResp->successful()) {
                return $this->apiUnavailable('Exchange Rates API');
            }

            $exchangeRates = $exchangeResp->json()['rates'];

            foreach ($countriesResp->json() as $data) {
                $currencyCode = $data['currencies'][0]['code'] ?? null;
                $exchangeRate = $currencyCode && isset($exchangeRates[$currencyCode]) ? $exchangeRates[$currencyCode] : null;
                $estimatedGdp = $data['population'] && $exchangeRate ? ($data['population'] * rand(1000, 2000)) / $exchangeRate : 0;

                Country::updateOrCreate(
                    ['name' => $data['name']],
                    [
                        'capital' => $data['capital'] ?? null,
                        'region' => $data['region'] ?? null,
                        'population' => $data['population'] ?? 0,
                        'currency_code' => $currencyCode,
                        'exchange_rate' => $exchangeRate,
                        'estimated_gdp' => $estimatedGdp,
                        'flag_url' => $data['flag'] ?? null,
                        'last_refreshed_at' => Carbon::now(),
                    ]
                );
            }

            $this->generateSummaryImage();

            DB::commit();

            return ['success' => true];

        } catch (\Exception $e) {
            DB::rollBack();

            return ['error' => 'Internal server error', 'details' => $e->getMessage()];
        }
    }

    private function apiUnavailable($api)
    {
        return response()->json([
            'error' => 'External data source unavailable',
            'details' => "Could not fetch data from $api",
        ], 503);
    }

    private function generateSummaryImage()
    {
        $countries = Country::orderByDesc('estimated_gdp')->get();
        $total = $countries->count();
        $top5 = $countries->take(5);

        $img = Image::create(800, 600)->fill('#ffffff');
        $img->text('Countries Summary', 400, 50, function ($font) {
            $font->file(public_path('fonts/Roboto-Regular.ttf'));
            $font->size(36);
            $font->align('center');
        });

        $img->text("Total Countries: $total", 20, 100, function ($font) {
            $font->file(public_path('fonts/Roboto-Regular.ttf'));
            $font->size(24);
            $font->color('#222222');
        });
        $y = 140;
        foreach ($top5 as $country) {
            $img->text("{$country->name} - GDP: {$country->estimated_gdp}", 20, $y, function ($font) {
                $font->file(public_path('fonts/Roboto-Regular.ttf'));
                $font->size(24);
                $font->color('#222222');
            });
            $y += 40;
        }
        $img->text('Last Refreshed: '.now(), 20, $y, function ($font) {
            $font->file(public_path('fonts/Roboto-Regular.ttf'));
            $font->size(24);
            $font->color('#222222');
        });

        if (! file_exists(storage_path('app/cache'))) {
            mkdir(storage_path('app/cache'), 0755, true);
        }

        $img->save(storage_path('app/cache/summary.png'));
    }
}
