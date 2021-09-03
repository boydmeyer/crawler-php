<?php

namespace App\Http\Controllers;

use App\Jobs\CrawlJob;
use App\Models\Link;
use App\Models\Page;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Page::paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string'
        ]);

        // TODO: Create Job for taking snapshots so user dont have to wait.
        $request['image'] = $this->takeSnapshot($request->url);

        $result = Page::create($request->all());

        $link = Link::create([
            'page_id' => $result->id,
            'url' => $result->url,
        ]);
        dispatch(new CrawlJob($link));

        return $result;
    }


    /**
     * Takes a snapshot of the url.
     * TODO: Move this to a Service or Job outside of the controller
     * 
     * @param string $url
     * 
     * @return string
     */
    private function takeSnapshot(string $url): string {
        $file_path = md5(rand()) . ".jpeg";
        Browsershot::url($url)->save($file_path);
        // TODO: move file
        return $file_path;
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {        
        return Link::where('page_id', $id)->paginate(15);
    }
}
