<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContributionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        // 사용자 토큰 활용 api call

        // 데이터 파싱

        // 데이터 적재
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        // 사용자 아이디 기반 조회
        $user_idx = auth()->user()->id;
        // select * from contributions where user_idx = $user_idx
    }
}
