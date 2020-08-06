@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center" style="margin-left: 5%; margin-right: 5%;">
            <div class="col-12 col-sm-12 col-lg-2 col-md-4">
                <div class="row">
                    <div class="col" style="margin-top:1em;">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <img src="{{ $user->avatar }}" style="width: 100%" alt="avatar">
                                </div>
                                <div style="margin-top: 1em;">
                                    <h3>
                                        {{ $user->github_id }}
                                    </h3>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col" style="text-align: right">
                                        <a style="color: gray" href={{ $user->github_url }}>Github <i class="fab fa-github"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-lg-10 col-md-8">
                <div class="row">
                    <div class="col" style="margin-top:1em;">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-8" style="margin-top: 0.5em">
                                        <h4>📅 Contribution Calendar</h4>
                                    </div>
                                    @if(auth()->check() && auth()->user()->idx==$user->idx)
                                    <div class="col" style="margin-top: 0.2em; text-align: right">
                                        <button onclick="updateCalendar({{$user->idx}})" class="btn btn-light" type="button">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col text-lef">
                                        <p id="total-contribution-text">Last Year Total Contribution: </p>
                                    </div>
                                    <div class="col text-right">
                                        <p id="calendar-updated-text" style="color: gray">Last Updated: </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                                        <div class="row">
                                            <div class="col-1">
                                                <button id="prev-btn" class="btn btn-light" style="height: 80%"><</button>
                                            </div>
                                            <div class="col" style="overflow: hidden">
                                                <div id="cal-heatmap">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="spinner-border text-secondary" role="status">
                                                            <span class="sr-only">Loading...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <button id="next-btn" class="btn btn-light" style="height: 80%">></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-4" id="onClick-placeholder"
                                         style="text-align: right">
                                        <p style="font-weight: bold; font-size: 1.2em"> Click your calendar ! </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 col-md-12" style="margin-top:1em;">
                        <div class="row">
                            <div class="col">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row">
                                            <div class="col-7" style="margin-top: 0.5em">
                                                <h4>📊 Skills</h4>
                                            </div>
                                            @if(auth()->check() && auth()->user()->idx==$user->idx)
                                                <div class="col" style="margin-top: 0.2em; text-align: right">
                                                    <button onclick="updateSkill({{$user->idx}})" class="btn btn-light" type="button">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col text-right">
                                                <p id="skill-updated-text" style="color: gray">Last Updated: </p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-12 col-md-12">
                                                <div class="card">
                                                    <div id="pie-chart-div" class="card-body">
                                                        <div class="d-flex justify-content-center">
                                                            <div class="spinner-border text-secondary" role="status">
                                                                <span class="sr-only">Loading...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-12 col-md-12">
                                                <div id="chart-desc-div" class="row" style="margin-top: 1em">
                                                    <div class="col" id="skill-desc-left-col"></div>
                                                    <div class="col" id="skill-desc-right-col"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($user->blog_url)
                            <div class="row">
                                <div class="col" style="margin-top:1em;">
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-7" style="margin-top: 0.5em">
                                                    <h4>✨ Recent Blog Posts</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row" id="blog-div">
                                                <div class="col">
                                                    <div class="d-flex justify-content-center">
                                                        <div class="spinner-border text-secondary" role="status">
                                                            <span class="sr-only">Loading...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col" style="text-align: right; margin-top: 1em">
                                                    <a class="btn btn-outline-secondary" href="{{ $user->blog_url }}"
                                                       role="button">Read More Posts</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-lg-6 col-md-12" style="margin-top:1em;">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-7" style="margin-top: 0.5em">
                                        <h4>📌 Pinned Repository</h4>
                                    </div>
                                    @if(auth()->check() && auth()->user()->idx==$user->idx)
                                    <div class="col" style="margin-top: 0.2em; text-align: right">
                                        <button onclick="updateRepo({{$user->idx}})" class="btn btn-light" type="button"><i class="fas fa-sync"></i></button>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col text-right">
                                        <p id="repository-updated-text" style="color: gray">Last Updated: </p>
                                    </div>
                                </div>
                                <div class="row" id="repositories-div">
                                    <div class="col">
                                        <div class="d-flex justify-content-center">
                                            <div class="spinner-border text-secondary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="repoModal" tabindex="-1" role="dialog" aria-labelledby="repoModalTitle"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body" id="repoModalBody">
                        <div class="row" style="margin: 0.01em;">
                            <div class="col" style="margin-top: 0.5em;">
                                <a id="titleLink"><h4 id="repoModalTitle">Modal title</h4></a>
                            </div>
                            <div id="modalCount" class="col" style="text-align: right; margin-top: 0.5em;">
                            </div>
                        </div>
                        <div id="modalPageUrlDiv" class="row" style="margin: 0.1em;">
                            <div class="col">
                                <p style='overflow: hidden; text-overflow: ellipsis; display: inline-block; width: 100%; white-space: nowrap;'>
                                    <i class='fas fa-link' style='color:gray;'></i>&nbsp;&nbsp;<a id="modalPageUrl" style='color:gray;'></a>
                                </p>
                            </div>
                        </div>
                        <div id="modalDescDiv" class="row" style="margin: 0.1em;">
                            <div class="col">
                                <p class="bg-light" id="repoModalDesc" style="padding: 1.5em;">Description</p>
                            </div>
                        </div>
                        <div id="modalLangDiv" class="row" style="margin: 0.1em;">
                            <div class="col">
                                <hr>
                                <div class="row">
                                    <div class="col">
                                        <h5>Languages</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div id="modalLangChart" class="col" style="margin: 1em;">
                                    </div>
                                    <div id="modalLangDesc" class="col">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="modalContriDiv" class="row" style="margin: 0.1em;">
                            <div class="col">
                                <hr>
                                <div class="row">
                                    <div class="col">
                                        <h5>Contributors</h5>
                                    </div>
                                </div>
                                <div class="row">
                                    <div id="contriProfileCol" class="col"></div>
                                    <div id="contriChartDiv" class="col"></div>
                                </div>
                            </div>
                        </div>
                        <div id="modalEmptyDiv" class="row" style="margin: 0.2em;">
                            <div class="col" style="text-align: center">
                                <h5 style="line-height: 2em; padding: 1em">저장소가 비어 있어요..! 😲<br>저장소의 내용을 분석할 수 없어요</h5>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- blog card template -->
    <script id="blog-card-template" type="text/template">
        <div class="col-12 col-sm-12 col-md-12" style="padding: 1em;">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-auto"><h5>{title}</h5></div>
                        <div class="col" style="text-align: right"><p style='color: gray'>{date}</p></div>
                    </div>
                    <div class="row">
                        <div class="col">
                            {badge}
                        </div>
                    </div>
                    <a class="stretched-link" href="{link}"></a>
                </div>
            </div>
        </div>
    </script>
    <!-- repo card template -->
    <script id="repo-card-template" type="text/template">
        <div class="col-12 col-sm-12 col-md-12" style="padding: 1em;">
            <div class="card">
                <div class="card-body">
                    <h5>{name}</h5><hr><p>{description}</p>
                    <div class="row">
                        <div class="col-auto">
                            <div class="row">
                                <div class="col">
                                    <p style='color:#808080;'>{stat}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col" style="text-align: right">
                            <p>{disk} KB</p>
                        </div>
                    </div>
                    <input type="hidden" value="{data}"/>
                    <a class="stretched-link" style="cursor: pointer" data-toggle="modal" data-target="#repoModal" onclick="makeRepoModal(this)"></a>
                </div>
            </div>
        </div>
    </script>
    <!-- lang desc text template -->
    <script id="lang-desc-text" type="text/template">
        <div class="row" style="margin-left: 1em;">
            <p><i class='fas fa-circle' style="color:{color}"></i>&nbsp;&nbsp;{name}: {size} bytes</p>
        </div>
    </script>
    <!-- star count col template -->
    <script id="star-count-template" type="text/template">
        <i class='fas fa-star' style='color: #808080'></i>&nbsp{count}
    </script>
    <!-- lang col template -->
    <script id="primary-lang-template" type="text/template">
        <i class='fas fa-circle' style='color:{color}'></i>&nbsp{name}
    </script>
    <!-- fork count col template -->
    <script id="fork-count-template" type="text/template">
        <i class='fas fa-code-branch' style='color: #808080'></i>&nbsp{count}
    </script>
    <!-- modal profile thumbnail -->
    <script id="modal-profile-thumbnail" type="text/template">
        <div class="row">
            <div class="col">
                <img src="{src}" class='img-thumbnail' alt='avatar'/>
            </div>
            <div class="col">
                <p><a href='{profileLink}'><span style='font-weight: bold'>{name}</span></a><br>{commit} commits</p>
            </div>
        </div>
    </script>
@endsection

@push('scripts')
    <script type="text/javascript">
        window.onload = function () {
            loadPortfolio({{ $user->idx }});
        }
    </script>
    <script src="{{ asset('js/portfolio.js') }}"></script>
    <script src="{{ asset('js/contribution.js') }}"></script>
    <script src="{{ asset('js/repository.js') }}"></script>
    <script src="{{ asset('js/skill.js') }}"></script>
    <script src="{{ asset('js/blog.js') }}"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>

    <!-- moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.0/moment.min.js"></script>

    <!-- Load d3.js & color scale -->
    <script type="text/javascript" src="//d3js.org/d3.v3.min.js"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/cal-heatmap/3.3.10/cal-heatmap.min.js"></script>
    <link rel="stylesheet" href="//cdn.jsdelivr.net/cal-heatmap/3.3.10/cal-heatmap.css"/>

    <!-- sweetalert -->
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
@endpush
