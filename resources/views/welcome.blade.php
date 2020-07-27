@extends('layouts.app')

@section('content')
    <div class="container justify-content-center">
        <div class="jumbotron">
            <h1 class="display-4">Devfolio</h1>
            <p class="lead">Github연동을 통해 Github 이력을 분석한 포트폴리오를 생성해 줍니다.</p>
            <hr class="my-4">
            <p>Saramin Intership First Mission.
                I use php language and Laravel framework.</p>
            @guest
                <div class="float-right">
                    <a class="btn btn-lg btn-dark"  href="{{ route('social.login', ['github']) }}"><i class="fab fa-github"></i> &nbsp; Sign in with Github</a>
                </div>
            @else
                <div class="float-right">
                    <a class="btn btn-lg btn-dark"  href="{{ 'portfolio/'.auth()->user()->idx }}">📋 Go to My Portfolio</a>
                </div>
            @endguest
        </div>
    </div>
@endsection
