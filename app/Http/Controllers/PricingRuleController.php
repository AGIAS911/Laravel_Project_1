<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PricingRuleController extends Controller
{
    public function index()
    {
        return response()->json(PricingRule::orderBy('id')->paginate(20));
    }

    public function store(Request $request)
    {
        return $this->save($request, new PricingRule);
    }

    public function update(Request $request, PricingRule $pricingRule)
    {
        return $this->save($request, $pricingRule);
    }

    public function destroy(PricingRule $pricingRule)
    {
        Cache::lock('moveit:pricing-rules', 10)->block(3, fn () => $pricingRule->delete());

        return response()->noContent();
    }

    private function save(Request $request, PricingRule $rule)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'day_of_week' => 'present|nullable|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'multiplier' => 'required|numeric|decimal:0,3|between:0.001,999.999',
            'is_active' => 'required|boolean',
        ]);

        return Cache::lock('moveit:pricing-rules', 10)->block(3, function () use ($data, $rule) {
            if ($data['is_active']) {
                $overlapping = PricingRule::where('is_active', true)
                    ->when($rule->exists, fn ($query) => $query->whereKeyNot($rule->id))
                    ->when($data['day_of_week'] !== null, fn ($query) => $query->where(
                        fn ($day) => $day->whereNull('day_of_week')->orWhere('day_of_week', $data['day_of_week'])
                    ))
                    ->where('start_time', '<', $data['end_time'].':00')
                    ->where('end_time', '>', $data['start_time'].':00')->exists();
                abort_if($overlapping, 422, 'An active pricing rule overlaps this time window.');
            }
            $status = $rule->exists ? 200 : 201;
            $rule->fill($data)->save();

            return response()->json($rule, $status);
        });
    }
}
