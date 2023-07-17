<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CommentsController extends Controller
{

    public function index(Request $request){
        
        $acceptHeader = $request->header('Accept');

        $comments = Comment::all();

        //$comments = Comment::with('user')->OrderBy("id", "ASC")->paginate(2)->toArray();

        if ($acceptHeader === 'application/json' || $acceptHeader === 'application/xml') {
            
            // $response = [
            //     "total_count" => $comments["total"],
            //     "limit" => $comments["per_page"],
            //     "pagination" => [
            //         "next_page" => $comments["next_page_url"],
            //         "current_page" => $comments["current_page"]
            //     ],
            //     "data" => $comments["data"],
            // ];

            if ($acceptHeader === 'application/json') {
                return response()->json($comments, 200);
            } else {
                $xml = new \SimpleXMLElement('<comments/>');
                foreach ($comments->items('data') as $item) {
                    $xmlItem = $xml->addChild('pst');
                    $xmlItem->addChild('id', $item->id);
                    $xmlItem->addChild('comment', $item->comment);
                    $xmlItem->addChild('post_id', $item->post_id);
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
                'comment' => 'required|min:5',
                'post_id' => 'required',
                'user_id' => 'required'
            ];

            $validator = Validator::make($input, $validationRules);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            
            $comment = Comment::create($input);         

            if ($acceptHeader === 'application/json') {
                return response()->json($comment, 200);
            } else {
                $xml = new \SimpleXMLElement('<comments/>');

                $xmlItem = $xml->addChild('pst');
                $xmlItem->addChild('id', $comment->id);
                $xmlItem->addChild('comment', $comment->comment);
                $xmlItem->addChild('post_id', $comment->post_id);
                $xmlItem->addChild('user_id', $comment->user_id);
                $xmlItem->addChild('created_at', $comment->created_at);
                $xmlItem->addChild('update_at', $comment->update_at);
                
                return $xml->asXML();
            }
        } else {
            return response('Not Acceptable', 406);
        }

    }
}
