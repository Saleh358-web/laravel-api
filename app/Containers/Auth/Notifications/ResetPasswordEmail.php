<?php

namespace App\Containers\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Auth\Notifications\ResetPassword;
use Config;

class ResetPasswordEmail extends Notification
{
    use Queueable;

    protected $pageUrl;

    public $token;

    /**
     * Create a new notification instance.
     *
     * @param $token
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
        $this->pageUrl = env('FE_SITE', 'localhost:8000') . '/reset';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $link = $this->pageUrl . '?token=' . $this->token;

        return (new MailMessage)
        ->subject('Reset Password')
        ->line('You are receiving this email because we received a password reset request for your account.')
        ->action('Reset Password', $link)
        ->line('This password reset link will expire in 60 minutes.')
        ->line('If you did not request a password reset, no further action is required.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
