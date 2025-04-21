<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\LawsUpdated;

class LawController extends Controller
{
    /**
     * Get a list of laws.
     */
    public function index()
    {
        $laws = [
            [
                'title' => 'Zakon o pečatu institucija BiH',
                'url' => 'http://www.mpr.gov.ba/web_dokumenti/Zakon%20o%20pecatu%20institucija%20BiH%20-%2012-98_bs.pdf'
            ],
            [
                'title' => 'Zakon o izmjenama Zakona o pečatu',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20izmjenama%20Zakona%20o%20pecatu%20-%2014-03.pdf'
            ],
            [
                'title' => 'Zakon o zastavi BiH',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20zastavi%20BiH%20-%2019%20-%2001.pdf'
            ],
            [
                'title' => 'Zakon o pravobranilaštvu',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20pravobranilastvu%20-%208%20-%2002.pdf'
            ],
            [
                'title' => 'Zakon o javnim nabavkama',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/?id=11420'
            ],
            [
                'title' => 'Zakon o upotrebi znakovnog jezika',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20upotrebi%20znakovnog%20jezika.pdf'
            ],
            [
                'title' => 'Zakon o državnoj himni BiH',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20drzavnoj%20himni%2019%20-%2001.pdf'
            ],
            [
                'title' => 'Zakon o izmjenama i dopunama Zakona o pravobranilaštvu',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/Zakon%20o%20izmjenama%20i%20dopunama%20Zakona%20o%20Pravobranilastvu%20BiH.pdf'
            ],
            [
                'title' => 'Zakon o osnivanju Zavoda za izvršenje krivičnih sankcija',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20osnivanju%20Zavoda%20za%20izvrsenje%20krivicnih%20sankcija.pdf'
            ],
            [
                'title' => 'Zakon o prekršajima (41-07)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20prekrsajima%2041%20-%2007.pdf'
            ],
            [
                'title' => 'Zakon o primjeni određenih privremenih mjera radi efikasnog...',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20primjeni%20odredjenih%20privremenih%20mjera%20radi%20efikasnog%20.pdf'
            ],
            [
                'title' => 'Zakon o pomilovanju (93-05)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20pomilovanju%20-%2093-05.pdf'
            ],
            [
                'title' => 'Zakon o izvršenju krivičnih sankcija (13-05)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20izvrsenju%20krivicnih%20sankcija%20-%2013%20-%2005.pdf'
            ],
            [
                'title' => 'Zakon o ustupanju predmeta od strane MKSJ (61-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20ustupanju%20predmeta%20od%20strane%20MKSJ%2061%20-%2004.pdf'
            ],
            [
                'title' => 'Zakon o javnim nabavkama (49-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20javnim%20nabavkama%2049%20-%2004.pdf'
            ],
            [
                'title' => 'Zakon o parničnom postupku pred Sudom BiH (36-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20parnicnom%20postupku%20pred%20Sudom%20BiH%20-%2036%20-%2004.pdf'
            ],
            [
                'title' => 'Zakon o polaganju pravosudnog ispita (33-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20polaganju%20pravosudnog%20ispita%20-%2033%20-%2004.pdf'
            ],
            [
                'title' => 'Zakon o programu zaštite svjedoka (29-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20programu%20zastite%20svjedoka%20-%2029-04.pdf'
            ],
            [
                'title' => 'Okvirni zakon o zalozima (28-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Okvirni%20zakon%20o%20zalozima%20-%2028%20-%2004.pdf'
            ],
            [
                'title' => 'Zakon o radu u institucijama BiH (26-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20radu%20u%20institucijama%20BiH%20-%2026-04.pdf'
            ],
            [
                'title' => 'Zakon o važnosti javnih isprava u BiH (23-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/23_04%20Zakon%20o%20vaznosti%20javnih%20isprava%20u%20BiH%20BJ.pdf'
            ],
            [
                'title' => 'Zakon o slobodi vjere',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/ZAKON%20o%20slobodi%20vjere.pdf'
            ],
            [
                'title' => 'Zakon o sudskim taksama u postupku pred sudom (39-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20sudskim%20taksama%20u%20postupku%20pred%20sudom%20-%2039%20-%2003.pdf'
            ],
            [
                'title' => 'Zakon o registraciji pravnih lica koja osnivaju institucije',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20registraciji%20pravnih%20lica%20koja%20osnivaju%20institucija%20.pdf'
            ],
            [
                'title' => 'Zakon o imunitetu',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20imunitetu%20BJ.pdf'
            ],
            [
                'title' => 'Zakon o upotrebi i zaštiti naziva BiH (30-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20upotrebi%20i%20zastiti%20naziva%20BiH%20-%2030%20-%2003.pdf'
            ],
            [
                'title' => 'Zakon o Vijeću ministara BiH (30-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20Vijecu%20ministara%20BiH%20-%2030-03.pdf'
            ],
            [
                'title' => 'Zakon o sudskoj policiji (21-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20sudskoj%20policiji%20-%2021-03.pdf'
            ],
            [
                'title' => 'Zakon o zaštiti svjedoka (21-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20zastiti%20svjedoka%2021-03.pdf'
            ],
            [
                'title' => 'Zakon o izvršnom postupku (18-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20izvrsnom%20postupku%2018-03.pdf'
            ],
            [
                'title' => 'Zakon o ministarstvima i drugim organima uprave BiH (5-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20ministarstvima%20i%20drugim%20organima%20uprave%20BiH%20-%205-03.pdf'
            ],
            [
                'title' => 'Krivični zakon BiH',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/KRIVICNI%20ZAKON%20BIH.pdf'
            ],
            [
                'title' => 'Zakon o krivičnom postupku (3-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20Krivicnom%20postupku%20-%203-03.pdf'
            ],
            [
                'title' => 'Zakon o VSTV-u (25-04)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/VSTV%2025%2004.pdf'
            ],
            [
                'title' => 'Zakon o upravi (32-02)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/ZAKON%20O%20UPRAVI%20-%2032-02.pdf'
            ],
            [
                'title' => 'Zakon o upravnom postupku (29-02)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20upravnom%20postupku%20-%2029%20-%2002.pdf'
            ],
            [
                'title' => 'Zakon o Tužilaštvu BiH (42-03)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20tuzilastvu%20-%2042%20-%2003.pdf'
            ],
            [
                'title' => 'Zakon o Sudu BiH (16-02)',
                'url' => 'http://www.mpr.gov.ba/biblioteka/zakoni/bs/Zakon%20o%20sudu%20BiH%20-%2016-02.pdf'
            ],
        ];
        

        // Emituj događaj preko WebSocketa
        event(new LawsUpdated($laws));


        return response()->json([
            'message' => 'List of laws retrieved successfully',
            'laws' => $laws
        ], 200);
    }
}
