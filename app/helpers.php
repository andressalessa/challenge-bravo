<?php

function json_response(?string $data = null, int $status = 200, array $headers = []) {

    $factory = new \App\Http\ResponseFactory();
    
    if (func_num_args() === 0) {
        return $factory;
    }

    return $factory->make($data);
};