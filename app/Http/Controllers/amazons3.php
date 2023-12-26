<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;
class amazons3 extends Controller
{
    public function store(Request $request)
    {
  
        try {
            // Verifica si se ha enviado un archivo en la solicitud
            if (!$request->hasFile('foto')) {
                throw new \Exception('No se ha enviado ningún archivo.');
            }
    
            $file = $request->file('foto');
       
            // Configura el cliente de AWS S3
            $s3 = new S3Client([
                'version' => 'latest',
                'region' => 'us-east-1',
                'credentials' => [
                    'key'    => 'AKIAX3574SKRRXCWOIBB',
                    'secret' => 'a3l+1Eeo6ry2f01OMDzUiPzNV5c5Y1H4gXmDVMvQDJzC',
                ],
            ]);
    
            // Configura el nombre del bucket y la carpeta en Amazon S3
            $bucket = 'kimochii';
            $folder = 'kimochii/';
    
            // Sube el archivo a Amazon S3
            $result = $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $folder . $file->getClientOriginalName(),
                'Body'   => fopen($file->getPathname(), 'r'),
                'ACL'    => 'public-read',
            ]);
    
            // Verifica si la carga a Amazon S3 fue exitosa
            if ($result['@metadata']['statusCode'] !== 200) {
                throw new \Exception('Error al cargar el archivo a Amazon S3.');
            }
    
            // Obtiene la URL del objeto recién cargado en S3
            $url = $result['ObjectURL'];
    
            // Devuelve una respuesta con la URL del archivo almacenado en S3 y un mensaje de éxito
            return response()->json(['success' => true, 'url' => $url, 'message' => 'Archivo cargado correctamente a Amazon S3']);
        } catch (\Exception $e) {
            // Devuelve una respuesta con un mensaje de error en caso de excepción
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
}
