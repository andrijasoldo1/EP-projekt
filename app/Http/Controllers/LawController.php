<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\LawsUpdated;

class LawController extends Controller
{
    public function index()
    {
        // Lista zakona s ispravnim URL-ovima
        $laws = [
            ['title' => 'Zakon o pečatu institucija BiH', 'url' => 'http://www.mpr.gov.ba/web_dokumenti/Zakon%20o%20pecatu%20institucija%20BiH%20-%2012-98_bs.pdf'],
            ['title' => 'Zakon o izmjenama Zakona o pečatu', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20izmjenama%20Zakona%20o%20pecatu%20-%2014-03.pdf'],
            ['title' => 'Zakon o zastavi BiH', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20zastavi%20BiH%20-%2019%20-%2001.pdf'],
            ['title' => 'Zakon o pravobranilaštvu', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20pravobranilastvu%20-%208%20-%2002.pdf'],
            ['title' => 'Zakon o slobodi pristupa informacijama', 'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20slobodi%20pristupa.pdf'],
        ];

        // Emitiraj event preko WebSocketa
        event(new LawsUpdated($laws));

        // Vrati kao JSON za Vue frontend
        return response()->json(['laws' => $laws]);
    }
}
