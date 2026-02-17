<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\JwtLibrary;
use Exception;

class JWTAuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->header('Authorization');
        if(!$header){
            return service('response')->setJSON([
                'status' => 'error',
                'message' => 'Authorization header not found'
            ])->setStatusCode(401);
        }
        if(!preg_match('/Bearer\s(\S+)/', $header, $matches)){
            return service('response')->setJSON([
                'status' => 'error',
                'message' => 'Authorization header invalid'
            ])->setStatusCode(401);
        }
        $token = $matches[1];
        try{
            $jwt = new JwtLibrary();
            $decoded = $jwt->validateToken($token);
            $request->user = $decoded->data;
        } catch(Exception $e){
            return service('response')->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ])->setStatusCode(401);
        }
        //
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
