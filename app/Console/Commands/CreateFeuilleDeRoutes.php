<?php

namespace App\Console\Commands;

use App\Fdr;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreateFeuilleDeRoutes extends Command {
    private $emListe;
    private $newEmListe;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jdb:create-fdr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée les feuilles de routes dans le système.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        $this->emListe = collect(Cache::pull('journal:emList'));
        $this->newEmListe = collect([]);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        Log::info("Début de la création des feuilles de routes");

        $this->emListe->each(function ($item, $key) {
            $emission = collect(Cache::get("journal:ems:{$item}"));

            if (!$emission->isEmpty()) {
                $remainingTime = $emission['heure_from']->diffInMinutes(Carbon::now());
                if ($remainingTime <= 75) {
                    Log::notice("Création d'une feuille de route pour {$emission['post_title']}");

                    $fdr = new Fdr;
                    $fdr->em_id = $emission['ID'];
                    $fdr->h_from = $emission['heure_from']->toDateTimeString();
                    $fdr->h_to = $emission['heure_to']->toDateTimeString();
                    $fdr->attente = 0;
                    $fdr->com_dir = "";
                    $fdr->com_anm = "";
                    $fdr->creation_date = Carbon::now()->toDateTimeString();
                    $fdr->soumis_date = null;
                    $fdr->save();

                    if ($fdr == false) {
                        Log::warning("Erreur de la création de feuille de route pour {$emission['post_title']}");
                    } else {
                        Log::notice("Feuille de route créée pour {$emission['post_title']}");
                    }

                    if (!!$emission['notif_jdb']) {
                        Artisan::queue('jdb:send-emails', [
                            'emId' => $emission['ID'],
                        ]);
                    }
                }else{
                    // L'émission n'est pas proche, on a donc pas envoyé d'avertissement... Encore.
                    // On va rajouter l'ID à la nouvelle liste pour changer la cache et ainsi
                    // ne pas repasser sur une émission qui aurait été envoyé.
                    $this->newEmListe->push($emission['ID']);
                }
            }
        });

        Cache::put('journal:emList', $this->newEmListe, 1400);
        Log::info("Fin de la création des feuilles de routes");
    }
}
