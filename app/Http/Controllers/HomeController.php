<?php

namespace App\Http\Controllers;

use App\Http\Traits\YoutubeTrait;
use App\Http\Traits\TwitchTrait;

use App\User;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Request;
use TwitchApi;

class HomeController extends Controller
{

use YoutubeTrait;
use TwitchTrait;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];

        if ($this->isLoggedInYoutube()) {
            $this->getSubVideos();
            $data['videos'] = [];
        } else {
            $data['youtube_url'] = $this->generateYoutubeUrl();
        }

        if ($this->isLoggedInTwitch()) {
            $data['streams'] = $this->getFollowedStreams();
        } else {
            $data['twitch_url'] = $this->generateTwitchUrl();
        }

        return view('home', $data);
    }

    public function create_post($request, $stream, $video)
    {
        $data = $request->all();
        if (isset($stream)) {
            $post = new Post();
            $post->user_id = Auth::user()->id;
            $post->type = 1;
            $post->caption = $data['caption'];
            $post->title = $stream['channel']['status'];
            $post->channel_name = $stream['channel']['name']; //Not using display_name
            $post->game_title = $stream['game'];
        }
        if (isset($video)) {
            $post = new Post();
            $post->user_id = Auth::user()->id;
            $post->type = 2;
            $post->caption = $data['caption'];
            $post->title = $video['title'];
            $post->channel_name = $video['name']; //Not using display_name
            $post->url = $video['url'];
        }
        if (isset($post)) {
            if ($post->save()) {
                return redirect()->back()->with('status', 'Le post a été créé.');
            } else {
                return redirect()->back()->withErrors('Erreur de sauvegarde du post.');
            }
        }
    }

    public function test()
    {
        $input = Request::input('search');

        $users = User::all();

        $foundUsers = User::where('first_name', 'LIKE', $input.'%')
                            ->orWhere('last_name', 'LIKE', $input.'%')
                            ->orderBy('first_name','ASC')
                            ->get();


        return view('test', compact('foundUsers', 'input'));
    }
}
