<?php

namespace App\Services\Storefront;

use Illuminate\Database\Eloquent\Builder;
use Lunar\Models\Channel;
use Lunar\Models\CustomerGroup;

class StorefrontAvailabilityQuery
{
    public function apply(Builder $query, bool $forPurchase = false): Builder
    {
        $channel = Channel::query()->where('handle', 'webstore')->firstOrFail();
        $customerGroup = CustomerGroup::query()->where('default', true)->firstOrFail();
        $customerGroupPivot = $query->getModel()->customerGroups()->getTable();

        return $query
            ->whereHas('activeChannels', fn (Builder $channels) => $channels->whereKey($channel->id))
            ->whereHas('customerGroups', function (Builder $groups) use (
                $customerGroup,
                $customerGroupPivot,
                $forPurchase,
            ): void {
                $groups
                    ->whereKey($customerGroup->id)
                    ->where("{$customerGroupPivot}.enabled", true)
                    ->where("{$customerGroupPivot}.visible", true)
                    ->where(function (Builder $schedule) use ($customerGroupPivot): void {
                        $schedule->whereNull("{$customerGroupPivot}.starts_at")
                            ->orWhere("{$customerGroupPivot}.starts_at", '<=', now());
                    })
                    ->where(function (Builder $schedule) use ($customerGroupPivot): void {
                        $schedule->whereNull("{$customerGroupPivot}.ends_at")
                            ->orWhere("{$customerGroupPivot}.ends_at", '>=', now());
                    });

                if ($forPurchase) {
                    $groups->where("{$customerGroupPivot}.purchasable", true);
                }
            });
    }
}
