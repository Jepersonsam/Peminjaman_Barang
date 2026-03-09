@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (file_exists(public_path('images/logo.png')))
<img src="{{ asset('images/logo.png') }}" class="logo" alt="{{ config('app.name') }} Logo">
@else
{{ $slot }}
@endif
</a>
</td>
</tr>
