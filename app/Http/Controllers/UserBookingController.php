<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DatVe;

class UserBookingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        $datVes = DatVe::with(['suatChieu.phim', 'chiTietDatVe.ghe', 'chiTietCombo.combo'])
            ->where('id_nguoi_dung', $user->id)
            ->orderByDesc('id')
            ->paginate(7);
        return view('user.booking_history', compact('datVes'));
    }
}
