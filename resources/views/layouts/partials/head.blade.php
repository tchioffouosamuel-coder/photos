{{--
  | En-tete commun a toutes les pages (application et authentification).
  | Source unique des dependances front : toute addition se fait ici.
--}}
<meta charset="utf-8">
{{-- viewport-fit=cover : indispensable pour que env(safe-area-inset-*) soit
     renseigne, et donc pour que la navigation basse evite l'encoche et la
     barre gestuelle sur iPhone. --}}
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#082B59">
<meta name="description" content="{{ $description ?? 'ACID - plateforme de gestion de l\'identification scolaire : eleves, photographies, cartes scolaires et documents d\'examen.' }}">

<title>{{ isset($title) ? $title . ' · ' . config('app.name', 'ACID') : config('app.name', 'ACID') }}</title>

<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

{{-- Typographie officielle : Plus Jakarta Sans --}}
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link rel="stylesheet"
      href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap">

{{-- Socle Bootstrap 5.3 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      crossorigin="anonymous">

{{-- Icones --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
      crossorigin="anonymous">

{{-- Theme ACID : design tokens + composants. Charge en dernier pour primer. --}}
<link rel="stylesheet" href="{{ asset('assets/css/acid.css') }}?v={{ @filemtime(public_path('assets/css/acid.css')) ?: '1' }}">

@stack('styles')
