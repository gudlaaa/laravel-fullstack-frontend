<?php

namespace App\Http\Controllers;

use App\Blog;
use App\User;
use App\Category;
use Illuminate\View\View;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request){
        $categories = Category::get(['id', 'categoryName']);
        $blogs = Blog::orderBy('id','desc')->with(['cat','user'])->limit(6)->get(['id', 'title', 'post_excerpt', 'slug', 'user_id', 'featuredImage']);
        return view('home')->with([
            'categories' => $categories,
            'blogs' => $blogs
        ]);
    }

    public function blogSingle(Request $request, $slug){
        $blog = Blog::where('slug', $slug)->with(['cat', 'tag', 'user'])->first(['id', 'title', 'user_id', 'featuredImage', 'post']);
        $category_ids = [];

        foreach( $blog->cat as $cat){
            array_push($category_ids, $cat->id);
        }

        $relatedBlogs = Blog::with('user')->where('id', '!=', $blog->id)->whereHas('cat', function($q) use( $category_ids ){
            $q->whereIn('category_id', $category_ids );
        })->orderBy('id', 'desc')->limit(5)->get(['id', 'title', 'slug', 'user_id', 'featuredImage']);
        //return $blog;
        
        return view('blogsingle')->with([
            'blog' => $blog,
            'relatedBlogs' => $relatedBlogs
            ]);

    }

    public function compose(View $view)
    {
        $cat = Category::get(['id', 'categoryName']);
        $view->with('cat', $cat);
    }
    
}
