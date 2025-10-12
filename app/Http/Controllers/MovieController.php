<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    /**
     * Display a listing of movies
     * Admin: Can see all movies with CRUD actions
     * Staff: Can only view movies (read-only)
     */
    public function index()
    {
        $movies = Phim::orderBy('id', 'desc')->paginate(10);
        
        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Search movies by name, ID, or director
     * Admin and Staff can access
     */
    public function search(Request $request)
    {
        $searchTerm = $request->get('search');
        
        if (empty($searchTerm)) {
            return redirect()->route('admin.movies.index');
        }

        $movies = Phim::where(function($query) use ($searchTerm) {
            $query->where('ten_phim', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('dao_dien', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('id', 'LIKE', "%{$searchTerm}%");
        })->orderBy('id', 'desc')->paginate(10);

        // Keep search term in pagination links
        $movies->appends(['search' => $searchTerm]);
        
        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Show the form for creating a new movie
     * Only Admin can access
     */
    public function create()
    {
        return view('admin.movies.create');
    }

    /**
     * Store a newly created movie
     * Only Admin can access
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_phim' => 'required|string|max:255',
            'do_dai' => 'required|integer|min:1|max:600',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mo_ta' => 'required|string|min:10|max:2000',
            'dao_dien' => 'required|string|max:100',
            'dien_vien' => 'required|string|max:500',
            'trailer' => 'nullable|url|max:500',
            'trang_thai' => 'boolean',
        ], [
            'ten_phim.required' => 'Tên phim không được để trống.',
            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự.',
            'do_dai.required' => 'Độ dài phim không được để trống.',
            'do_dai.min' => 'Độ dài phim phải lớn hơn 0 phút.',
            'do_dai.max' => 'Độ dài phim không được vượt quá 600 phút.',
            'poster.image' => 'File poster phải là hình ảnh.',
            'poster.mimes' => 'Poster phải có định dạng: jpeg, png, jpg, gif, webp.',
            'poster.max' => 'Kích thước poster không được vượt quá 5MB.',
            'mo_ta.required' => 'Mô tả không được để trống.',
            'mo_ta.min' => 'Mô tả phải có ít nhất 10 ký tự.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'dao_dien.required' => 'Tên đạo diễn không được để trống.',
            'dao_dien.max' => 'Tên đạo diễn không được vượt quá 100 ký tự.',
            'dien_vien.required' => 'Danh sách diễn viên không được để trống.',
            'dien_vien.max' => 'Danh sách diễn viên không được vượt quá 500 ký tự.',
            'trailer.url' => 'Link trailer phải là URL hợp lệ.',
            'trailer.max' => 'Link trailer không được vượt quá 500 ký tự.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Vui lòng kiểm tra lại thông tin nhập vào.');
        }

        try {
            $data = $request->all();
            
            // Handle poster upload
            if ($request->hasFile('poster')) {
                $file = $request->file('poster');
                $filename = time() . '_' . $file->getClientOriginalName();
                $posterPath = $file->storeAs('posters', $filename, 'public');
                $data['poster'] = $posterPath;
            }

            $data['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

            $movie = Phim::create($data);

            return redirect()->route('admin.movies.index')
                ->with('success', 'Thêm phim "' . $movie->ten_phim . '" thành công!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi thêm phim. Vui lòng thử lại.');
        }
    }

    /**
     * Display the specified movie
     * Admin and Staff can access
     */
    public function show(Phim $movie)
    {
        return view('admin.movies.show', compact('movie'));
    }

    /**
     * Show the form for editing the specified movie
     * Only Admin can access
     */
    public function edit(Phim $movie)
    {
        return view('admin.movies.edit', compact('movie'));
    }

    /**
     * Update the specified movie
     * Only Admin can access
     */
    public function update(Request $request, Phim $movie)
    {
        $validator = Validator::make($request->all(), [
            'ten_phim' => 'required|string|max:255',
            'do_dai' => 'required|integer|min:1|max:600',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'mo_ta' => 'required|string|min:10|max:2000',
            'dao_dien' => 'required|string|max:100',
            'dien_vien' => 'required|string|max:500',
            'trailer' => 'nullable|url|max:500',
            'trang_thai' => 'boolean',
        ], [
            'ten_phim.required' => 'Tên phim không được để trống.',
            'ten_phim.max' => 'Tên phim không được vượt quá 255 ký tự.',
            'do_dai.required' => 'Độ dài phim không được để trống.',
            'do_dai.min' => 'Độ dài phim phải lớn hơn 0 phút.',
            'do_dai.max' => 'Độ dài phim không được vượt quá 600 phút.',
            'poster.image' => 'File poster phải là hình ảnh.',
            'poster.mimes' => 'Poster phải có định dạng: jpeg, png, jpg, gif, webp.',
            'poster.max' => 'Kích thước poster không được vượt quá 5MB.',
            'mo_ta.required' => 'Mô tả không được để trống.',
            'mo_ta.min' => 'Mô tả phải có ít nhất 10 ký tự.',
            'mo_ta.max' => 'Mô tả không được vượt quá 2000 ký tự.',
            'dao_dien.required' => 'Tên đạo diễn không được để trống.',
            'dao_dien.max' => 'Tên đạo diễn không được vượt quá 100 ký tự.',
            'dien_vien.required' => 'Danh sách diễn viên không được để trống.',
            'dien_vien.max' => 'Danh sách diễn viên không được vượt quá 500 ký tự.',
            'trailer.url' => 'Link trailer phải là URL hợp lệ.',
            'trailer.max' => 'Link trailer không được vượt quá 500 ký tự.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        
        // Handle poster upload
        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
                Storage::disk('public')->delete($movie->poster);
            }
            
            $posterPath = $request->file('poster')->store('posters', 'public');
            $data['poster'] = $posterPath;
        }

        $data['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        $movie->update($data);

        return redirect()->route('admin.movies.index')
            ->with('success', 'Cập nhật phim thành công!');
    }

    /**
     * Remove the specified movie
     * Only Admin can access
     */
    public function destroy(Phim $movie)
    {
        // Delete poster file if exists
        if ($movie->poster && Storage::disk('public')->exists($movie->poster)) {
            Storage::disk('public')->delete($movie->poster);
        }

        $movie->delete();

        return redirect()->route('admin.movies.index')
            ->with('success', 'Xóa phim thành công!');
    }

    /**
     * Toggle movie status
     * Only Admin can access
     */
    public function toggleStatus(Phim $movie)
    {
        $movie->update(['trang_thai' => !$movie->trang_thai]);
        
        $status = $movie->trang_thai ? 'kích hoạt' : 'vô hiệu hóa';
        
        return redirect()->back()
            ->with('success', "Đã {$status} phim thành công!");
    }
}
