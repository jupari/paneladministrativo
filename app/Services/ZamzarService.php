<?php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZamzarService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('ZAMZAR_API_KEY');
        $this->baseUrl = "https://api.zamzar.com/v1";
    }

    public function convertirArchivo($rutaArchivo, $formatoDestino)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->attach('source_file', fopen($rutaArchivo, 'r'))
                ->post("{$this->baseUrl}/jobs", [
                    'target_format' => 'pdf',
                ]);
            return $response->json();
        } catch (Exception $e) {
           throw new Exception("Error al convertir el archivo: " . $e->getMessage());
        }
    }

    public function obtenerEstadoTrabajo($jobId)
    {
        // $response = Http::withBasicAuth($this->apiKey, '')
        //     ->get("{$this->baseUrl}/jobs/{$jobId}");

        // return $response->json();
        do {
            sleep(3);

            $jobStatus = Http::withBasicAuth($this->apiKey, '')
                ->get("{$this->baseUrl}/jobs/{$jobId}");

            $jobData = $jobStatus->json();
        } while ($jobData['status'] !== 'successful');
        return $jobData;
    }

    public function descargarArchivo($fileId, $rutaDestino)
    {
        $response = Http::withBasicAuth($this->apiKey, '')
        ->withHeaders([
            'Accept' => 'application/octet-stream'
        ])
        ->get("{$this->baseUrl}/files/{$fileId}/content");

        // Verificar si la solicitud fue exitosa
        if ($response->successful()) {
            // Guardar el contenido binario en la ruta destino
            //dd($response);
            file_put_contents($rutaDestino, $response->body());
            return $rutaDestino;
        } else {
            Log::error("Error al descargar archivo: " . $response->body());
            return null;
        }
    }

    public function obtenerInformacionArchivo($fileId)
    {
        $apiKey = env('ZAMZAR_API_KEY');

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get("{$this->baseUrl}/files/{$fileId}", [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
                ],
            ]);

            // Decodificar respuesta JSON
            $data = json_decode($response->getBody()->getContents(), true);

            return $data;

        } catch (\Exception $e) {
            return ['error' => 'Error al obtener información del archivo', 'details' => $e->getMessage()];
        }
    }
}
