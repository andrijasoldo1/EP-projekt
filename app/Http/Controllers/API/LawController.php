<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LawController extends Controller
{
    /**
     * Get a list of laws.
     */
    public function index()
    {
        // List of laws with correct URLs
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
        ];

        return response()->json([
            'message' => 'List of laws retrieved successfully',
            'laws' => $laws
        ], 200);
    }
}
