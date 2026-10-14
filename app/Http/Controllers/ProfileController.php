<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Services\Order\OrderViewService;
use Lunar\Models\Order;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, OrderViewService $orderView): View
    {
        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->whereNotNull('reference')
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(fn (Order $order) => $orderView->map($order));

        return view('profile.edit', [
            'user' => $request->user(),
            'orders' => $orders,
            'ordersCount' => Order::query()
                ->where('user_id', $request->user()->id)
                ->whereNotNull('reference')
                ->count(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
