@component('mail::message')
# Здравствуйте, {{ $application->film->participant->name ?? 'Участник' }}!

Ваша заявка на фильм **"{{ $application->film->title }}"** для мероприятия **"{{ $application->event->title }}"** была {{ $statusRu }}.

@if($application->comment)
@component('mail::panel')
💬 **Комментарий организатора:**  
{{ $application->comment }}
@endcomponent
@endif

Спасибо за участие в Krasnodar International Filmfest!

@component('mail::button', ['url' => url('/login')])
Войти в личный кабинет
@endcomponent

С уважением,<br>
Команда фестиваля KIFF
@endcomponent