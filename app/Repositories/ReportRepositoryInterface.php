<?php


namespace App\Repositories;


interface ReportRepositoryInterface
{
    public function get($userIdx, $type);

    public function update($userIdx, $type);
}
