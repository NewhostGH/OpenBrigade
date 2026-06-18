@extends(\App\Support\ErrorPage::layout(505))

@section('title', \App\Support\ErrorPage::meta(505)['title'].' · '.config('app.name'))

@section('content')
    @include('errors.partials.content', ['code' => 505])
@endsection
