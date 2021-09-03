<?php

namespace App\Jobs;

use App\Models\Link;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Job on the queue to crawl Link
 */
class CrawlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var App\Models\Link
     */
    private $link;

    /**
     * Create a new job instance.
     *
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {

        $this->createNewLinks();
        $this->update();
    }


    /**
     * Create new Links from page response
     */
    private function createNewLinks()
    {

        $urls = $this->getUrlsFromPage();
        $this->addNewLinks($urls);
    }

    /**
     * Update Link data in the database
     */
    private function update()
    {
        $html = file_get_contents($this->link->url);
        $crawler =  new Crawler($html);
        $title = $crawler->filter('title')->text() ?: "";

        Link::where('id', $this->link->id)
            ->update(['title' => $title, 'body' => $html, 'crawled' => TRUE]);
    }

    /**
     * Gets all a tags href values from page response.
     * TODO: check if url is already in database.
     * TODO: check if url is media file.
     * TODO: Do a check if new url is from the same host as original link. Now will get to many new urls for every link visited.
     * 
     * @return array
     */
    private function getUrlsFromPage()
    {
        $html = file_get_contents($this->link->url);
        $crawler = new Crawler($html);
        $urls = $crawler->filter('a[href^="http:"], a[href^="https:"]')->each(function ($node) {
            return $node->attr('href');
        });

        return array_unique($urls);
    }

    /**
     * Creates new Links from new found urls on page.
     * TODO: Batch insert
     */
    
    
    /**
     * @param array $urls
     * 
     */
    private function addNewLinks(array $urls)
    {
        foreach ($urls as $url) {
            $this->createLink($url);
        }
    }

    /**
     * Create new link with given url
     * dispatch new Crawljob for link
     * 
     * @param string $url
     */
    private function createLink(string $url)
    {
        $link = Link::create([
            'page_id' => $this->link->page_id,
            'url' => $url,
        ]);

        dispatch(new CrawlJob($link));
    }

    /**
     * Checks if url already exists in database
     */
    private function linkExists(string $url)
    {
        return Link::where('url', $url)
            ->where('page_id', $this->link->page_id)
            ->count() > 0;
    }

    /**
     * TODO: write validation
     * Is Url valid or invalid
     */
    private function urlIsValid(string $url): bool
    {
        return true;
    }
}
