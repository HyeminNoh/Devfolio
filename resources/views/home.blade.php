@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-6 col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <img src="{{ auth()->user()->avatar }}" width="100%;">
                        </div>
                        <div style="margin-top: 1em;">
                            <h3>
                                {{ auth()->user()->github_id }}
                            </h3>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="row">
                            <div class="col" style="text-align: right">
                                <a style="color: gray" href={{ auth()->user()->github_url }}>Github <i class="fab fa-github"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="row">
                    <div class="col">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <h4>📅 Contribution Calendar</h4>
                                    <div class="col text-right">
                                        <button onclick="calendarUpdate()" class="btn btn-light" type="button"><i class="fas fa-sync"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col text-lef">
                                        <p id="total-contribution-text">Total Contribution: </p>
                                    </div>
                                    <div class="col text-right">
                                        <p id="calendar-updated-text" style="color: gray">Last Updated: </p>
                                    </div>
                                </div>
                                <div id="calendar-div">
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
                <div class="row">
                    <div class="col" style="margin-top:1em;">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <h4>📊 Skills</h4>
                                    <div class="col" style="text-align: right">
                                        <button onclick="skillsUpdate()" class="btn btn-light" type="button"><i class="fas fa-sync"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
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
                                    <div class="col">
                                        <div class="row">
                                            <div class="col text-right">
                                                <p id="skill-updated-text" style="color: gray">Last Updated: </p>
                                            </div>
                                        </div>
                                        <div id="chart-desc-div" class="row">
                                            <p>Loading...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col" style="margin-top:1em;">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <h4>📌 Pinned Repository</h4>
                                    <div class="col" style="text-align: right">
                                        <button onclick="repositoriesUpdate()" class="btn btn-light" type="button"><i class="fas fa-sync"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col text-right">
                                        <p id="repository-updated-text" style="color: gray">Last Updated: </p>
                                    </div>
                                </div>
                                <div id="repositories-div">
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
@endsection

@push('scripts')
    <script src="{{ asset('js/dataLoad.js') }}"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script>
@endpush
