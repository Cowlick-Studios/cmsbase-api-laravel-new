<x-mail::message>
# New email submission: {{$emailSubmission->name}}

<br>

@foreach ($formData as $key => $value)
<p><b>{{$key}}</b>: {{$value}}</p>
@endforeach

<br>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
