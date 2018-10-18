@component('mail::message')
## Hello {{ $user->firstname }},

Welcome to {{ config('app.name') }}!

Please confirm your email

@component('mail::button', ['url' => $url])
    Confirm My Email
@endcomponent

@include('mail/partials/signature')

@endcomponent
