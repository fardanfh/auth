<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{

    /**
     * @OA\Get(
     *     path="/public/posts",
     *     summary="List all posts",
     *     @OA\Parameter(
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */

    public function index(Request $request){
        
        $acceptHeader = $request->header('Accept');

        //$posts = Post::with('comment')->OrderBy('id', 'ASC')->paginate(2)->toArray();
        $posts = Post::all();

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            
            // $response = [
            //     "total_count" => $posts["total"],
            //     "limit" => $posts["per_page"],
            //     "pagination" => [
            //         "next_page" => $posts["next_page_url"],
            //         "current_page" => $posts["current_page"]
            //     ],
            //     "data" => $posts["data"],
            // ];

            if ($acceptHeader === 'application/json') {
                return response()->json($posts, 200);
            } else {
                $xml = new \SimpleXMLElement('<posts/>');
                foreach ($posts as $item) {
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

    public function detail($id, Request $request)
    {

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {

            $post = Post::with(['user' => function($query)
            {
                $query->select('id','name');
            }])->find($id);

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

    public function insert(Request $request){

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
    
}
