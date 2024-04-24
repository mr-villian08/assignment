@extends('layouts.root-layout')

@section('title', 'Assignment')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bucket Form</div>
                <div class="card-body">
                    <form method="POST" id="bucket-form" onsubmit="createBucket(event)">
                        @csrf
                        <div class="form-group">
                            <label for="bucket-name">Bucket Name:</label>
                            <input type="text" name="name" id="bucket-name" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="bucket-volume">Volume (cubic inches):</label>
                            <input type="number" step=".01" name="volume" id="bucket-volume" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2" id="bucket-button">Create Bucket</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Ball Form</div>
                <div class="card-body">
                    <form method="POST" id="ball-form" onsubmit="createBall(event)">
                        @csrf
                        <div class="form-group">
                            <label for="ball-color">Ball Color:</label>
                            <input type="text" name="color" id="ball-color" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="ball-size">Size (cubic inches):</label>
                            <input type="number" step=".01" name="size" id="ball-size" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary mt-2" id="ball-button">Create Ball</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bucket Suggestion Form</div>
                <div class="card-body">
                    <form id="bucket-suggestion-form" method="POST" onsubmit="createBucketSuggestion(event)">

                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Result</div>
                <div class="card-body" id="result-body">

                </div>
            </div>
        </div>
    </div>
@endsection
