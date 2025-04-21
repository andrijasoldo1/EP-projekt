<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Conversation;
use App\Models\ChatMessage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\UserSignature;
use Illuminate\Support\Str;


class DocumentController extends Controller
{
    /**
     * Generiranje Word dokumenta
     */
    public function generateWord($conversationId)
    {
        $conversation = $this->getConversation($conversationId);
        $chatbotResponse = $this->generateDocumentFromChatbot($conversationId);

        if (!$chatbotResponse) {
            Log::error("AI nije vratio odgovor za Word dokument.");
            return back()->with('error', 'Neuspjelo generiranje Word dokumenta.');
        }

        // Uklanja sve **bold oznake** iz AI odgovora
        $chatbotResponse = preg_replace('/\*\*(.*?)\*\*/', '$1', $chatbotResponse);

        Log::info("AI Odgovor za Word dokument: " . $chatbotResponse);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $section->addText("PRAVNI DOKUMENT", ['bold' => true, 'size' => 16, 'alignment' => 'center']);
        $section->addText("Generirano od strane AI odvjetnika", ['bold' => true, 'size' => 12, 'italic' => true]);
        $section->addTextBreak(2);
        $section->addText("Tema: " . $conversation->topic, ['bold' => true, 'size' => 12]);
        $section->addText("Datum generiranja: " . date('d.m.Y'), ['size' => 10, 'italic' => true]);
        $section->addTextBreak(2);

        foreach (explode("\n", $chatbotResponse) as $line) {
            $section->addText($line, ['size' => 12]);
        }

        // Spremanje dokumenta
        $this->ensureDirectoryExists(public_path('downloads'));
        $filePath = public_path("downloads/Pravni_Dokument_{$conversationId}.docx");
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    /**
     * Generiranje PDF-a sa QR kodom i stvarnim podacima iz baze
     */
    public function generateSignedPdf(Request $request, $conversationId)
    {
        set_time_limit(600);
        $conversation = $this->getConversation($conversationId);
        $chatbotResponse = $this->generateDocumentFromChatbot($conversationId);

        if (!$chatbotResponse) {
            Log::error("AI nije vratio odgovor za PDF generaciju.");
            return back()->with('error', 'Neuspjelo generiranje dokumenta. Pokušajte ponovno.');
        }

        // Uklanja sve **bold oznake** iz AI odgovora
        $chatbotResponse = preg_replace('/\*\*(.*?)\*\*/', '$1', $chatbotResponse);

        Log::info("AI Odgovor za PDF: " . $chatbotResponse);

        $sections = match ($conversation->document_type) {
            'tužba' => ['Činjenični opis', 'Pravni temelj', 'Zahtjev'],
            'ugovor' => ['Predmet ugovora', 'Obveze stranaka', 'Trajanje i raskid'],
            default => ['Opći podaci', 'Sadržaj', 'Zaključak'],
        };

        $data = [];
        foreach ($sections as $section) {
            $data[$section] = $this->extractSection($chatbotResponse, $section) ?? "Podaci nisu dostupni.";
        }


        // Ekstraktiranje podataka
        $caseDescription = $this->extractSection($chatbotResponse, 'Činjenični opis') ?? $this->getFallbackCaseDescription($conversationId);
        $legalBasis = $this->extractSection($chatbotResponse, 'Pravni temelj') ?? $this->getFallbackLegalBasis($conversationId);
        $claim = $this->extractSection($chatbotResponse, 'Zahtjev') ?? $this->getFallbackClaim($conversationId);

        // 🆕 Dohvaćanje korisničkog potpisa iz baze
        // 🆕 Dohvaćanje korisničkog potpisa iz baze
        $signatureData = UserSignature::where('user_id', Auth::id())->value('signature');

        if ($signatureData) {
            $signatureData = Str::startsWith($signatureData, 'data:image') ? $signatureData : asset($signatureData);
        }



        // Generiranje QR koda
        $verificationUrl = url("/verify-document/{$conversationId}");
        $qrCode = base64_encode(QrCode::format('png')->size(200)->generate($verificationUrl));

        // Kreiranje PDF-a
        $pdfPath = public_path("documents/Pravni_Dokument_{$conversationId}.pdf");

        $sections = match ($conversation->document_type) {
            'tužba' => ['Činjenični opis', 'Pravni temelj', 'Zahtjev'],
            'ugovor' => ['Predmet ugovora', 'Obveze stranaka', 'Trajanje i raskid'],
            'punomoć' => ['Opunomoćenik', 'Opunomoćitelj', 'Sadržaj punomoći'],
            default => ['Opći podaci', 'Sadržaj', 'Zaključak'],
        };

        // Kreiraj dinamične sekcije
        // Dinamično kreiranje sekcija ovisno o vrsti dokumenta
$data = [
    'Naslov dokumenta' => ucfirst($conversation->document_type ?? 'Pravni dokument'),
    'Tema' => $conversation->topic ?? 'N/A',
    'Činjenični opis' => $caseDescription,
    'Pravni temelj' => $legalBasis,
    'Zahtjev' => is_array($claim) ? implode("\n", $claim) : $claim
];

$pdf = Pdf::loadView('pdf.legal_document', [
    'conversation' => $conversation,
    'qrCode' => $qrCode,
    'verificationUrl' => $verificationUrl,
    'signatureData' => $signatureData,
    'data' => $data // Dodajemo niz u view
])->setPaper('A4', 'portrait')->setOptions([
    'defaultFont' => 'DejaVu Sans',
    'isHtml5ParserEnabled' => true,
    'isRemoteEnabled' => true
]);



        $pdf->save($pdfPath);

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    /**
     * Dohvaća razgovor
     */
    private function getConversation($conversationId)
    {
        return Conversation::where('id', $conversationId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    /**
     * Dohvaća AI odgovor
     */
    private function generateDocumentFromChatbot($conversationId)
{
    $conversation = $this->getConversation($conversationId);

    $messages = ChatMessage::where('conversation_id', $conversationId)
        ->orderBy('created_at')
        ->get()
        ->map(fn ($msg) => ['role' => $msg->role, 'content' => $msg->message])
        ->toArray();

    // Dinamički generiraj upute na temelju vrste dokumenta
    $instructions = match ($conversation->document_type) {
        'tužba' => 'Generiraj pravni dokument u obliku tužbe s relevantnim pravnim osnovama i zahtjevima.',
        'ugovor' => 'Generiraj pravni dokument u obliku kupoprodajnog ugovora s klauzulama, pravima i obvezama strana.',
        'opći dokument' => 'Generiraj pravni dokument u formalnom pravnom obliku na temelju dostupnih informacija.',
        default => 'Generiraj pravni dokument na temelju razgovora.',
    };

    array_unshift($messages, ['role' => 'system', 'content' => $instructions]);

    try {
        $client = new Client();
        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => ['Authorization' => 'Bearer ' . env('OPENAI_API_KEY'), 'Content-Type' => 'application/json'],
            'json' => ['model' => 'ft:gpt-4o-2024-08-06:toplaw:toplaw:AtYaitAD', 'messages' => $messages, 'max_tokens' => 1000, 'temperature' => 0.3]
        ]);

        return json_decode($response->getBody(), true)['choices'][0]['message']['content'] ?? null;
    } catch (\Exception $e) {
        Log::error("Greška pri dohvaćanju AI odgovora: " . $e->getMessage());
        return null;
    }
}


    /**
     * Ekstraktira sekcije iz AI odgovora
     */
    private function extractSection($text, $sectionTitle)
    {
        $pattern = "/{$sectionTitle}[:\n](.*?)(?=\n[A-Z]|\z)/s";
        preg_match($pattern, $text, $matches);
        return isset($matches[1]) ? trim($matches[1]) : null;
    }

    /**
     * Ako AI ne vrati sekcije, dohvaća pravu korisničku poruku iz baze
     */
    private function getFallbackLegalBasis($conversationId)
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->where('role', 'user')
            ->where('message', 'LIKE', '%zakon%')
            ->orderBy('created_at', 'desc')
            ->value('message') ?? "Pravni temelj nije pravilno dohvaćen.";
    }

    private function getFallbackClaim($conversationId)
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->where('role', 'user')
            ->where('message', 'LIKE', '%naknada%')
            ->orderBy('created_at', 'desc')
            ->value('message') ?? "Zahtjev nije pravilno generiran.";
    }

    /**
     * Osigurava da direktorij postoji
     */
    private function ensureDirectoryExists($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }
    }
    private function getFallbackCaseDescription($conversationId)
{
    return ChatMessage::where('conversation_id', $conversationId)
        ->where('role', 'user')
        ->orderBy('created_at', 'desc')
        ->value('message') ?? "Činjenični opis nije pravilno generiran.";
}



public function saveSignature(Request $request)
{
    $request->validate([
        'signature' => 'required|string',
    ]);

    $userId = Auth::id();
    $signature = $request->input('signature');

    // Provjeri je li potpis u Base64 formatu
    if (Str::startsWith($signature, 'data:image')) {
        $image = str_replace('data:image/png;base64,', '', $signature);
        $image = str_replace(' ', '+', $image);
        $imageName = "signatures/user_{$userId}_" . time() . ".png";

        // Spremi sliku u /storage/app/public/signatures/
        Storage::disk('public')->put($imageName, base64_decode($image));

        // Umjesto Base64 stringa, spremi putanju do slike
        $signature = "/storage/{$imageName}";
    }

    // Sprema u bazu (putanja umjesto Base64)
    UserSignature::updateOrCreate(
        ['user_id' => $userId],
        ['signature' => $signature]
    );

    return response()->json(['message' => 'Potpis uspješno spremljen!', 'signature_path' => $signature]);
}

}
