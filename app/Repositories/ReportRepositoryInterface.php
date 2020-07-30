<?php


namespace App\Repositories;


interface ReportRepositoryInterface
{
    /**
     * @param $userIdx
     * @param $type
     * @return mixed
     */
    public function get($userIdx, $type);

    /**
     * @param $userIdx
     * @param $type
     * @return mixed
     */
    public function update($userIdx, $type);
}
