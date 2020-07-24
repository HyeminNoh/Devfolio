<?php


namespace App\Repositories;


interface ReportInterface
{
    public function show($type);
    public function update($type);
}
