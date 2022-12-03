<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PublicController extends Controller
{
    public function index(Request $request){
        
        $acceptHeader = $request->header('Accept');

        $posts = Post::OrderBy('id', 'DESC')->paginate(2)->toArray();

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

    public function detail($id, Request $request)
    {

        $acceptHeader = $request->header('Accept');

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {

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
    
}
