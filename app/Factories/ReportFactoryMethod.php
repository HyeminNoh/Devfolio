<?php


namespace App\Factories;

use Illuminate\Support\Facades\Log;
use ReflectionClass;

class ReportFactoryMethod extends AbstractFactoryMethod
{
    function makeReport($userIdx, $type)
    {
        // Reflection 활용 클래스 생성 - 생성할 객체가 뚜렷해 활용 가능
        $className = ucfirst($type);
        $classPath = "App\Factories\\".$className;
        if(class_exists($classPath)){
            try {
                Log::info($className.' instance created');
                $refClass = new ReflectionClass($classPath);
                return $refClass->newInstance($userIdx);
            } catch (\ReflectionException $e) {
                Log::info('Creating '.$className.' instance is fail');
            }
        }
        else{
            Log::info($className.' Class is not existing');
        }
    }
}
