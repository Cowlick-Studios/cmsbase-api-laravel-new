<x-mail::message>
# Reset Password

Use the following code to reset your password.

## {{$verificationCode}}

<!-- <x-mail::button :url="''">
Button Text
</x-mail::button> -->

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
