<?php

namespace App\Jobs;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Goutte\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $id;
    private $url;
    public function __construct($id,$url)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client();

        $crawler = $client->request('GET',$this->url);
        $uri = $crawler->getUri();
        $update = Offer::where('id',$this->id)->update([
            'link' => \helpers::update_links($uri),
            'link_updated' => 'yes'
        ]);
    }
}
