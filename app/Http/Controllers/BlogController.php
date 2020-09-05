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

    public function categoryIndex(Request $request, $categoryName, $id){
        $blogs = Blog::with('user')->whereHas('cat', function($q) use( $id ){
            $q->where('category_id', $id );
        })->orderBy('id', 'desc')->select(['id', 'title', 'slug', 'user_id', 'featuredImage'])->paginate(1);
        return view('category')->with([
            'categoryName' => $categoryName,
            'blogs' => $blogs
        ]);
    }

    public function tagIndex(Request $request, $categoryName, $id){
        $blogs = Blog::with('user')->whereHas('tag', function($q) use( $id ){
            $q->where('tag_id', $id );
        })->orderBy('id', 'desc')->select(['id', 'title', 'slug', 'user_id', 'featuredImage'])->paginate(1);
        return view('tag')->with([
            'tagName' => $categoryName,
            'blogs' => $blogs
        ]);
    }

    public function allBlogs(Request $request){
        $blogs = Blog::orderBy('id','desc')->with(['cat','user'])->select(['id', 'title', 'post_excerpt', 'slug', 'user_id', 'featuredImage'])->paginate(1);
        return view('blogs')->with([
            'blogs' => $blogs
        ]);
    }
    
    public function search(Request $request){
        $str = $request->str;
        $blogs = Blog::orderBy('id','desc')->with(['cat','user','tag'])->select(['id', 'title', 'post_excerpt', 'slug', 'user_id', 'featuredImage']);

        //using when
        $blogs->when($str!='', function($q) use($str) {
            $q->where('title', 'LIKE', "%{$str}%")
               ->orwhereHas('cat', function($q) use($str){
                $q->where('categoryName', $str);
            })
               ->orwhereHas('tag', function($q) use($str){
            $q->where('tagName', $str);
            });
        });

        $blogs = $blogs->paginate(1);
        $blogs = $blogs->appends($request->all());
        return view('blogs')->with([
            'blogs' => $blogs
        ]);

        //using Normal if
        /*if($str) return $blogs->get();
        
        $blogs->where('title', 'LIKE', "%{$str}%")
                ->orwhereHas('cat', function($q) use($str){
                    $q->where('categoryName', $str);
                })
                ->orwhereHas('tag', function($q) use($str){
                $q->where('tagName', $str);
                });
        */
        return $blogs->get();
        // return view('blogs')->with([
        //     'blogs' => $blogs
        // ]);
    }
}
