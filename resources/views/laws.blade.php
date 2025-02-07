@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">Pregled Zakona</h1>

    <div class="row">
        <!-- Dropdown za odabir zakona -->
        <div class="col-md-4">
            <h4>Lista zakona:</h4>
            <select id="lawSelector" class="form-select">
                <option value="">-- Odaberi zakon --</option>
                @foreach ($laws as $law)
                    <option value="{{ $law['url'] }}">{{ $law['title'] }}</option>
                @endforeach
            </select>
        </div>

        <!-- Pregled dokumenta -->
        <div class="col-md-8">
            <h4>Pregled dokumenta:</h4>
            <div id="docContainer">
                <iframe id="docViewer" src="" style="width:100%; height:600px;" frameborder="0"></iframe>
            </div>
            <div id="downloadMessage" style="display: none;">
                <p>Dokument se ne može prikazati unutar preglednika. Možete ga preuzeti putem poveznice: </p>
                <a id="downloadLink" href="" target="_blank">Preuzmi zakon</a>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('lawSelector').addEventListener('change', function () {
        const docUrl = this.value;
        const docViewer = document.getElementById("docViewer");
        const docContainer = document.getElementById("docContainer");
        const downloadMessage = document.getElementById("downloadMessage");
        const downloadLink = document.getElementById("downloadLink");

        if (docUrl) {
            if (docUrl.endsWith('.pdf') || docUrl.endsWith('.docx')) {
                docViewer.src = `https://docs.google.com/gview?url=${encodeURIComponent(docUrl)}&embedded=true`;
                docContainer.style.display = "block";
                downloadMessage.style.display = "none";
            } else {
                docContainer.style.display = "none";
                downloadMessage.style.display = "block";
                downloadLink.href = docUrl;
            }
        } else {
            docViewer.src = "";
            docContainer.style.display = "none";
            downloadMessage.style.display = "none";
        }
    });
</script>
@endsection
