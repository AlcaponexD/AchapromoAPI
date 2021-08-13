<?php

namespace App\Http\Controllers;


use App\Jobs\ProcessUrl;
use App\Models\Offer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Goutte\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class OfferController extends Controller
{

    public function index(){
        $offers = Offer::where('active','=','yes')->where('review','=','no')->where('published','=','yes')->orderBy('id','DESC')->limit(8)->get();
        $play = Offer::where('active','=','yes')->where('review','=','no')->where('published','=','yes')->where('alerted_sound','=','no')->orderBy('id','DESC')->first();
        $top_discounts = DB::select("SELECT distinct (SELECT COUNT(id) FROM offers WHERE published = 'yes' AND review = 'no' AND active = 'yes' AND store = off.store) AS total,store FROM offers AS off  ORDER BY total DESC LIMIT 3");
        $lower_discounts = DB::select("SELECT distinct (SELECT COUNT(id) FROM offers WHERE published = 'yes' AND review = 'no' AND active = 'yes' AND store = off.store) AS total,store FROM offers AS off ORDER BY total ASC LIMIT 3");

        if($play){
            $play_sound = true;
            $update_sounds= Offer::where('active','=','yes')->where('review','=','no')->where('published','=','yes')->where('alerted_sound','=','no')->update([
                'alerted_sound' => 'yes'
            ]);
        }
        
        return response()->json([
            'play_sound' => $play_sound ?? false,
            'offers' => $offers,
            'tops' => $top_discounts,
            'lowers' => $lower_discounts
        ]);
    }

    public function publish(){
        $offers = Offer::where('active','=','yes')->where('review','=','no')->where('published','=','no')->where('link_updated','=','yes')->orderBy('id','DESC')->get();
        foreach ($offers as $item){
            $offer = Offer::where('id',$item->id)->first();
            try{
                $response = Http::post(env("WEB_HOOK_DISCORD_API"),[
                    "embeds" => [
                        [
                            "title" => $offer->product,
                            "description" => "R$ ".number_format($offer->price,'2','.',','),
                            "color" => 14177041,
                            "url" => $offer->link,
                            "image" => [
                                "url" => $offer->image_url
                            ]
                        ]
                    ]
                ]);
                $offer->update([
                    "published" => "yes"
                ]);
            }catch (\Exception $exception){
                Log::error($exception);
            }
        }
        return response()->json($offers);
    }
    public function pelando(){


        $client = new Client();
        $crawler = $client->request('GET', 'https://www.pelando.com.br/grupo/videogames');

        $crawler->filter('script')->each(function (Crawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $articles = $crawler->filter('article')->each(function (Crawler $node,$i){
            try{
                $link = $node->filter('a.cept-dealBtn')->attr('href');
            }catch (\Exception $e){
                $link = 'empty';
            }

            $store = $node->filter('span.cept-merchant-name.text--b.text--color-brandPrimary.link')->text('empty');
            $title = $node->filter('a.cept-tt.thread-link.linkPlain.thread-title--list')->text('empty');
            $img = $node->filter('img.thread-image.width--all-auto.height--all-auto.imgFrame-img.cept-thread-img')->attr('src');
            $price = $node->filter('span.thread-price.text--b.cept-tp.size--all-l.size--fromW3-xl')->text('empty');
            try{
                $coupon = $node->filter('input.lbox--v-4.flex--width-calc-fix.flex--grow-1.overflow--ellipsis.width--all-12.hAlign--all-c.text--color-charcoal.text--b.btn--mini.clickable')->attr('value');
            }catch (\Exception $exception){
                $coupon = 'empty';
            }
            $item = [
                'product' => \helpers::format_title_pelando($title),
                'image_url' => $img,
                'price' => \helpers::money_to_db($price),
                'store' =>$store,
                'link' =>\helpers::update_links($link),
                'source' => 'Pelando - Peças e PC gamer',
                'coupon' => $coupon
            ];
            return $item;
        });

        $titles = $crawler->filter('a.cept-tt.thread-link.linkPlain.thread-title--list')->each(function ($node){
            return $node->text();
        });
        $prices = $crawler->filter('span.thread-price.text--b.cept-tp.size--all-l.size--fromW3-xl')->each(function ($node){
            return $node->text();
        });
        $stores = $crawler->filter('span.cept-merchant-name.text--b.text--color-brandPrimary.link')->each(function ($node){
            return $node->text();
        });

        $links = $crawler->filter('a.cept-dealBtn.boxAlign-jc--all-c.space--h-3.width--all-12.btn.btn--mode-primary')->each(function ($node){
            return $node->attr('href');
        });
        $coupons = $crawler->filter('input.lbox--v-4.flex--width-calc-fix.flex--grow-1.overflow--ellipsis.width--all-12.hAlign--all-c.text--color-charcoal.text--b.btn--mini.clickable')->each(function ($node){
            return $node->attr('value');
        });

        foreach ($articles as $key =>  $article){
            $articles[$key]['published'] = 'no';
            if($article['price'] == 'empty' && $article['product'] == 'empty' && $article['store'] == 'empty' && $article['link'] == 'empty'){
                $articles[$key]['price'] = \helpers::money_to_db($prices[$key])  ?? '0';

                if($article['product'] == 'empty'){
                    $articles[$key]['product'] = \helpers::format_title_pelando($titles[$key]) ?? 'empty';
                }
                if($article['store'] == 'empty'){
                    $articles[$key]['store'] = $stores[$key] ?? 'empty';
                }
                if($article['link'] == 'empty'){
                    $articles[$key]['link'] = $links[$key] ?? 'empty';
                }
                if($article['coupon'] == 'empty'){
                    $articles[$key]['coupon'] = $coupons[$key] ?? 'empty';
                }

                //Deixa para revisão
                $articles[$key]['review'] = 'yes';
                $articles[$key]['published'] = 'no';
            }

            if($article['price'] == 'empty' && $article['link'] == 'empty'&& $article['coupon'] == 'empty'){
                //Deixa para revisão
                $articles[$key]['review'] = 'yes';
            }
        }


        //Loop para salvar
        for ($i = 0 ; $i < count($articles); $i++){
            try{

            $price = $articles[$i]['price'];
            $product = $articles[$i]['product'];
            $exists = Offer::where('price',$price)->where('product','LIKE',"%$product%")->first();
            if(!$exists){
                $insert = Offer::create($articles[$i]);
                //Ao salvar pega os retornos e cria uma fila que irá acessar o link e pegar a url e dar update com o novo link.

                //Cria filas
                ProcessUrl::dispatch($insert->id,$insert->link);
            }


            }catch (\Exception $exception){
                return response()->json($exception->getMessage());
            }
        }
        return true;

    }

    public function update_photos()
    {
          $photos_to_update = Offer::where('link_updated','no')->get();

            foreach($photos_to_update as $photo){
                $url = $photo->image_url;
                $contents = file_get_contents($url);
                $name = substr($url, strrpos($url, '/') + 1);
                $return  = Storage::disk('public')->put($name, $contents);
                $link_updated = 'https://api.achapromo.com.br/storage/'.$name;
                $upd  = Offer::where('id',$photo->id)->update([
                    'image_url' => $link_updated
                ]);
            }


            return response()->json($photos_to_update);
    }

    public function check(){
        //List of executes
        $this->pelando();

    }

}
