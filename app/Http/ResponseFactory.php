<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

class ResponseFactory extends JsonResponse
{

    /**
     * Formats the http response to better treatment 
     * 
     * @param null|string $data 
     * @param int $status 
     * @param array $headers 
     * @return ResponseFactory 
     */
    public function make(?string $data = null, int $status = 200, array $headers = [])
    {
        return $this->fromJsonString($data);
    }
}
