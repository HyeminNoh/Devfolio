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
                    <a class="btn btn-lg btn-dark" href="{{ route('social.login', ['github']) }}"><i
                            class="fab fa-github"></i> &nbsp; Sign in with Github</a>
                </div>
            @else
                <div class="float-right">
                    <a class="btn btn-lg btn-dark" href="{{ 'portfolio/'.auth()->user()->idx }}">📋 Go to My
                        Portfolio</a>
                </div>
            @endguest
        </div>
        <hr>
        <div class="row">
            <div class="col">
                <h3>🔎 Explore</h3>
            </div>
            <div class="col" style="text-align: right">
                <p style="color: gray">👀 다른 사용자들의 포트폴리오를 둘러보세요!</p>
            </div>
        </div>
        <div class="row">
            @foreach($userList as $user)
                <div class="col-lg-3 col-md-4 col" style="margin-top: 1em">
                    <div class="card">
                        <div class="card-body">
                            <img src="{{ $user->avatar }}" width="100%;"/>
                            <h5 style="margin-top: 1em">{{ $user->name }}</h5>
                            <a class="stretched-link" href="{{ route('portfolio', ['userIdx' => $user->idx]) }}"></a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
