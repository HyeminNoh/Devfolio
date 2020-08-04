<?php


namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionException;

class ReportFactoryMethod
{
    /**
     * @param $userIdx
     * @param $type
     * @return bool|mixed|object
     */
    function makeReport($userIdx, $type)
    {
        // Reflection 활용 클래스 생성 - 생성할 객체가 뚜렷해 활용 가능
        $className = ucfirst($type);
        $classPath = "App\Factories\\" . $className;
        if (class_exists($classPath)) {
            try {
                Log::info($className . ' instance created');
                $refClass = new ReflectionClass($classPath);
                $userRepo = new UserRepository();
                $user = $userRepo->get($userIdx);
                return $refClass->newInstance($user);
            } catch (ReflectionException $e) {
                Log::info('Creating ' . $className . ' instance is fail');
                return false;
            }
        } else {
            Log::info($className . ' Class is not existing');
            return false;
        }
    }
}
