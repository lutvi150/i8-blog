<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::latest()->paginate(10);
        return view('blog.index', compact('blogs'));
    }
    public function create()
    {
        return view('blog.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:png,jpg,jpeg',
            'title' => 'required',
            'content' => 'required',
        ]);
        //upload image
        $image = $request->file('image');
        try {
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/blogs'), $fileName);
            $blog = Blog::create([
                'image' => $fileName,
                'title' => $request->title,
                'content' => $request->content,
            ]);
            if ($blog) {
                //redirect dengan pesan sukses
                return redirect()->route('blog.index')->with(['success' => 'Data Berhasil Disimpan!']);
            } else {
                //redirect dengan pesan error
                return redirect()->route('blog.index')->with(['error' => 'Data Gagal Disimpan!']);
            }
        } catch (\Throwable $e) {
            return redirect()->route('blog.index')->with('error', 'Terjadi Kesalahan: ' . $e->getMessage());
        }
    }
    public function edit(Blog $blog)
    {
        return view('blog.edit', compact('blog'));
    }
    public function update(Request $request, Blog $blog)
    {
        $request->validate([
            'image' => 'image|mimes:png,jpg,jpeg',
            'title' => 'required',
            'content' => 'required',
        ]);
        $blog = Blog::findOrFail($blog->id);
        if ($request->file('image') == "") {
            $blog->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            if (file_exists(public_path('storage/blogs/' . $blog->image))) {
                unlink(public_path('storage/blogs/' . $blog->image));
            }
            $image = $request->file('image');
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('storage/blogs'), $fileName);
            $blog->update([
                'image' => $fileName,
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }
        if ($blog) {
            return redirect()->route('blog.index')->with(['success' => 'Data Berhasil Diupdate!']);
        } else {
            return redirect()->route('blog.index')->with(['error' => 'Data Gagal Diupdate!']);
        }
    }
    public function destroy($id)
    {
        $blog = Blog::findOrFail($id);
        if (file_exists(public_path('storage/blogs/' . $blog->image))) {
            unlink(public_path('storage/blogs/' . $blog->image));
        }
        $blog->delete();
        if ($blog) {
            return redirect()->route('blog.index')->with(['success' => 'Data Berhasil Dihapus!']);
        } else {
            return redirect()->route('blog.index')->with(['error' => 'Data Gagal Dihapus!']);
        }
    }
}
