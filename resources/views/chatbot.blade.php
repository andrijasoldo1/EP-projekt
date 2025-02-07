@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">AI Lawyer Chatbot</h1>

    <!-- Dropdown za upute -->
    <div class="mb-3">
        <button class="btn btn-info" type="button" id="toggleInstructions">📖 Prikaži upute</button>
        <div id="instructionsBox" class="alert alert-info mt-2" style="display: none;">
            <h5>💡 Kako koristiti chat za pravni dokument?</h5>
            <p>Da biste generirali ispravan pravni dokument, chatbotu trebate pružiti sljedeće informacije:</p>
            <ul>
                <li><strong>Vrsta dokumenta:</strong> Npr. tužba, ugovor, punomoć...</li>
                <li><strong>Podaci o strankama:</strong> Ime i prezime tužitelja, tuženika, kupca, prodavatelja itd.</li>
                <li><strong>Činjenični opis:</strong> Detaljan opis situacije (što se dogodilo, gdje, kada...)</li>
                <li><strong>Pravni temelj:</strong> Ako znate pravnu osnovu, možete je navesti.</li>
                <li><strong>Zahtjev:</strong> Što želite postići ovim dokumentom?</li>
            </ul>
            <p>📌 <strong>Primjer:</strong> "Želim sastaviti kupoprodajni ugovor. Prodavatelj je Ivan Horvat, kupac je Marko Perić. Predmet prodaje je automobil BMW X5, 2018. godište, registracija ST-123-AB. Cijena je 20.000 EUR, a plaćanje se vrši u dvije rate."</p>
        </div>
    </div>

    <!-- Conversation Selector -->
    <div class="mb-3">
        <label for="conversationSelector" class="form-label">Odaberite razgovor:</label>
        <select id="conversationSelector" class="form-select">
            <option value="">-- Odaberite razgovor --</option>
            @foreach ($conversations as $conversation)
                <option value="{{ $conversation->id }}" {{ $conversationId == $conversation->id ? 'selected' : '' }}>
                    {{ $conversation->topic }}
                </option>
            @endforeach
        </select>
        <button id="newConversationBtn" class="btn btn-sm btn-primary mt-2">Novi razgovor</button>
    </div>

    <!-- Document Generation Buttons -->
    <div class="mb-3">
        <button id="generateWordBtn" class="btn btn-sm btn-success">📄 Preuzmi Word</button>
        <button id="generatePdfBtn" class="btn btn-sm btn-danger">📄 Preuzmi PDF</button>
    </div>

    <!-- Chatbox -->
    <div id="chatbox" class="border rounded p-3 mb-3" style="height: 400px; overflow-y: auto; background-color: #f9f9f9;">
        @foreach ($chatHistory as $message)
            <div class="d-flex {{ $message->role === 'user' ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="p-2 mb-2 rounded {{ $message->role === 'user' ? 'user-bubble' : 'chatbot-bubble' }}">
                    {{ $message->message }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Input Form -->
    <form id="chatbotForm" class="d-flex align-items-center">
        @csrf
        <textarea name="query" id="query" rows="1" class="form-control me-2" style="resize: none;" placeholder="Upišite pitanje..."></textarea>
        <button type="submit" class="btn btn-primary">Pošalji</button>
    </form>
</div>

<script>
    // Toggle instrukcija
    document.getElementById('toggleInstructions').addEventListener('click', function () {
        const box = document.getElementById('instructionsBox');
        if (box.style.display === 'none') {
            box.style.display = 'block';
            this.textContent = "📖 Sakrij upute";
        } else {
            box.style.display = 'none';
            this.textContent = "📖 Prikaži upute";
        }
    });

    document.getElementById('conversationSelector').addEventListener('change', function () {
        const conversationId = this.value;
        if (conversationId) {
            window.location.href = `/chatbot/${conversationId}`;
        }
    });

    document.getElementById('newConversationBtn').addEventListener('click', async function () {
        const topic = prompt('Unesite temu novog razgovora:');
        if (topic) {
            const response = await fetch("{{ route('conversation.create') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ topic }),
            });

            const data = await response.json();
            if (data.conversation_id) {
                window.location.href = `/chatbot/${data.conversation_id}`;
            }
        }
    });

    document.getElementById('chatbotForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const query = document.getElementById('query').value;
        const conversationId = document.getElementById('conversationSelector').value;
        const csrfToken = '{{ csrf_token() }}';

        if (!conversationId) {
            alert('Odaberite razgovor.');
            return;
        }

        addMessage('user', query);

        try {
            const response = await fetch(`/chatbot/${conversationId}/ask`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ query }),
            });

            const data = await response.json();

            if (data.answer) {
                addMessage('assistant', data.answer);
            } else if (data.error) {
                addMessage('error', 'Greška: ' + data.error);
            }
        } catch (error) {
            addMessage('error', 'Došlo je do greške pri slanju zahtjeva.');
        }

        document.getElementById('query').value = '';
    });

    document.getElementById('generateWordBtn').addEventListener('click', function () {
        const conversationId = document.getElementById('conversationSelector').value;

        if (!conversationId) {
            alert('Molimo odaberite razgovor.');
            return;
        }

        window.location.href = `/conversation/${conversationId}/generate-word`;
    });

    document.getElementById('generatePdfBtn').addEventListener('click', function () {
        const conversationId = document.getElementById('conversationSelector').value;

        if (!conversationId) {
            alert('Molimo odaberite razgovor.');
            return;
        }

        window.location.href = `/conversation/${conversationId}/generate-signed-pdf`;
    });

    function addMessage(role, text) {
        const chatbox = document.getElementById('chatbox');
        const messageDiv = document.createElement('div');

        messageDiv.className = 'd-flex ' + (role === 'user' ? 'justify-content-end' : 'justify-content-start');
        messageDiv.innerHTML = `
            <div class="p-2 mb-2 rounded ${role === 'user' ? 'user-bubble' : 'chatbot-bubble'}">
                ${text}
            </div>
        `;
        chatbox.appendChild(messageDiv);
        chatbox.scrollTop = chatbox.scrollHeight;
    }
</script>

<style>
    .user-bubble {
        background-color: #007bff;
        color: white;
        max-width: 75%;
        display: inline-block;
        text-align: left;
        border-radius: 15px;
        padding: 10px;
        margin-left: auto;
    }

    .chatbot-bubble {
        background-color: #28a745;
        color: white;
        max-width: 75%;
        display: inline-block;
        text-align: left;
        border-radius: 15px;
        padding: 10px;
        margin-right: auto;
    }

    #chatbox {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 10px;
    }
</style>
@endsection
