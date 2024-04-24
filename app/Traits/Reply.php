<?php

namespace App\Traits;

trait Reply
{
    // ? ********************************************************** Success method ********************************************************** */
    public function success($message, $data = null, array $extra = [])
    {
        $info['status'] = true;
        $info['message'] = ucwords($message);
        if ($data != null) {
            $info['data'] = $data;
        }

        if ($extra != []) {
            foreach ($extra as $key => $value) {
                $info[$key] = $value;
            }
        }

        return $info;
    }

    // ? ********************************************************** Failed method ********************************************************** */
    public function failed($message)
    {
        $info['status'] = false;
        $info['message'] = ucwords($message);

        return $info;
    }
}
