<?php

namespace App\Controllers;
use GuzzleHttp\Client;

class Home extends BaseController
{
    public function index(): string
    {
        return view('main');
    }

    public function lacak_bus(): string
    {
        return view('detail');
    }

    public function home(): string
    {
        return view('welcome_message');
    }

    public function getPolyline()
    {
        $query = $this->request->getGet('query');
        
        $client = new Client();
        $response = $client->get(
            'https://routing.openstreetmap.de/routed-bike/route/v1/driving/112.72079074999999,-7.1704675;112.73813557744137,-7.302871834544816?overview=false&alternatives=true&steps=true',
            [
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'Thishub/1.0'
                ]
            ]
        );
    
        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);
            return $this->response->setJSON([
                'status_code' => 200,
                'error' => false,
                'message' => "Successfully",
                'data' => $data
            ]);
        } elseif ($response->getStatusCode() == 403) {
            return $this->response->setJSON([
                'status_code' => 403,
                'error' => true,
                'message' => "Forbidden: The request is forbidden. Please check the query parameters and API usage."
            ], 403);
        } else {
            return $this->response->setJSON([
                'status_code' => 500,
                'error' => true,
                'message' => "Internal Server Error"
            ], 500);
        }
    }
}
