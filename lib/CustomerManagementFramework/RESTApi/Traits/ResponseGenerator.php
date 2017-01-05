<?php


namespace CustomerManagementFramework\RESTApi\Traits;


use CustomerManagementFramework\RESTApi\Response;

trait ResponseGenerator
{
    /**
     * Create a JSON response with normalized body containing timestamp
     *
     * TODO timestamp needed?
     * TODO use a standard format like JSON-API?
     *
     * @param array|null $data
     * @param $code
     * @return Response
     */
    protected function createResponse(array $data = null, $code = Response::RESPONSE_CODE_OK)
    {
        $responseData = null;
        if (null !== $data) {
            $responseData = [
                'timestamp' => time()
            ];

            $responseData['data'] = $data;
        }

        return new Response($responseData, $code);
    }

    /**
     * Create error response
     *
     * @param $errors
     * @param int $code
     * @return Response
     */
    protected function createErrorResponse($errors, $code = Response::RESPONSE_CODE_BAD_REQUEST)
    {
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $response =  new Response([
            'success' => false,
            'errors'  => $errors
        ], $code);

        return $response;
    }
}
