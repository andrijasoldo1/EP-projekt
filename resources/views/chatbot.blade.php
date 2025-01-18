@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Ask the AI Lawyer</h1>
        <form id="chatbotForm" method="POST" action="{{ route('chatbot.ask') }}">
            @csrf
            <div class="mb-3">
                <label for="query" class="form-label">Your Legal Question:</label>
                <textarea name="query" id="query" rows="4" class="form-control" placeholder="Type your question here..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Ask</button>
        </form>

        <div id="response" class="mt-4">
            <!-- The AI's response will be displayed here -->
        </div>
    </div>
@endsection
