<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth')->only(['create']);
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::check()) {
            $categories = Category::get();
            return view('theme.blogs.create', compact('categories'));
        }
        return redirect('/login');
        // abort(403);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBlogRequest $request)
    {
        $data = $request->validated();
        // Image Uploading Steps
        // 1- Get image
        $image = $request->image;
        // 2- Change it's current name
        $newImageName = time() . '-' . $image->getClientOriginalName();
        // 3- Move image to my project
        $image->storeAs('blogs', $newImageName, 'public');
        // 4- Save new image name to database record
        $data['image'] = $newImageName;
        $data['user_id'] = Auth::user()->id;

        // Create new blog record
        Blog::create($data);

        return back()->with('blog_create_status', 'Your blog created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog)
    {
        return view('theme.single-blog', compact('blog'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog)
    {
        if ($blog->user_id == Auth::user()->id) {
            $categories = Category::get();
            return view('theme.blogs.edit', compact('categories', 'blog'));
        }
        abort(403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        if ($blog->user_id == Auth::user()->id) {
            $data = $request->validated();

            if ($request->hasFile('image')) {
                // Image Uploading Steps
                // 1- Delete the previous image
                Storage::delete("public/blogs/$blog->image");
                // 2- Get image
                $image = $request->image;
                // 3- Change it's current name
                $newImageName = time() . '-' . $image->getClientOriginalName();
                // 4- Move image to my project
                $image->storeAs('blogs', $newImageName, 'public');
                // 5- Save new image name to database record
                $data['image'] = $newImageName;
            }

            // Create new blog record
            $blog->update($data);

            return back()->with('blog_update_status', 'Your blog Updated successfully');
        }
        abort(403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        if ($blog->user_id == Auth::user()->id) {
            Storage::delete("public/blogs/$blog->image");
            $blog->delete();
            return back()->with('blog_delete_status', "Your blog \"$blog->title\" deleted successfully");
        }
        abort(403);
    }

    /**
     * Display all blogs
     */
    public function myBlogs()
    {
        if (Auth::check()) {
            $blogs = Blog::where('user_id', '=', Auth::user()->id)->paginate(10);
            return view('theme.blogs.my-blogs', compact('blogs'));
        } else {
            return redirect('/');
        }
    }
}
