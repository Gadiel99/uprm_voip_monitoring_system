<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeUserNotification extends Notification
{
    use Queueable;

    /**
     * The temporary password for the new user.
     *
     * @var string
     */
    public $temporaryPassword;

    /**
     * The user's email address.
     *
     * @var string
     */
    public $email;

    /**
     * Create a new notification instance.
     *
     * @param  string  $email
     * @param  string  $temporaryPassword
     * @return void
     */
    public function __construct($email, $temporaryPassword)
    {
        $this->email = $email;
        $this->temporaryPassword = $temporaryPassword;
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
        $loginUrl = url('/login');

        return (new MailMessage)
            ->subject('Welcome to UPRM VoIP Monitoring System')
            ->view('emails.welcome-user', [
                'user' => $notifiable,
                'email' => $this->email,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => $loginUrl,
            ]);
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
