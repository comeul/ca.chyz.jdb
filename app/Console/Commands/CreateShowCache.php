<?php

namespace App\Console\Commands;

use App\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreateShowCache extends Command
{
    private $emListe;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jdb:set-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will create show\'s cache when called.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->emListe = [];
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info("Début de la mise à jour de la cache des journaux de bord");
        
        $wp_prefix = env('WP_TABLE_PREFIX');     
        
        // va chercher les émissions publiées, magie disponible grâce au package 'corcel'
        $emissions = Post::type('em')->status('publish')->get();

        $emissions->each(function ($item, $key){
            // On doit entrer dans la cache les émissions qui sont NOW +1 heures et NOW +12 heures.
            // La raison pour lequel on commence à Now+1 est parce que les feuilles de routes
            // sont créer jusqu'à 1:15 en avance, on ne veut donc pas ajouter une
            // émission qui serait, par exemple, à 19h parce que la feuille de route
            // pour cette émission aura déjà été créer à 18h, donc avant le tick de 18:30.
            $targetTime = Carbon::now()->addHours(12);
            $startTime = Carbon::now()->addMinutes(59);
            
            $idEm = $item->ID;
            $titleEm = $item->post_title;
            $journeeEm = $item->meta->journee;
            $ajdDemn = $this->getAjdDemValue($journeeEm);
            $contact = $item->meta->contact;

            if ($ajdDemn != -1) {
                $actif = $item->meta->actif_jdb;
                $notif = $item->meta->notif_jdb;
                $fHeureFrom = Carbon::createFromFormat('H:i', $item->meta->heure_from, 'America/Montreal')->addDays($ajdDemn);

                if ($fHeureFrom->between($startTime, $targetTime) && $actif == true) {
                    $fHeureTo = Carbon::createFromFormat('H:i', $item->meta->heure_to, 'America/Montreal')->addDays($ajdDemn);
                    
                    //Ajustement de l'heure ou l'émission se termine aussi, pour information.
                    $diffFromTo = $fHeureFrom->diffInMinutes($fHeureTo, false);
                    if ($diffFromTo<0) {
                      $fHeureTo->addDay();
                    }

                    //Ajout des infos de cette émission en cache, si elle n'existent pas
                    Cache::add("journal:ems:{$idEm}", [
                        'ID' => $idEm,
                        'journee' => $journeeEm,
                        'heure_from' => $fHeureFrom,
                        'heure_to' => $fHeureTo,
                        'notif_jdb' => !!$notif, //turn string to integer and inverse it. So "1" becomes "TRUE";
                        'post_title' => $titleEm,
                        'courriel' => $contact,
                    ], 1440);

                    //Ajout de l'ID dans la listes d'émissions
                    $this->emListe[] = $idEm;
                }
            }
        });

        if (count($this->emListe) != 0){
            Log::notice("Mise à jour de la liste des émissions.");
            Cache::put('journal:emList', $this->emListe, 1400);
        }else {
            Log::notice("Aucune nouvelle émission dans la liste.");
        }
        
        Log::info("Fin de la mise à jour de la cache des journaux de bord");
    }

    private function getAjdDemValue($journee)
    {
        // Génération d'une variable qui dit si l'émission est pour la journée 
        // d'aujourd'hui ou de demain. Sera utile aussi plus bas dans le script.
        switch ($journee) {
            case $this->getTextualValueOfDay('today'):
                return 0;

            case $this->getTextualValueOfDay('tomorrow'):
                return 1;
                
            default:
                return -1;
            }
    }

    private function getTextualValueOfDay($when)
    {
        // Un array des jours de la semaine, tels qu'inscrit dans la BDD des journaux de bord.
        // Ils sont en ordre de dayOfWeek tels que sorti par Carbon. (1=lundi, etc)
        $jourSemaine = [
            'Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'
        ];

        switch ($when) {
            case 'today':
                return $jourSemaine[Carbon::now()->dayOfWeek];
            
            case 'tomorrow':
                return $jourSemaine[Carbon::tomorrow('America/Montreal')->dayOfWeek];
        }
    }
}
