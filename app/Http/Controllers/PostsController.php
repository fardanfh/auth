<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PostsController extends Controller
{
    public function index(Request $request){
        
        $acceptHeader = $request->header('Accept');

        if (Gate::denies('read-post')) {
            return response()->json([
                'success' => false,
                'status' => 403,
                'message' => 'You are unauthorized'
            ], 403);
        }

        if (Auth::user()->role === 'admin') {
            $posts = Post::OrderBy('id', 'DESC')->paginate(2)->toArray();
        }else{
            $posts = Post::Where(['user_id' => Auth::user()->id])->OrderBy("id", "DESC")->paginate(2)->toArray();
        }

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            
            $response = [
                "total_count" => $posts["total"],
                "limit" => $posts["per_page"],
                "pagination" => [
                    "next_page" => $posts["next_page_url"],
                    "current_page" => $posts["current_page"]
                ],
                "data" => $posts["data"],
            ];

            if ($acceptHeader === 'application/json') {
                return response()->json($response, 200);
            } else {
                $xml = new \SimpleXMLElement('<posts/>');
                foreach ($posts->items('data') as $item) {
                    $xmlItem = $xml->addChild('pst');
                    $xmlItem->addChild('id', $item->id);
                    $xmlItem->addChild('title', $item->title);
                    $xmlItem->addChild('status', $item->status);
                    $xmlItem->addChild('content', $item->content);
                    $xmlItem->addChild('user_id', $item->user_id);
                    $xmlItem->addChild('created_at', $item->created_at);
                    $xmlItem->addChild('update_at', $item->update_at);
                }
                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable', 406);
        }

    }

    public function store(Request $request){

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $input = $request->all();

            $validationRules =[
                'title' => 'required|min:5',
                'content' => 'required|min:10',
                'status' => 'required|in:draft,published',
                'user_id' => 'required'
            ];

            $validator = Validator::make($input, $validationRules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            
            $post = Post::create($input);

            if (Gate::denies('create-post')) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'You are unauthorized'
                ], 403);
            }            

            if ($acceptHeader === 'application/json') {
                return response()->json($post, 200);
            } else {
                $xml = new \SimpleXMLElement('<posts/>');

                $xmlItem = $xml->addChild('post');
                $xmlItem->addChild('id', $post->id);
                $xmlItem->addChild('title', $post->title);
                $xmlItem->addChild('status', $post->status);
                $xmlItem->addChild('content', $post->content);
                $xmlItem->addChild('user_id', $post->user_id);
                $xmlItem->addChild('created_at', $post->created_at);
                $xmlItem->addChild('update_at', $post->update_at);
                
                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable', 406);
        }

    }

    public function detail($id, Request $request)
    {

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {

            if (Gate::denies('read-detail-post')) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'You are unauthorized'
                ], 403);
            }

            $post = Post::find($id);

            if ($acceptHeader === 'application/json') {
                 return response()->json($post, 200);
            } else {
                $xml = new \SimpleXMLElement('<posts/>');

                $xmlItem = $xml->addChild('post');
                $xmlItem->addChild('id', $post->id);
                $xmlItem->addChild('title', $post->title);
                $xmlItem->addChild('status', $post->status);
                $xmlItem->addChild('content', $post->content);
                $xmlItem->addChild('user_id', $post->user_id);
                $xmlItem->addChild('created_at', $post->created_at);
                $xmlItem->addChild('update_at', $post->update_at);
                
                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable', 406);
        }
        
    }

    public function update(Request $request ,$id)
    {

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $input = $request->all();
            $post = Post::find($id);

            if (!$post) {
                abort(404);
            }

            if (Gate::denies('update-post', $post)) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'You are unauthorized'
                ], 403);
            }
            
            $validationRules =[
                'title' => 'required|min:5',
                'content' => 'required|min:10',
                'status' => 'required|in:draft,published',
            ];

            $validator = Validator::make($input, $validationRules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $post->fill($input);
            $post->save();

            $contentTypeHeader = $request->header('Content-Type');

            if ($acceptHeader === 'application/json') {

                if ($contentTypeHeader === 'application/json') {
                    return response()->json($post, 200);
                }else{
                     return response('Unsupported Media Type', 415);
                }

                
            } else if ($contentTypeHeader === 'application/xml') {
                if ($contentTypeHeader === 'application/xml') {
                    $xml = new \SimpleXMLElement('<posts/>');

                    $xmlItem = $xml->addChild('post');
                    $xmlItem->addChild('id', $post->id);
                    $xmlItem->addChild('title', $post->title);
                    $xmlItem->addChild('status', $post->status);
                    $xmlItem->addChild('content', $post->content);
                    $xmlItem->addChild('user_id', $post->user_id);
                    $xmlItem->addChild('created_at', $post->created_at);
                    $xmlItem->addChild('update_at', $post->update_at);
                    
                    return $xml->asXML();
                }else{
                    return response('Unsupported Media Type', 415);
                }
            }  
            
        } else {
            return response('Not Acceptable', 406);
        }

    }

    public function delete($id, Request $request)
    {

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            $post = Post::find($id);

            if (!$post) {
                abort(404);
            }

            if (Gate::denies('delete-post', $post)) {
                return response()->json([
                    'success' => false,
                    'status' => 403,
                    'message' => 'You are unauthorized'
                ], 403);
            }

            $post->delete();

            if ($acceptHeader === 'application/json') {
                $outPut = [
                    "message" => "delete Successfully",
                    "post_id" => $id
                ];

                return response()->json($outPut, 200);
            } else {
                $xml = new \SimpleXMLElement('<posts/>');

                $xmlItem = $xml->addChild('post');
                $xmlItem->addChild('id', $post->id);
                $xmlItem->addChild('message', 'delete succesfully');
                
                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable', 406);
        }

    }
    
}
