<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::where('vendor_id', $request->user()->id)
            ->latest()
            ->get();

        return Inertia::render('Vendor/Coupons/Index', [
            'coupons' => $coupons,
        ]);
    }

    public function create()
    {
        return Inertia::render('Vendor/Coupons/Create');
    }

    public function store(StoreCouponRequest $request)
    {
        Coupon::create([
            ...$request->validated(),
            'vendor_id' => $request->user()->id,
        ]);

        return redirect()->route('vendor.coupons.index')
            ->with('success', 'Coupon created successfully');
    }

    public function destroy(Coupon $coupon)
    {
        abort_unless($coupon->vendor_id === auth()->id(), 403);

        $coupon->delete();

        return redirect()->route('vendor.coupons.index')
            ->with('success', 'Coupon deleted successfully');
    }

    public function toggle(Coupon $coupon)
    {
        abort_unless($coupon->vendor_id === auth()->id(), 403);

        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', 'Coupon status updated');
    }
}
