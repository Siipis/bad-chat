<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use CMS;
use Auth;

class ConversationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('access:conversations');
    }

    public function getIndex()
    {
        return CMS::render('conversations.main', [
            'conversations' => Conversation::visible()->readable()->orderBy('updated_at', 'desc')->paginate(50),
        ]);
    }

    public function getCreate()
    {
        return CMS::render('conversations.create');
    }

    public function postCreate(Requests\ConversationRequest $request)
    {
        \DB::beginTransaction();

        try {
            $auth = Auth::user();
            $users = [];

            $title = trim($request->input('title'));
            $message = $request->input('message');

            foreach (explode(' ', $request->input('participants')) as $participant) {
                $name = trim($participant, ', ');

                array_push($users, $name);
            }

            $users = array_unique($users);

            $conversation = new Conversation();
            $conversation->title = $title;
            $conversation->message = $message;

            $auth->conversationPosts()->save($conversation);

            $conversation->participants()->save($auth);

            foreach ($users as $user) {
                $user = User::findByName($user);

                if ($user->id == $auth->id) {
                    continue;
                }

                if (!is_null($user)) {
                    $conversation->participants()->save($user);

                    $user->sendConversation($auth->name, $conversation);
                }
            }

            \DB::commit();

            return redirect(url('conversations/view', $conversation->id));
        } catch (\Exception $e) {
            \DB::rollBack();

            if (config('app.debug')) {
                throw $e;
            }

            return redirect()->back()->with([
                'alert' => [
                    'type' => 'danger',
                    'message' => 'An error occurred! Please try again later.',
                ]
            ]);
        }
    }

    public function getView($id)
    {
        $conversation = Conversation::findOrFail($id);

        if ($conversation instanceof Conversation) {
            if ($conversation->isThrashed(Auth::user())) {
                abort(404);
            }

            $conversation->read(Auth::user());

            return CMS::render('conversations.view', [
                'conversation' => $conversation,
                'responses' => $conversation->responses()->getQuery()->orWhere('id', $conversation->id)->orderBy('id', 'desc')->paginate(10),
                'thrashed' => $conversation->participants->filter(function ($user) use ($conversation) {
                    return $conversation->isThrashed($user);
                }),
            ]);
        }
    }

    public function postView(Request $request)
    {
        $this->validate($request, [
            'parent_id' => 'required|exists:conversations,id',
            'message' => 'required',
        ]);

        $conversation = Conversation::findOrFail($request->input('parent_id'));

        $auth = Auth::user();

        if ($conversation instanceof Conversation) {
            if ($conversation->canView($auth)) {
                $message = $request->input('message');

                $response = new Conversation();
                $response->message = $message;

                $auth->conversationPosts()->save($response);

                $conversation->responses()->save($response);

                $conversation->touch();

                foreach ($conversation->participants as $user) {
                    if ($user->id == $auth->id) {
                        continue;
                    }

                    $user->sendConversation($auth->name, $response);
                }

                return redirect(url('conversations/view', $conversation->id));
            }
        }

        return redirect('conversations');
    }

    public function postDelete(Request $request)
    {
        $this->validate($request, [
            'conversation_id' => 'required|numeric'
        ]);

        $conversation = Conversation::findOrFail($request->input('conversation_id'));

        if ($conversation instanceof Conversation) {
            $conversation->thrash(Auth::user());
        }

        return redirect('conversations');
    }
}
