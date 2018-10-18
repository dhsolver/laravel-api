<style>
.panel-item h2, .panel-item h1 { text-align: center; }
</style>

@component('mail::message')
## Hello {{ $request->user->firstname }},

You have submitted a request to change your email to *{{ $request->new_email }}*

@component('mail::panel')
## Your Activation Code
# {{ $request->readableCode }}
@endcomponent

If you did not request this change, then please ignore this email.

@include('mail/partials/signature')

@endcomponent
