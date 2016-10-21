<?php

namespace App\Mail;

use App\Notifications\FailedAvertissementFdRToSlack;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Queue\SerializesModels;

class AvertissementFeuilleDeRoute extends Mailable
{
    use Queueable, SerializesModels, Notifiable;

    public $emission;
    public $fdr;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emission, $fdr)
    {
        $this->emission = $emission;
        $this->fdr = $fdr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('programmation@chyz.ca')
            ->subject("Feuille de route - {$this->emission['post_title']} du {$this->fdr['creation_date']}")
            ->view('emails.avertissementFdR');
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @return string
     */
    public function routeNotificationForSlack()
    {
        return "https://hooks.slack.com/services/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";
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Notification::send($this->fdr, new FailedAvertissementFdRToSlack($exception));
    }
}
