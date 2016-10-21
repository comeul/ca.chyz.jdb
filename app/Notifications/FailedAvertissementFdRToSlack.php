<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FailedAvertissementFdRToSlack extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        $url = url('http://jdb.chyz.ca/log-viewer/logs/'.Carbon::now()->toDateString());

        return (new SlackMessage)
                    ->error()
                    ->content("Oops! Il y a une erreur.")
                    ->attachment(function ($attachment) use ($url) {
                        $attachment->title("Visionner le log du jour de l'erreur", $url)
                                   ->content("Erreur d'envoie de courriel de feuille de route!");
                    });
    }
}
